<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'lead_id',
        'campaign_id',
        'email_campaign_id',
        'email_account_id',
        'direction',
        'subject',
        'body',
        'from_email',
        'to_email',
        'message_id',
        'thread_id',
        'status',
        'delivery_status',
        'sent_at',
        'replied_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function emailCampaign()
    {
        return $this->belongsTo(EmailCampaign::class);
    }

    public function account()
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }
}