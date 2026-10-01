<?php

namespace Database\Seeders;

use App\Enums\DocumentCategory;
use App\Enums\DocumentVisibility;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeds a small number of text documents on the private disk.
 *
 * Real files are written under the configured upload disk so downloads and
 * visibility rules can be exercised end-to-end.
 */
class DocumentSeeder extends Seeder
{
    public function run(): void
    {
        $secretary = User::where('email', 'secretary@daruso.local')->first();

        if ($secretary === null) {
            return;
        }

        $disk = Storage::disk(config('daruso.uploads.disk'));

        $documents = [
            [
                'title' => 'DARUSO Constitution',
                'description' => 'The governing constitution of the Daruso Students Organisation.',
                'category' => DocumentCategory::Constitution,
                'visibility' => DocumentVisibility::Public,
                'content' => "DARUSO CONSTITUTION\n\n1. Name\n2. Objectives\n3. Membership\n4. Leadership\n5. Meetings\n",
            ],
            [
                'title' => 'Finance Committee minutes (latest quarter)',
                'description' => 'Minutes of the most recent finance committee review meeting.',
                'category' => DocumentCategory::Minutes,
                'visibility' => DocumentVisibility::Leaders,
                'content' => "FINANCE COMMITTEE MINUTES\n\nPresent: 4\nApologies: 1\n\nFinancial statements reviewed and adopted.\n",
            ],
            [
                'title' => 'Student grievance procedure',
                'description' => 'How a student should lodge and follow up a complaint.',
                'category' => DocumentCategory::Policy,
                'visibility' => DocumentVisibility::Students,
                'content' => "STUDENT GRIEVANCE PROCEDURE\n\n1. Submit a complaint\n2. Track status\n3. Respond to requests for information\n",
            ],
        ];

        foreach ($documents as $data) {
            $existing = Document::where('title', $data['title'])->first();

            if ($existing !== null) {
                continue;
            }

            $path = "documents/{$data['category']->value}/".Str::slug($data['title']).'.txt';

            $disk->put($path, $data['content']);

            Document::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'category' => $data['category']->value,
                'uploader_id' => $secretary->getKey(),
                'visibility' => $data['visibility']->value,
                'file_path' => $path,
                'file_name' => Str::slug($data['title']).'.txt',
                'file_mime_type' => 'text/plain',
                'file_size' => $disk->size($path),
                'version' => 1,
            ]);
        }
    }
}
