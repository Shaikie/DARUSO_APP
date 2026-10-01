<?php

namespace Database\Factories;

use App\Enums\DocumentCategory;
use App\Enums\DocumentVisibility;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->sentence(),
            'category' => $this->faker->randomElement(DocumentCategory::values()),
            'uploader_id' => User::factory(),
            'visibility' => DocumentVisibility::Students->value,
            'file_path' => 'documents/test.txt',
            'file_name' => 'test.txt',
            'file_mime_type' => 'text/plain',
            'file_size' => 128,
            'version' => 1,
        ];
    }

    public function public(): static
    {
        return $this->state(fn (): array => ['visibility' => DocumentVisibility::Public->value]);
    }

    /**
     * Store a real file on the upload disk so download tests exercise storage.
     */
    public function withStoredFile(?string $contents = null): static
    {
        return $this->afterCreating(function (Document $document) use ($contents): void {
            $disk = Storage::disk(config('daruso.uploads.disk'));
            $disk->put($document->file_path, $contents ?? 'Test document contents.');

            $document->update(['file_size' => $disk->size($document->file_path)]);
        });
    }
}
