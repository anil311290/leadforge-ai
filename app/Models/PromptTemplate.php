<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromptTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'active_version_id',
    ];

    public function versions()
    {
        return $this->hasMany(PromptVersion::class);
    }

    public function activeVersion()
    {
        return $this->hasOne(PromptVersion::class)->where('is_active', true);
    }
}