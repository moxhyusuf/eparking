<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Foundation\Auth\User as Authenticatable;

#[Unguarded]
#[Hidden(['password'])]

class User extends Authenticatable
{
    protected $table = 'user';

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
