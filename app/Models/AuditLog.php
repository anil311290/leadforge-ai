<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'entity',
        'entity_id',
        'before',
        'after',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'entity_id' => 'integer',
            'before' => 'array',
            'after' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}