<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Hash;

class HashHelper
{
    /**
     * Hash password using bcrypt.
     */
    public static function make(string $password): string
    {
        return Hash::make($password);
    }

    /**
     * Verify password (supports PBKDF2, bcrypt, and plain text).
     */
    public static function check(string $password, string $hashedPassword): bool
    {
        if (str_starts_with($hashedPassword, 'pbkdf2$')) {
            $parts = explode('$', $hashedPassword);
            if (count($parts) !== 4) {
                return false;
            }
            
            $iterations = (int)$parts[1];
            $salt = $parts[2];
            $hash = $parts[3];
            
            $testHash = bin2hex(hash_pbkdf2('sha512', $password, $salt, $iterations, 64, true));
            return hash_equals($hash, $testHash);
        }

        // Fallback for legacy plain text passwords in database
        // Bcrypt hashes start with $2y$ or $2a$ (or $2x$)
        if (!str_starts_with($hashedPassword, '$2y$') && 
            !str_starts_with($hashedPassword, '$2a$') && 
            !str_starts_with($hashedPassword, '$2b$') &&
            !str_starts_with($hashedPassword, '$2x$')) {
            return $password === $hashedPassword;
        }

        return Hash::check($password, $hashedPassword);
    }
}
