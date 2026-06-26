<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class AppUser extends Authenticatable implements JWTSubject
{
    protected $table = 'app_users';
    
    // Disable auto-incrementing integer IDs as we use string UUIDs
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'data',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims(): array
    {
        $decoded = json_decode($this->data, true) ?? [];
        return [
            'id'       => $this->id,
            'email'    => $decoded['email'] ?? '',
            'provider' => $decoded['provider'] ?? 'phone',
        ];
    }
}
