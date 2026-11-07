<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use MongoDB\Laravel\Eloquent\DocumentModel;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use DocumentModel;

    protected $connection = 'mongodb';

    protected $collection = 'personal_access_tokens';

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'token',
        'abilities',
        'expires_at',
        'tokenable_id',
        'tokenable_type',
        'last_used_at',
    ];

    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $hidden = [
        'token',
    ];

    public function tokenable()
    {
        return $this->morphTo('tokenable', 'tokenable_type', 'tokenable_id');
    }

    public static function findToken($token)
    {
        if (str_contains($token, '|')) {
            [$id, $token] = explode('|', $token, 2);

            if ($instance = static::find($id)) {
                return hash_equals($instance->token, hash('sha256', $token)) ? $instance : null;
            }

            return null;
        }

        return static::where('token', hash('sha256', $token))->first();
    }

    public function can($ability): bool
    {
        $abilities = $this->abilities ?? [];

        return in_array('*', $abilities, true) ||
               in_array($ability, $abilities, true);
    }

    public function cant($ability): bool
    {
        return ! $this->can($ability);
    }
}
