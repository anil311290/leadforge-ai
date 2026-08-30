<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'min_value',
        'max_value',
        'currency',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_value' => 'decimal:2',
            'max_value' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function rules()
    {
        return $this->hasMany(ServiceRule::class);
    }

    public function caseStudies()
    {
        return $this->hasMany(ServiceCaseStudy::class);
    }
}