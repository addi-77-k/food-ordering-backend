<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use MongoDB\Laravel\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasApiTokens;

    protected $connection = 'mongodb';

    protected $collection = 'admins';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
