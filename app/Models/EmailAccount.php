<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailAccount extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'email',
        'name',
        'oauth_tokens',
        'config',
        'is_active',
        'is_default',
        'status',
        'error',
    ];

    protected $hidden = [
        'oauth_tokens',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'oauth_tokens' => 'array',
            'config' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}