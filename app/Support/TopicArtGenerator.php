<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class TopicArtGenerator
{
    private const VIEWBOX = '0 0 800 500';

    /**
     * Build a unique, seed-tinted SVG for any card topic.
     */
    public static function render(string $topic, string $seed, ?string $title = null, ?string $contentHint = null): string
    {
        $topic = self::normalizeTopic($topic);
        $seed = Str::slug($seed) ?: 'default';
        $palette = self::paletteFromSeed($seed);
        $accents = self::topicAccents($topic);
        $particles = self::particlesFromSeed($seed);
        $monogram = self::monogram($title);
        $base = self::loadBaseSvg($topic);
        $motif = TopicArtMotifs::render($topic, $seed, $title, $contentHint, $accents);

        $overlay = self::dynamicOverlay($topic, $palette, $accents, $particles, $monogram, $seed).$motif;

        return self::injectOverlay($base, $overlay, $seed);
    }

    public static function normalizeTopic(string $topic): string
    {
        $topic = Str::slug($topic);

        return array_key_exists($topic, SiteTopicArt::topicFiles())
            ? $topic
            : 'default';
    }

    /**
     * @return array{gold: string, goldLight: string, navy: string, cream: string, angle: int}
     */
    public static function paletteFromSeed(string $seed): array
    {
        $hash = crc32($seed);

        return [
            'gold' => self::shiftGold('#b8892a', ($hash % 28) - 14),
            'goldLight' => self::shiftGold('#d4a843', (($hash >> 4) % 28) - 14),
            'navy' => self::shiftNavy('#1a2332', (($hash >> 8) % 12) - 6),
            'cream' => '#ece6dc',
            'angle' => 115 + ($hash % 50),
        ];
    }

    /**
     * @return list<array{x: int, y: int, r: int, o: float}>
     */
    public static function particlesFromSeed(string $seed): array
    {
        $hash = crc32($seed.'-particles');
        $particles = [];

        for ($i = 0; $i < 14; $i++) {
            $particles[] = [
                'x' => 40 + (($hash >> ($i * 3)) % 720),
                'y' => 24 + (($hash >> ($i * 5 + 2)) % 452),
                'r' => 1 + (($hash >> ($i * 2 + 1)) % 4),
                'o' => round(0.18 + (($hash >> ($i * 4)) % 55) / 100, 2),
            ];
        }

        return $particles;
    }

    /**
     * @return array{accent: string, accentSoft: string, violet: string, cyan: string}
     */
    public static function topicAccents(string $topic): array
    {
        return match ($topic) {
            'communion', 'worship', 'worship-location', 'online-worship', 'service-times', 'home', 'give', 'gallery' => [
                'accent' => '#e8c56a',
                'accentSoft' => '#f5dfa0',
                'violet' => '#c4b5fd',
                'cyan' => '#67e8f9',
            ],
            'prayer', 'prayer-groups', 'prayer-request' => [
                'accent' => '#a5b4fc',
                'accentSoft' => '#c7d2fe',
                'violet' => '#818cf8',
                'cyan' => '#7dd3fc',
            ],
            'youth-fellowship', 'sunday-school' => [
                'accent' => '#fbbf24',
                'accentSoft' => '#fde68a',
                'violet' => '#f472b6',
                'cyan' => '#34d399',
            ],
            'news' => [
                'accent' => '#c4b5fd',
                'accentSoft' => '#ddd6fe',
                'violet' => '#a78bfa',
                'cyan' => '#67e8f9',
            ],
            'event', 'fellowship', 'community-fellowship', 'events', 'welcome', 'new-member', 'register' => [
                'accent' => '#d4a843',
                'accentSoft' => '#f0d78c',
                'violet' => '#f9a8d4',
                'cyan' => '#5eead4',
            ],
            'sermon', 'resource', 'sermons', 'resources', 'liturgy', 'lectionary', 'our-church', 'privacy-policy', 'terms-of-use' => [
                'accent' => '#d4a843',
                'accentSoft' => '#ece6dc',
                'violet' => '#94a3b8',
                'cyan' => '#38bdf8',
            ],
            'choir' => [
                'accent' => '#d4a843',
                'accentSoft' => '#f5dfa0',
                'violet' => '#f9a8d4',
                'cyan' => '#67e8f9',
            ],
            'mission', 'steci-heritage', 'mission-vision', 'evangelism-mission' => [
                'accent' => '#d4a843',
                'accentSoft' => '#ece6dc',
                'violet' => '#818cf8',
                'cyan' => '#22d3ee',
            ],
            'pastoral-care', 'leadership', 'contact', 'safeguarding', 'login', 'forgot-password', 'reset-password' => [
                'accent' => '#c4b5fd',
                'accentSoft' => '#ddd6fe',
                'violet' => '#a78bfa',
                'cyan' => '#7dd3fc',
            ],
            'ministry-default', 'ministries' => [
                'accent' => '#d4a843',
                'accentSoft' => '#ece6dc',
                'violet' => '#c4b5fd',
                'cyan' => '#34d399',
            ],
            'uk-locations' => [
                'accent' => '#e8c56a',
                'accentSoft' => '#f5dfa0',
                'violet' => '#67e8f9',
                'cyan' => '#22d3ee',
            ],
            default => [
                'accent' => '#d4a843',
                'accentSoft' => '#ece6dc',
                'violet' => '#c4b5fd',
                'cyan' => '#67e8f9',
            ],
        };
    }

    public static function monogram(?string $title): string
    {
        $letter = strtoupper(substr(trim((string) $title), 0, 1));

        if ($letter === '' || ! preg_match('/^[A-Z0-9]$/', $letter)) {
            return '';
        }

        return $letter;
    }

    private static function loadBaseSvg(string $topic): string
    {
        $file = SiteTopicArt::topicFiles()[$topic] ?? SiteTopicArt::topicFiles()['default'];
        $path = public_path('images/topics/'.$file);

        if (! File::exists($path)) {
            $path = public_path('images/topics/default.svg');
        }

        return File::get($path);
    }

    /**
     * @param  array{gold: string, goldLight: string, navy: string, cream: string, angle: int}  $palette
     * @param  array{accent: string, accentSoft: string, violet: string, cyan: string}  $accents
     * @param  list<array{x: int, y: int, r: int, o: float}>  $particles
     */
    private static function dynamicOverlay(
        string $topic,
        array $palette,
        array $accents,
        array $particles,
        string $monogram,
        string $seed,
    ): string {
        $id = 'dyn-'.substr(md5($seed), 0, 10);
        $hash = crc32($seed.'-mesh');
        $meshOffset = ($hash % 120) - 60;
        $particleMarkup = collect($particles)
            ->map(function (array $p, int $index) use ($accents, $palette): string {
                $fill = $index % 3 === 0 ? $accents['cyan'] : ($index % 3 === 1 ? $accents['violet'] : $palette['goldLight']);

                return sprintf(
                    '<circle cx="%d" cy="%d" r="%d" fill="%s" opacity="%.2f"/>',
                    $p['x'],
                    $p['y'],
                    $p['r'],
                    $fill,
                    $p['o'],
                );
            })
            ->implode('');

        $monogramMarkup = $monogram === ''
            ? ''
            : sprintf(
                '<text x="400" y="290" text-anchor="middle" font-family="Georgia, serif" font-size="240" font-weight="700" fill="%s" opacity="0.06">%s</text>',
                $palette['cream'],
                htmlspecialchars($monogram, ENT_QUOTES),
            );

        return <<<SVG
<defs>
  <linearGradient id="{$id}-sheen" x1="0%" y1="0%" x2="100%" y2="100%">
    <stop offset="0%" stop-color="{$accents['accentSoft']}" stop-opacity="0.28"/>
    <stop offset="38%" stop-color="{$palette['gold']}" stop-opacity="0.1"/>
    <stop offset="72%" stop-color="{$accents['violet']}" stop-opacity="0.08"/>
    <stop offset="100%" stop-color="{$palette['navy']}" stop-opacity="0"/>
  </linearGradient>
  <linearGradient id="{$id}-beam" gradientTransform="rotate({$palette['angle']} 0.5 0.5)">
    <stop offset="0%" stop-color="#ffffff" stop-opacity="0"/>
    <stop offset="46%" stop-color="#ffffff" stop-opacity="0.16"/>
    <stop offset="54%" stop-color="#ffffff" stop-opacity="0.16"/>
    <stop offset="100%" stop-color="#ffffff" stop-opacity="0"/>
  </linearGradient>
  <linearGradient id="{$id}-aurora" x1="0%" y1="100%" x2="100%" y2="0%">
    <stop offset="0%" stop-color="{$accents['cyan']}" stop-opacity="0"/>
    <stop offset="45%" stop-color="{$accents['violet']}" stop-opacity="0.12"/>
    <stop offset="100%" stop-color="{$accents['accent']}" stop-opacity="0"/>
  </linearGradient>
  <radialGradient id="{$id}-pulse" cx="72%" cy="18%" r="58%">
    <stop offset="0%" stop-color="{$accents['accentSoft']}" stop-opacity="0.22"/>
    <stop offset="100%" stop-color="{$accents['accent']}" stop-opacity="0"/>
  </radialGradient>
  <radialGradient id="{$id}-depth" cx="18%" cy="82%" r="52%">
    <stop offset="0%" stop-color="{$accents['cyan']}" stop-opacity="0.14"/>
    <stop offset="100%" stop-color="{$accents['violet']}" stop-opacity="0"/>
  </radialGradient>
  <filter id="{$id}-noise" x="0%" y="0%" width="100%" height="100%">
    <feTurbulence type="fractalNoise" baseFrequency="0.85" numOctaves="2" stitchTiles="stitch" result="n"/>
    <feColorMatrix type="saturate" values="0" in="n" result="g"/>
    <feComponentTransfer in="g"><feFuncA type="table" tableValues="0 0.035"/></feComponentTransfer>
  </filter>
  <pattern id="{$id}-mesh" width="56" height="56" patternUnits="userSpaceOnUse" patternTransform="translate({$meshOffset} 0)">
    <path d="M56 0H0V56" fill="none" stroke="{$accents['accentSoft']}" stroke-width="0.65" opacity="0.08"/>
    <path d="M28 0v56M0 28h56" fill="none" stroke="{$accents['cyan']}" stroke-width="0.35" opacity="0.05"/>
  </pattern>
</defs>
<rect width="800" height="500" fill="url(#{$id}-pulse)"/>
<rect width="800" height="500" fill="url(#{$id}-depth)"/>
<rect width="800" height="500" fill="url(#{$id}-aurora)" opacity="0.85"/>
<rect width="800" height="500" fill="url(#{$id}-mesh)"/>
<rect width="800" height="500" fill="url(#{$id}-sheen)"/>
<rect width="800" height="500" fill="url(#{$id}-beam)" opacity="0.62"/>
<rect width="800" height="500" filter="url(#{$id}-noise)" opacity="0.55"/>
{$monogramMarkup}
<g opacity="0.95">{$particleMarkup}</g>
<path d="M48 48 H148 M48 48 V148 M752 48 H652 M752 48 V148 M48 452 H148 M48 452 V352 M752 452 H652 M752 452 V352" fill="none" stroke="{$accents['accentSoft']}" stroke-width="2" opacity="0.22" stroke-linecap="round"/>
<rect x="20" y="20" width="760" height="460" rx="30" fill="none" stroke="{$palette['goldLight']}" stroke-width="1.5" opacity="0.16"/>
<path d="M0 468 Q200 430 400 468 T800 468" fill="none" stroke="{$accents['accent']}" stroke-width="1.5" opacity="0.18"/>
SVG;
    }

    private static function injectOverlay(string $baseSvg, string $overlay, string $seed): string
    {
        $tinted = self::tintBaseGold($baseSvg, self::paletteFromSeed($seed));

        return str_replace('</svg>', $overlay.'</svg>', $tinted);
    }

    /**
     * @param  array{gold: string, goldLight: string}  $palette
     */
    private static function tintBaseGold(string $svg, array $palette): string
    {
        return str_replace(
            ['#b8892a', '#d4a843'],
            [$palette['gold'], $palette['goldLight']],
            $svg,
        );
    }

    private static function shiftGold(string $hex, int $shift): string
    {
        return self::shiftHex($hex, $shift, 0.9, 1.05);
    }

    private static function shiftNavy(string $hex, int $shift): string
    {
        return self::shiftHex($hex, (int) ($shift / 2), 0.98, 1.02);
    }

    private static function shiftHex(string $hex, int $shift, float $min, float $max): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $factor = 1 + ($shift / 100);
        $factor = max($min, min($max, $factor));

        $r = (int) max(0, min(255, round($r * $factor)));
        $g = (int) max(0, min(255, round($g * $factor)));
        $b = (int) max(0, min(255, round($b * $factor)));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
