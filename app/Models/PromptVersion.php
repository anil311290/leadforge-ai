<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromptVersion extends Model
{
    protected $fillable = [
        'prompt_template_id',
        'version',
        'content',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function template()
    {
        return $this->belongsTo(PromptTemplate::class);
    }

    public function analyses()
    {
        return $this->hasMany(AiAnalysis::class);
    }
}