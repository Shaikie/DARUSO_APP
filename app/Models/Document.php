<?php

namespace App\Models;

use App\Enums\DocumentCategory;
use App\Enums\DocumentVisibility;
use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * A versioned document (constitution, minutes, policy, …).
 *
 * Files live on the private disk. A new upload supersedes the previous row
 * rather than overwriting it, so older versions remain auditable.
 */
class Document extends Model
{
    use HasFactory;
    use Searchable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'category',
        'uploader_id',
        'visibility',
        'ministry_id',
        'committee_id',
        'group_id',
        'file_path',
        'file_name',
        'file_mime_type',
        'file_size',
        'version',
        'supersedes_id',
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    /**
     * @return BelongsTo<Ministry, $this>
     */
    public function ministry(): BelongsTo
    {
        return $this->belongsTo(Ministry::class);
    }

    /**
     * @return BelongsTo<Committee, $this>
     */
    public function committee(): BelongsTo
    {
        return $this->belongsTo(Committee::class);
    }

    /**
     * @return BelongsTo<Document, $this>
     */
    public function supersedes(): BelongsTo
    {
        return $this->belongsTo(self::class, 'supersedes_id');
    }

    public function fileExists(): bool
    {
        return Storage::disk(config('daruso.uploads.disk'))->exists($this->file_path);
    }

    /**
     * Human-readable file size for display.
     */
    public function humanReadableSize(): string
    {
        $bytes = (int) $this->file_size;

        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);

        return round($bytes / (1024 ** $power), $power === 0 ? 0 : 1).' '.$units[$power];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => DocumentCategory::class,
            'visibility' => DocumentVisibility::class,
            'file_size' => 'integer',
            'version' => 'integer',
        ];
    }
}
