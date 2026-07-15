<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use FilesystemIterator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * TEMPORARY maintenance endpoint (added 2026-07-14, remove once ops fixes the
 * server-side ownership properly).
 *
 * On vdn-typos1/vdn-typop1, `shared/storage/app` ended up owned solely by
 * `www-data` (mode 0700), created by a PHP-FPM web request rather than the
 * deploy script — so neither the `octopus` (deploy) nor `web` SSH accounts
 * can create `app/private` or symlink `app/public` to the network uploads
 * volume, and no scoped sudo rule exists for it. Since this controller runs
 * IN-PROCESS as the `www-data` PHP-FPM user, it CAN fix its own directory.
 *
 * Guarded by the `admin.ip` middleware + an authenticated admin check.
 */
class StoragePermissionsMaintenanceController extends Controller
{
    public function fix(Request $request): JsonResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $log = [];

        $appDir = storage_path('app');
        $privateDir = storage_path('app/private');
        $publicLink = storage_path('app/public');

        $uploadsPath = app()->environment('production')
            ? '/mnt/typo3_data/artistes_uploads'
            : '/mnt/typo3_data/artistes-staging_uploads';

        if (@chmod($appDir, 0775)) {
            $log[] = "chmod 0775 {$appDir}: ok";
        } else {
            $message = error_get_last()['message'] ?? 'unknown';
            $log[] = "chmod 0775 {$appDir}: FAILED ({$message})";
        }

        if (! is_dir($privateDir)) {
            $log[] = mkdir($privateDir, 0775, true)
                ? "mkdir {$privateDir}: ok"
                : "mkdir {$privateDir}: FAILED";
        } else {
            $log[] = "mkdir {$privateDir}: already exists";
            $log[] = @chmod($privateDir, 0775)
                ? "chmod 0775 {$privateDir}: ok"
                : "chmod 0775 {$privateDir}: FAILED";

            [$fixed, $failed] = $this->recursivelyMakeGroupWritable($privateDir);
            $log[] = "recursive chmod 0775 under {$privateDir}: {$fixed} ok, {$failed} failed";
        }

        if (! is_dir($uploadsPath)) {
            $log[] = mkdir($uploadsPath, 0775, true)
                ? "mkdir {$uploadsPath}: ok"
                : "mkdir {$uploadsPath}: FAILED";
        } else {
            $log[] = "mkdir {$uploadsPath}: already exists";
        }

        if (is_link($publicLink) && readlink($publicLink) === $uploadsPath) {
            $log[] = "symlink {$publicLink} -> {$uploadsPath}: already correct";
        } else {
            if (is_link($publicLink) || is_dir($publicLink) || file_exists($publicLink)) {
                if (is_dir($publicLink) && ! is_link($publicLink)) {
                    $log[] = "symlink {$publicLink}: SKIPPED, a real directory already exists there (not overwriting)";
                } else {
                    @unlink($publicLink);
                    $log[] = symlink($uploadsPath, $publicLink)
                        ? "symlink {$publicLink} -> {$uploadsPath}: ok (replaced)"
                        : "symlink {$publicLink} -> {$uploadsPath}: FAILED";
                }
            } else {
                $log[] = symlink($uploadsPath, $publicLink)
                    ? "symlink {$publicLink} -> {$uploadsPath}: ok"
                    : "symlink {$publicLink} -> {$uploadsPath}: FAILED";
            }
        }

        return response()->json(['log' => $log]);
    }

    /**
     * chmod() is not recursive in PHP, and files/directories written by
     * PHP-FPM (www-data) under app/private can end up with a restrictive
     * mode (e.g. 0700) that blocks the octopus/web SSH accounts even after
     * fixing the parent directory. Since this process owns everything
     * under private/ (it's all www-data-owned), it can chmod each entry.
     *
     * @return array{0: int, 1: int} [$fixedCount, $failedCount]
     */
    private function recursivelyMakeGroupWritable(string $directory): array
    {
        $fixed = 0;
        $failed = 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $path => $fileInfo) {
            $mode = $fileInfo->isDir() ? 0775 : 0664;

            if (@chmod($path, $mode)) {
                $fixed++;
            } else {
                $failed++;
            }
        }

        return [$fixed, $failed];
    }
}
