<?php

namespace App\Services;

use App\Database\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UsersService
{

    public function __construct() {}

    public function create(string $email, string $name, UserRole $role = UserRole::Artist, ?string $adfsId = null, ?string $magicLink = null): User
    {
        $data = [
            'uuid' => (string) Str::uuid(),
            'email' => $email,
            'name' => $name,
            'enum_role' => $role,
            'adfs_id' => $adfsId,
            'magic_link' => $magicLink,
        ];

        Validator::make($data, User::getRules())->validate();

        return User::create($data);
    }
}
