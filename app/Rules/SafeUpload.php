<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Inspects the *contents* of an upload and rejects anything that looks like
 * executable or scripted content.
 *
 * Laravel's `mimes` rule validates the extension implied by the MIME type
 * reported for the file. That value can still be influenced by the client, so
 * this rule adds a second, independent check: it reads the actual bytes with
 * fileinfo and refuses formats that a browser or server could execute.
 */
class SafeUpload implements ValidationRule
{
    /**
     * Content types that must never be accepted.
     *
     * @var list<string>
     */
    private const BLOCKED = [
        'text/x-php',
        'application/x-php',
        'application/x-httpd-php',
        'application/x-httpd-php-source',
        'text/x-python',
        'text/x-shellscript',
        'application/x-sh',
        'application/x-shellscript',
        'application/javascript',
        'text/javascript',
        'application/x-executable',
        'application/x-elf',
        'application/x-msdownload',
        'application/x-dosexec',
        'application/vnd.microsoft.portable-executable',
    ];

    /**
     * Byte patterns that indicate script content regardless of the reported type.
     *
     * @var list<string>
     */
    private const SIGNATURES = ['<?php', '<?=', '<script'];

    public function __construct(
        private readonly int $maxSizeKb,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            return;
        }

        $path = $value->getRealPath();

        if ($path === false || ! is_readable($path)) {
            $fail('The uploaded file could not be read for validation.');

            return;
        }

        $sizeKb = (int) ceil($value->getSize() / 1024);

        if ($sizeKb > $this->maxSizeKb) {
            $fail("The file may not be larger than {$this->maxSizeKb} KB.");

            return;
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path);

        if ($mime !== false && in_array($mime, self::BLOCKED, true)) {
            $fail("Files of type {$mime} are not permitted.");

            return;
        }

        $head = (string) file_get_contents($path, false, null, 0, 1024);

        foreach (self::SIGNATURES as $signature) {
            if (stripos($head, $signature) !== false) {
                $fail('Files containing script content are not permitted.');

                return;
            }
        }
    }
}
