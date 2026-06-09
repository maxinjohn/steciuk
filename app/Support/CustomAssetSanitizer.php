<?php

namespace App\Support;

class CustomAssetSanitizer
{
    public static function css(?string $css): ?string
    {
        if ($css === null || trim($css) === '') {
            return null;
        }

        $css = strip_tags($css);
        $css = preg_replace('/<\/style>/i', '', $css) ?? $css;
        $css = preg_replace('/@import\b[^;]*;/i', '', $css) ?? $css;
        $css = preg_replace('/expression\s*\(/i', '', $css) ?? $css;
        $css = preg_replace('/javascript\s*:/i', '', $css) ?? $css;
        $css = preg_replace('/-moz-binding\s*:/i', '', $css) ?? $css;
        $css = preg_replace('/behavior\s*:/i', '', $css) ?? $css;
        $css = preg_replace('/@charset\b[^;]*;/i', '', $css) ?? $css;

        $css = trim($css);

        return $css === '' ? null : $css;
    }

    public static function js(?string $js): ?string
    {
        if (! config('security.allow_page_custom_js', false)) {
            return null;
        }

        if ($js === null || trim($js) === '') {
            return null;
        }

        $js = strip_tags($js);
        $js = preg_replace('/javascript\s*:/i', '', $js) ?? $js;
        $js = trim($js);

        return $js === '' ? null : $js;
    }
}
