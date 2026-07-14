<?php

namespace App\Database\Models;

use App\Database\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rules\Enum;
use App\Enums\UserRole;
use Filament\Panel;
use Illuminate\Support\Str;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use App\Database\Traits\PreventUpdate;
use App\Database\Traits\PreventDelete;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract, FilamentUser
{
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail, TwoFactorAuthenticatable;
    use HasFactory;
    use Notifiable;
    use PreventUpdate;
    use PreventDelete;

    protected $table = 'newusers';

    protected $fillable = [
        'uuid',
        'email',
        'name',
        'enum_role',
        'adfs_id',
        'magic_link'
    ];

    protected $updatable = [
        'magic_link'
    ];


    protected $casts = [
        'enum_role' => UserRole::class
    ];

    /* * * * * * * * VALIDATION * * * * * * * */
    public static function getRules(array $fields = [], $register = null): array
    {
        $id = $register['id'] ?? null;

        $rules = [
            'uuid' => 'required|string|max:36|unique:newusers,uuid,'.$id.',id',
            'email' => 'required|string|email|max:200|unique:newusers,email,'.$id.',id',
            'name' => 'required|string|max:125',
            'enum_role' => ['required', new Enum(UserRole::class)],
            'adfs_id' => 'nullable|string|max:255|unique:newusers,adfs_id,'.$id.',id',
            'magic_link' => 'nullable|string|max:255'
        ];

        if (empty($fields)) {
            return $rules;
        }

        return array_intersect_key($rules, array_flip($fields));
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    public function artist(): HasOne
    {
        return $this->hasOne(Artist::class, 'user_id');
    }

    public function reviewedRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'reviewed_by');
    }

    public function reviewedArtistsChangeRequests(): HasMany
    {
        return $this->hasMany(ArtistChangeRequest::class, 'reviewed_by');
    }
    /* * * * * * * * END - RELATIONS * * * * * * * */

    /* * * * * * * * ACL * * * * * * * */
    public function isAdmin(): bool
    {
        return $this->enum_role === UserRole::Admin;
    }

    public function isArtist(): bool
    {
        return $this->enum_role === UserRole::Artist;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' && $this->isAdmin();
    }
        /* * * * * * * * END - ACL * * * * * * * */

    /* * * * * * * * ACCESSORS * * * * * * * */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
    /* * * * * * * * END - ACCESSORS * * * * * * * */
}
