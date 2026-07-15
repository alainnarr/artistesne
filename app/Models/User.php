<?php

namespace App\Models;

/**
 * @deprecated Use App\Database\Models\User directly.
 * This alias exists for backward-compatibility with Laravel internals
 * that reference the auth model via string (config/auth.php).
 */
class User extends \App\Database\Models\User {}
