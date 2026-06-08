<?php

use Mews\Purifier\Facades\Purifier;

if (! function_exists('safeHtml')) {
    function safeHtml(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        return Purifier::clean($html);
    }
}

if (! function_exists('safeEmbed')) {
    function safeEmbed(?string $html): string
    {
        return \App\Support\EmbedSanitizer::iframe($html);
    }
}
