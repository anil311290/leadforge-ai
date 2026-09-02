<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiAnalysis extends Model
{
    protected $fillable = [
        'lead_id',
        'scan_id',
        'prompt_version_id',
        'model',
        'provider',
        'content_hash',
        'input',
        'output',
        'score',
        'confidence',
        'input_tokens',
        'output_tokens',
        'cost',
        'duration_ms',
        'status',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'confidence' => 'decimal:2',
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'cost' => 'decimal:4',
            'duration_ms' => 'integer',
            'input' => 'array',
            'output' => 'array',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function scan()
    {
        return $this->belongsTo(WebsiteScan::class, 'scan_id');
    }

    public function promptVersion()
    {
        return $this->belongsTo(PromptVersion::class);
    }
}