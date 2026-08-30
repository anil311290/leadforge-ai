<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailCampaign extends Model
{
    protected $fillable = [
        'campaign_id',
        'email_account_id',
        'name',
        'status',
        'auto_send',
    ];

    protected function casts(): array
    {
        return [
            'auto_send' => 'boolean',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function account()
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }

    public function messages()
    {
        return $this->hasMany(EmailMessage::class);
    }
}