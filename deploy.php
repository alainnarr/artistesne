<?php

namespace Deployer;

require 'recipe/laravel.php';
require 'contrib/sentry.php';

/*
|--------------------------------------------------------------------------
| Inventaire des artistes neuchâtelois·es — Deployer config
|--------------------------------------------------------------------------
|
| Deployment pipeline:
|
|   Azure DevOps (build on VDN-TFS1)
|       ├─ composer install --no-dev --optimize-autoloader
|       ├─ pnpm install + pnpm build (Vite manifest in public/build/)
|       └─ tar artefact → push as Octopus package
|
|   Octopus Deploy
|       └─ step "Run a Script" on VDN-TFS1 worker → executes:
|            dep deploy <stage> -o artifact_path=<extracted>
|
| The targets (VDN-TYPOS1/VDN-TYPOP1) have no Node.js and no outbound
| internet — we never run `pnpm`, `composer` nor `git clone` there.
| Deployer uploads the pre-built artefact via rsync over SSH (web user).
|
*/

// --- Project ---------------------------------------------------------------

set('application', 'sien-inventaire-artistes');
set('keep_releases', 5);
set('git_tty', false);
// We do NOT clone on the remote — artefact-driven flow.
set('repository', '');

// --- Shared & writable ----------------------------------------------------

add('shared_files', ['.env']);
add('shared_dirs', ['storage']);
add('writable_dirs', ['bootstrap/cache', 'storage']);
set('writable_mode', 'chmod');
set('writable_chmod_mode', '0777');
set('writable_use_sudo', false);
set('http_user', 'www-data');

// --- Custom variables -----------------------------------------------------

// Local path (on the Octopus worker) to the extracted build artefact.
// Override via:   dep deploy <stage> -o artifact_path=/path/to/extracted
set('artifact_path', getenv('ARTIFACT_PATH') ?: '');

set('bin/php', '/usr/bin/php8.4');

// --- Hosts ----------------------------------------------------------------

host('staging')
    ->setLabels(['stage' => 'staging'])
    ->set('stage', 'staging')
    ->setHostname('vdn-typos1')
    ->setRemoteUser('web')
    ->setDeployPath('/data/artistes')
    ->set('http_user', 'www-data')
    ->set('uploads_path', '/mnt/typo3_data/artistes-staging_uploads')
    ->setForwardAgent(false);

host('production')
    ->setLabels(['stage' => 'production'])
    ->set('stage', 'production')
    ->setHostname('vdn-typop1')
    ->setRemoteUser('web')
    ->setDeployPath('/data/artistes')
    ->set('http_user', 'www-data')
    ->set('uploads_path', '/mnt/typo3_data/artistes_uploads')
    ->setForwardAgent(false);

// --- Tasks ----------------------------------------------------------------

/**
 * Replace Deployer's default `deploy:update_code` task: instead of cloning a
 * git repo on the remote host, we rsync a locally extracted artefact produced
 * by the AzDO pipeline and downloaded by Octopus on the worker.
 */
task('deploy:update_code', function () {
    $artifact = get('artifact_path');

    if ($artifact === '' || ! is_dir($artifact)) {
        throw new \RuntimeException(
            "ARTIFACT_PATH is empty or not a directory: '{$artifact}'. "
            .'Pass it via `dep deploy <stage> -o artifact_path=/path/to/extracted`.'
        );
    }

    // Trailing slash → rsync copies contents, not the wrapper folder.
    $artifact = rtrim($artifact, '/').'/';

    run('mkdir -p {{release_path}}');

    upload($artifact, '{{release_path}}/', [
        'options' => [
            '--delete',
            '--exclude=.git',
            '--exclude=node_modules',
            '--exclude=tests',
            '--exclude=.env',
            '--exclude=storage',
        ],
    ]);
})->desc('Upload pre-built artefact to the new release directory');

task('deploy:fpm:reload', function () {
    run('sudo /bin/systemctl reload php8.4-fpm');
})->desc('Gracefully reload PHP-FPM 8.4 workers');

task('deploy:fix_storage_permissions', function () {
    // Ensure shared/storage is writable by PHP-FPM (www-data).
    // Runs as the `web` SSH user who owns the shared directory, so no sudo needed.
    run('chmod -R u+rwx,g+rwx {{deploy_path}}/shared/storage');
    run('chown -R {{remote_user}}:www-data {{deploy_path}}/shared/storage || true');
})->desc('Make shared/storage writable for PHP-FPM');

task('deploy:storage:link', function () {
    run('rm -rf {{release_path}}/public/storage');
    run('ln -s {{deploy_path}}/shared/storage/app/public {{release_path}}/public/storage');
})->desc('Symlink public/storage to shared storage');

/**
 * User-uploaded files (artist portraits, attachments) must live on the
 * mounted mass-storage volume (/mnt/typo3_data, same DFS share used by the
 * TYPO3 sites' fileadmin/uploads folders) instead of the local /data disk,
 * so they follow the same backup/retention policy as every other app.
 *
 * storage/app/public is turned into a symlink pointing at the shared mount;
 * everything else (logs, framework cache/sessions/views) stays on local
 * disk under {{deploy_path}}/shared/storage since it doesn't need to be
 * persisted on the network share.
 */
task('deploy:uploads:link', function () {
    $uploadsPath = get('uploads_path');

    run("mkdir -p {$uploadsPath}");
    run('mkdir -p {{deploy_path}}/shared/storage/app');
    run('rm -rf {{deploy_path}}/shared/storage/app/public');
    run("ln -sfn {$uploadsPath} {{deploy_path}}/shared/storage/app/public");
})->desc('Point storage/app/public at the shared /mnt uploads volume');

task('artisan:optimize', function () {
    run('cd {{release_path}} && {{bin/php}} artisan optimize');
})->desc('Cache config, routes, views & events in one shot');

task('artisan:database:prepare', function () {
    run('cd {{release_path}} && {{bin/php}} artisan migrate --force');
})->desc('Run pending migrations');

// --- Deployment flow ------------------------------------------------------

task('deploy', [
    'deploy:info',
    'deploy:setup',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',     // ← uploads artefact (no git, no node)
    'deploy:shared',          // ← links .env + storage from shared/
    'deploy:uploads:link',    // ← points storage/app/public at /mnt uploads volume
    'deploy:fix_storage_permissions',
    'deploy:writable',
    'deploy:storage:link',
    'artisan:database:prepare',
    'artisan:optimize',
    'deploy:symlink',         // ← atomic switch of `current`
    'deploy:fpm:reload',
    'deploy:unlock',
    'deploy:cleanup',
])->desc('Deploy the SCNE Inventaire des artistes application');

// --- Hooks ----------------------------------------------------------------

after('deploy:failed', 'deploy:unlock');
