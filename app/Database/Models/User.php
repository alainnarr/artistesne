<?php

declare(strict_types=1);

namespace App\Database\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Database\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\Rules\Enum;
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

    protected $table = 'users';

    /** @return UserFactory<User, $this> */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    protected $fillable = [
        'uuid',
        'email',
        'name',
        'enum_role',
        'adfs_id',
        'magic_link_token',
        'magic_link_sent_at',
    ];

    protected $updatable = [
        'magic_link_token',
        'magic_link_sent_at',
    ];

    /** @return array<string, class-string|'datetime'> */
    protected function casts(): array
    {
        return [
            'enum_role' => UserRole::class,
            'magic_link_sent_at' => 'datetime',
        ];
    }

    /* * * * * * * * VALIDATION * * * * * * * */
    /** @return array<string, string|array> */
    public static function getRules(array $fields = [], $register = null): array
    {
        $id = $register['id'] ?? null;

        $rules = [
            'uuid' => 'required|string|max:36|unique:users,uuid,'.$id.',id',
            'email' => 'required|string|email|max:125|unique:users,email,'.$id.',id',
            'name' => 'required|string|max:125',
            'enum_role' => ['required', new Enum(UserRole::class)],
            'adfs_id' => 'nullable|string|max:255|unique:users,adfs_id,'.$id.',id',
            'magic_link_token' => 'nullable|string|max:255',
            'magic_link_sent_at' => 'nullable|date',
        ];

        if (empty($fields)) {
            return $rules;
        }

        return array_intersect_key($rules, array_flip($fields));
    }
    /* * * * * * * * END - VALIDATION * * * * * * * */

    /* * * * * * * * RELATIONS * * * * * * * */
    /** @return HasOne<Artist, $this> */
    public function artist(): HasOne
    {
        return $this->hasOne(Artist::class, 'user_id');
    }

    /** @return HasMany<Registration, $this> */
    public function reviewedRegistrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'reviewed_by');
    }

    /** @return HasMany<Link, $this> */
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
