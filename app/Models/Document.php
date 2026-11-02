<?php

namespace App\Models;

use App\Enums\DocumentCategory;
use App\Enums\DocumentVisibility;
use App\Models\Concerns\Searchable;
use Illuminate\Database\Eloquent\Builder;
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
     * Restrict a query to the documents a user may read.
     *
     * This is the SQL mirror of DocumentPolicy::view. Keeping both in one model
     * prevents the listing and the policy from drifting apart.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $ministryIds = $user->ministryIds()->all();
        $committeeIds = $user->committeeIds()->all();
        $isLeader = $user->isLeader();

        return $query->where(function (Builder $inner) use ($user, $ministryIds, $committeeIds, $isLeader): void {
            $inner->whereIn('visibility', [
                DocumentVisibility::Public->value,
                DocumentVisibility::Students->value,
            ]);

            if ($isLeader) {
                $inner->orWhere('visibility', DocumentVisibility::Leaders->value);
            }

            if ($ministryIds !== []) {
                $inner->orWhere(function (Builder $ministry) use ($ministryIds): void {
                    $ministry->where('visibility', DocumentVisibility::Ministry->value)
                        ->whereIn('ministry_id', $ministryIds);
                });
            }

            if ($committeeIds !== []) {
                $inner->orWhere(function (Builder $committee) use ($committeeIds): void {
                    $committee->where('visibility', DocumentVisibility::Committee->value)
                        ->whereIn('committee_id', $committeeIds);
                });
            }

            $inner->orWhere(function (Builder $group) use ($user): void {
                $group->where('visibility', DocumentVisibility::Group->value)
                    ->where('group_id', $user->getKey());
            });

            $inner->orWhere('uploader_id', $user->getKey());
        });
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
