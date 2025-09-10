<?php

namespace RaDevs\JwtAuth\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetCode extends Model
{
    protected $fillable = [
        'email', 'code_hash', 'attempts', 'max_attempts', 'expires_at', 'used_at', 'ip_address', 'user_agent',
    ];


    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];


    public function scopeActive($query)
    {
        return $query->whereNull('used_at')->where('expires_at', '>', now());
    }
}