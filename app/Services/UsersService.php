<?php

namespace App\Services;

use App\Database\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UsersService
{
    public function create(
        string $email,
        string $name,
        UserRole $role = UserRole::ARTIST,
        ?string $adfsId = null,
        ?string $magic_link_token = null,
        ?string $magic_link_sent_at = null
    ): User
    {
        $data = [
            'uuid' => (string) Str::uuid(),
            'email' => $email,
            'name' => $name,
            'enum_role' => $role,
            'adfs_id' => $adfsId,
            'magic_link_token' => $magic_link_token,
            'magic_link_sent_at' => $magic_link_sent_at,
        ];

        $user = User::where('email', $email)->first();
        if ($user) {
            return $user;
        }
        Validator::make($data, User::getRules([], $user))->validate();

        return User::firstOrCreate(['email' => $email], $data);
    }
}
