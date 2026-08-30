<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model
{
    protected $fillable = [
        'lead_id',
        'email_message_id',
        'sequence_number',
        'scheduled_at',
        'content',
        'status',
        'sent_at',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'sequence_number' => 'integer',
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function emailMessage()
    {
        return $this->belongsTo(EmailMessage::class);
    }
}