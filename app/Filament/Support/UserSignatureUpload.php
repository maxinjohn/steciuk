<?php

namespace App\Filament\Support;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserSignatureUpload
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function fillFormData(User $user, array $data): array
    {
        if (! $user->canUploadVerificationSignature()) {
            return $data;
        }

        $media = $user->getFirstMedia('signature');

        if ($media) {
            $relative = 'users/signatures/'.$media->file_name;
            Storage::disk('public')->put($relative, $media->get());

            $data['signature_upload'] = [$relative];
        }

        return $data;
    }

    public static function persist(User $user, mixed $upload): void
    {
        if (! $user->canUploadVerificationSignature()) {
            $user->clearMediaCollection('signature');

            return;
        }

        if ($upload === null || $upload === [] || $upload === '') {
            return;
        }

        $paths = is_array($upload) ? array_values($upload) : [$upload];
        $storedPath = (string) ($paths[0] ?? '');

        if ($storedPath === '' || ! Storage::disk('public')->exists($storedPath)) {
            return;
        }

        $user->clearMediaCollection('signature');
        $user->addMedia(Storage::disk('public')->path($storedPath))
            ->usingFileName(basename($storedPath))
            ->toMediaCollection('signature');

        Storage::disk('public')->delete($storedPath);
    }
}
