<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

class OidcController extends Controller
{
    /**
     * Redirect the admin to the AD FS OIDC authorization endpoint.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('adfs')->redirect();
    }

    /**
     * Handle the callback from AD FS after successful authentication.
     *
     * Flow:
     *  1. Retrieve the authenticated user from AD FS.
     *  2. Validate group membership (if ADFS_ALLOWED_GROUP is configured).
     *  3. Find an existing admin or provision a new one (JIT, if enabled).
     *  4. Log in and redirect to the admin panel.
     */
    public function callback(Request $request): RedirectResponse
    {
        if ($request->has('error')) {
            Log::warning('AD FS OIDC error', [
                'error' => $request->input('error'),
                'description' => $request->input('error_description'),
            ]);

            return redirect()->route('filament.admin.auth.login')
                ->withErrors(['adfs' => __('La connexion via le compte canton a échoué. Veuillez réessayer.')]);
        }

        try {
            $socialiteUser = Socialite::driver('adfs')->user();
        } catch (\Throwable $e) {
            // InvalidStateException carries no message, so log the class and the
            // state values to distinguish a lost session from a token/userinfo failure.
            Log::error('AD FS OIDC callback error', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'request_state' => $request->input('state'),
                'session_has_state' => $request->session()->has('state'),
            ]);

            return redirect()->route('filament.admin.auth.login')
                ->withErrors(['adfs' => __('La connexion via le compte canton a échoué. Veuillez réessayer.')]);
        }

        if (! $this->isGroupAllowed($socialiteUser)) {
            Log::warning('AD FS login refused: user not in allowed group', [
                'email' => $socialiteUser->getEmail(),
            ]);

            return redirect()->route('filament.admin.auth.login')
                ->withErrors(['adfs' => __('Vous n\'avez pas les droits nécessaires pour accéder à l\'administration.')]);
        }

        $user = $this->findOrProvisionAdmin($socialiteUser);

        if ($user === null) {
            return redirect()->route('filament.admin.auth.login')
                ->withErrors(['adfs' => __('Aucun compte administrateur associé à cette identité canton.')]);
        }

        Auth::login($user, remember: false);
        $request->session()->regenerate();

        return redirect()->intended(route('filament.admin.pages.dashboard'));
    }

    /**
     * Local-only shortcut: log in as an existing admin without a real AD FS
     * instance. The route this action is bound to is only registered when
     * `app()->environment('local')` (see routes/web.php); this extra check
     * is defense in depth so the method 404s if ever reached elsewhere.
     */
    public function fakeLogin(Request $request): RedirectResponse
    {
        abort_unless(app()->environment('local'), 404);

        $user = User::query()->where('role', UserRole::Admin)->first();

        abort_if($user === null, 404, 'Aucun administrateur trouvé. Lancez le DemoSeeder pour en créer un.');

        Auth::login($user, remember: false);
        $request->session()->regenerate();

        return redirect()->intended(route('filament.admin.pages.dashboard'));
    }

    /**
     * Check whether the AD FS user belongs to the configured allowed group.
     *
     * When ADFS_ALLOWED_GROUP is not configured, all authenticated AD FS users
     * pass this check and access is controlled solely by the role column.
     */
    private function isGroupAllowed(SocialiteUser $socialiteUser): bool
    {
        $allowedGroup = config('services.adfs.allowed_group');

        if (empty($allowedGroup)) {
            return true;
        }

        $groups = $socialiteUser->getRaw()['groups'] ?? [];

        return in_array($allowedGroup, (array) $groups, strict: true);
    }

    /**
     * Find an existing admin by adfs_id or email, or provision a new one (JIT).
     *
     * JIT provisioning is only active when ADFS_JIT_PROVISIONING=true.
     * When disabled, the user must already exist in the database with role=Admin.
     */
    private function findOrProvisionAdmin(SocialiteUser $socialiteUser): ?User
    {
        /** @var User|null $user */
        $user = User::where('adfs_id', $socialiteUser->getId())
            ->orWhere('email', $socialiteUser->getEmail())
            ->first();

        if ($user !== null) {
            // Sync adfs_id on first OIDC login for pre-registered admins.
            if ($user->adfs_id === null) {
                $user->adfs_id = $socialiteUser->getId();
                $user->save();
            }

            // Reject if the existing user is not an admin.
            if (! $user->isAdmin()) {
                return null;
            }

            return $user;
        }

        // JIT provisioning — only when explicitly enabled.
        if (! config('services.adfs.jit_provisioning')) {
            return null;
        }

        return User::create([
            'name' => $socialiteUser->getName(),
            'email' => $socialiteUser->getEmail(),
            'email_verified_at' => now(),
            'adfs_id' => $socialiteUser->getId(),
            'password' => null,
            'role' => UserRole::Admin,
        ]);
    }
}
