<?php

use App\Models\Setting;
use App\Support\SitePaths;

if (! function_exists('public_upload_url')) {
    function public_upload_url(?string $path): ?string
    {
        return Setting::assetUrl($path);
    }
}

if (! function_exists('public_upload_exists')) {
    function public_upload_exists(?string $path): bool
    {
        return SitePaths::publicUploadExists($path);
    }
}
