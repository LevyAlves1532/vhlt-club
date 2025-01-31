<?php

namespace App\Policies;

use App\Enum\PermissionsEnum;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserPolicy
{
    private $user;

    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function delete()
    {
        return $this->user->permission === PermissionsEnum::SUPER_ADMIN;
    }
}
