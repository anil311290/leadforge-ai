<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceCaseStudy extends Model
{
    protected $fillable = [
        'service_id',
        'title',
        'summary',
        'client',
        'industry',
        'outcome',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}