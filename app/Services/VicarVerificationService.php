<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;

class VicarVerificationService
{
    public function verifyingVicar(): ?User
    {
        return User::query()
            ->where('role', UserRole::Vicar->value)
            ->where('is_active', true)
            ->whereHas('media', fn ($query) => $query->where('collection_name', 'signature'))
            ->with(['designation', 'media'])
            ->orderBy('id')
            ->first();
    }

    /**
     * @return array{name: string, title: string, verified_at: string, signature_data_uri: string|null}|null
     */
    public function pdfVerificationBlock(): ?array
    {
        $vicar = $this->verifyingVicar();

        if (! $vicar) {
            return null;
        }

        $media = $vicar->getFirstMedia('signature');
        $dataUri = null;

        if ($media) {
            try {
                $path = $media->getPath();
                $mime = $media->mime_type ?: 'image/png';
                $contents = @file_get_contents($path);

                if ($contents !== false) {
                    $dataUri = 'data:'.$mime.';base64,'.base64_encode($contents);
                }
            } catch (\Throwable) {
                $dataUri = null;
            }
        }

        $title = $vicar->designationLabel() ?: 'Vicar';

        return [
            'name' => $vicar->displayFullName(),
            'title' => $title,
            'verified_at' => now()->format('j F Y'),
            'signature_data_uri' => $dataUri,
        ];
    }
}
