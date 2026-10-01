<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Authorized attachment downloads.
 *
 * A single gateway for files attached to announcements, complaints, meetings and
 * events. Ownership is decided by the parent model's policy, so this controller
 * never grants access on its own.
 */
class AttachmentController extends Controller
{
    public function download(Request $request, Attachment $attachment): StreamedResponse
    {
        abort_unless($this->canAccess($request, $attachment), 403);

        $disk = Storage::disk($attachment->disk);

        abort_unless($disk->exists($attachment->file_path), 404, 'The stored file is missing.');

        // Served as an attachment with a neutral content type: an uploaded
        // HTML or SVG file must never render in the application's origin.
        return $disk->download($attachment->file_path, $attachment->file_name, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    /**
     * Delegate the decision to the parent model's policy.
     */
    private function canAccess(Request $request, Attachment $attachment): bool
    {
        $parent = $attachment->attachable;

        if ($parent === null) {
            return false;
        }

        return $request->user()->can('view', $parent);
    }
}
