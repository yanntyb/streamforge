<?php

namespace App\Models;

use Database\Factories\ClipFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'title', 'original_filename', 'thumbnail_path'])]
class Clip extends Model
{
    /** @use HasFactory<ClipFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<ClipPlatformUpload, $this>
     */
    public function platformUploads(): HasMany
    {
        return $this->hasMany(ClipPlatformUpload::class);
    }
}
