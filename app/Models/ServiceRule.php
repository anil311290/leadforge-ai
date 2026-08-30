<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRule extends Model
{
    protected $fillable = [
        'service_id',
        'type',
        'signal',
        'keyword',
        'weight',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}