<?php

namespace App\Models;

use Database\Factories\ClipPlatformUploadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['clip_id', 'platform_type', 'platform_id', 'external_id', 'status', 'metadata'])]
class ClipPlatformUpload extends Model
{
    /** @use HasFactory<ClipPlatformUploadFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Clip, $this>
     */
    public function clip(): BelongsTo
    {
        return $this->belongsTo(Clip::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function platform(): MorphTo
    {
        return $this->morphTo();
    }
}
