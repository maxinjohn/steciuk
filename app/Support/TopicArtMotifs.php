<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Seed-tinted, topic-aware illustration layer — recognisable line art per page.
 */
final class TopicArtMotifs
{
    /**
     * @param  array{accent: string, accentSoft: string, violet: string, cyan: string}  $accents
     */
    public static function render(
        string $topic,
        string $seed,
        ?string $title = null,
        ?string $contentHint = null,
        array $accents = [],
    ): string {
        $hash = crc32($seed.'|'.($title ?? '').'|'.Str::limit((string) $contentHint, 120, ''));
        $ox = 400 + (($hash % 28) - 14);
        $oy = 248 + ((($hash >> 5) % 20) - 10);
        $scale = 0.98 + ((($hash >> 9) % 10) / 100);
        $rotate = (($hash >> 13) % 8) - 4;

        $accent = $accents['accent'] ?? '#d4a843';
        $soft = $accents['accentSoft'] ?? '#ece6dc';
        $violet = $accents['violet'] ?? '#c4b5fd';
        $cyan = $accents['cyan'] ?? '#67e8f9';

        $inner = self::motifForTopic($topic, $title, $contentHint, $accent, $soft, $violet, $cyan, $hash);

        return sprintf(
            '<g transform="translate(%d %d) scale(%.2f) rotate(%d)" opacity="0.96">%s</g>',
            $ox,
            $oy,
            $scale,
            $rotate,
            $inner,
        );
    }

    private static function motifForTopic(
        string $topic,
        ?string $title,
        ?string $contentHint,
        string $accent,
        string $soft,
        string $violet,
        string $cyan,
        int $hash,
    ): string {
        $haystack = strtolower(trim(implode(' ', array_filter([(string) $title, (string) $contentHint]))));

        if ($topic === 'online-worship' || str_contains($haystack, 'online worship') || str_contains($haystack, 'live stream')) {
            return self::motifStream($accent, $soft, $cyan, $violet);
        }

        if (str_contains($haystack, 'communion') || str_contains($haystack, 'eucharist') || str_contains($haystack, 'lord\'s table')) {
            return self::motifCommunion($accent, $soft);
        }

        if (str_contains($haystack, 'safeguard') || str_contains($haystack, 'child protection')) {
            return self::motifSafeguarding($accent, $soft, $violet);
        }

        return match ($topic) {
            'home' => self::motifHome($accent, $soft, $violet, $cyan),
            'our-church' => self::churchBuilding($accent, $soft, $violet, $cyan),
            'welcome' => self::motifWelcome($accent, $soft, $violet, $cyan),
            'new-member', 'register' => self::motifMemberJoin($accent, $soft, $violet, $cyan),
            'login', 'forgot-password', 'reset-password' => self::motifMemberSignIn($accent, $soft, $violet, $cyan),
            'steci-heritage' => self::motifHeritage($accent, $soft, $violet, $cyan),
            'mission-vision' => self::motifMissionVision($accent, $soft, $cyan),
            'mission', 'evangelism-mission' => self::motifMission($accent, $soft, $cyan),
            'leadership' => self::motifLeadership($accent, $soft, $violet),
            'uk-locations' => self::motifLocations($accent, $cyan, $violet),
            'service-times' => self::motifServiceTimes($accent, $soft, $violet),
            'online-worship' => self::motifStream($accent, $soft, $cyan, $violet),
            'worship', 'worship-location' => self::motifWorshipGathering($accent, $soft, $violet, $cyan),
            'choir' => self::motifChoir($accent, $soft, $violet),
            'sunday-school' => self::motifSundaySchool($accent, $soft, $cyan),
            'youth-fellowship' => self::motifYouth($accent, $soft, $violet, $cyan),
            'womens-fellowship' => self::motifWomensFellowship($accent, $soft, $violet),
            'prayer', 'prayer-groups' => self::motifPrayer($accent, $soft, $violet),
            'prayer-request' => self::motifPrayerRequest($accent, $soft, $violet, $cyan),
            'communion' => self::motifCommunion($accent, $soft),
            'sermon', 'sermons' => self::motifSermon($accent, $soft, $violet),
            'news' => self::motifNews($accent, $violet, $cyan),
            'event', 'events' => self::motifEvent($accent, $soft, $violet, $cyan),
            'liturgy' => self::motifLiturgy($accent, $soft, $violet),
            'lectionary' => self::motifLectionary($accent, $soft, $cyan),
            'resource', 'resources' => self::motifScroll($accent, $soft, $violet),
            'gallery' => self::motifGallery($accent, $soft, $violet, $cyan),
            'give' => self::motifGive($accent, $soft, $violet),
            'pastoral-care', 'contact' => self::motifContact($accent, $soft, $violet),
            'safeguarding' => self::motifSafeguarding($accent, $soft, $violet),
            'privacy-policy', 'terms-of-use' => self::motifDocument($accent, $soft, $violet),
            'community-fellowship', 'fellowship' => self::motifFellowship($accent, $soft, $violet),
            'ministry-default', 'ministries' => self::motifServe($accent, $soft, $cyan),
            default => self::motifByTopic(
                self::resolveMotifTopic($topic, $title, $contentHint),
                $accent,
                $soft,
                $violet,
                $cyan,
                $hash,
            ),
        };
    }

    private static function resolveMotifTopic(string $topic, ?string $title, ?string $contentHint): string
    {
        if ($topic !== 'default') {
            return $topic;
        }

        $resolved = SiteTopicArt::resolve(null, $title, 'page', null, $contentHint);

        return $resolved !== 'default' ? $resolved : 'default';
    }

    private static function motifByTopic(
        string $topic,
        string $accent,
        string $soft,
        string $violet,
        string $cyan,
        int $hash,
    ): string {
        return match ($topic) {
            'home' => self::motifHome($accent, $soft, $violet, $cyan),
            'our-church' => self::churchBuilding($accent, $soft, $violet, $cyan),
            'welcome' => self::motifWelcome($accent, $soft, $violet, $cyan),
            'new-member', 'register' => self::motifMemberJoin($accent, $soft, $violet, $cyan),
            'login', 'forgot-password', 'reset-password' => self::motifMemberSignIn($accent, $soft, $violet, $cyan),
            'steci-heritage' => self::motifHeritage($accent, $soft, $violet, $cyan),
            'mission-vision' => self::motifMissionVision($accent, $soft, $cyan),
            'mission', 'evangelism-mission' => self::motifMission($accent, $soft, $cyan),
            'leadership' => self::motifLeadership($accent, $soft, $violet),
            'uk-locations' => self::motifLocations($accent, $cyan, $violet),
            'service-times' => self::motifServiceTimes($accent, $soft, $violet),
            'online-worship' => self::motifStream($accent, $soft, $cyan, $violet),
            'worship', 'worship-location' => self::motifWorshipGathering($accent, $soft, $violet, $cyan),
            'choir' => self::motifChoir($accent, $soft, $violet),
            'sunday-school' => self::motifSundaySchool($accent, $soft, $cyan),
            'youth-fellowship' => self::motifYouth($accent, $soft, $violet, $cyan),
            'womens-fellowship' => self::motifWomensFellowship($accent, $soft, $violet),
            'prayer', 'prayer-groups' => self::motifPrayer($accent, $soft, $violet),
            'prayer-request' => self::motifPrayerRequest($accent, $soft, $violet, $cyan),
            'communion' => self::motifCommunion($accent, $soft),
            'sermon', 'sermons' => self::motifSermon($accent, $soft, $violet),
            'news' => self::motifNews($accent, $violet, $cyan),
            'event', 'events' => self::motifEvent($accent, $soft, $violet, $cyan),
            'liturgy' => self::motifLiturgy($accent, $soft, $violet),
            'lectionary' => self::motifLectionary($accent, $soft, $cyan),
            'resource', 'resources' => self::motifScroll($accent, $soft, $violet),
            'gallery' => self::motifGallery($accent, $soft, $violet, $cyan),
            'give' => self::motifGive($accent, $soft, $violet),
            'pastoral-care', 'contact' => self::motifContact($accent, $soft, $violet),
            'safeguarding' => self::motifSafeguarding($accent, $soft, $violet),
            'privacy-policy', 'terms-of-use' => self::motifDocument($accent, $soft, $violet),
            'community-fellowship', 'fellowship' => self::motifFellowship($accent, $soft, $violet),
            'ministry-default', 'ministries' => self::motifServe($accent, $soft, $cyan),
            default => self::motifFromHash($accent, $soft, $violet, $cyan, $hash),
        };
    }

    private static function neonRing(string $color, int $r, float $opacity = 0.35): string
    {
        return sprintf(
            '<circle cx="0" cy="0" r="%d" fill="none" stroke="%s" stroke-width="1.5" opacity="%.2f"/>',
            $r,
            $color,
            $opacity,
        );
    }

    private static function churchBuilding(string $accent, string $soft, string $violet, string $cyan): string
    {
        return self::neonRing($violet, 102, 0.16)
            .self::neonRing($accent, 80, 0.3)
            .'<path d="M-58 10 L0 -56 L58 10z" fill="none" stroke="'.$soft.'" stroke-width="2.75"/>'
            .'<path d="M-58 10v54h116V10" fill="none" stroke="'.$soft.'" stroke-width="2.75"/>'
            .'<path d="M-14 -56 L14 -56 L10 -92 L-10 -92z" fill="none" stroke="'.$accent.'" stroke-width="2.75"/>'
            .'<path d="M0 -104 L0 -86M-10 -95 L10 -95" stroke="'.$accent.'" stroke-width="3.25"/>'
            .'<path d="M-72 10 L-88 22v34h16V10M72 10 L88 22v34H72V10" fill="none" stroke="'.$accent.'" stroke-width="2.25" opacity="0.82"/>'
            .'<path d="M-16 64 Q0 48 16 64" fill="none" stroke="'.$accent.'" stroke-width="2.75"/>'
            .'<path d="M-16 64v-18h32v18" fill="none" stroke="'.$accent.'" stroke-width="2"/>'
            .'<circle cx="-34" cy="30" r="11" fill="none" stroke="'.$cyan.'" stroke-width="2" opacity="0.78"/>'
            .'<circle cx="34" cy="30" r="11" fill="none" stroke="'.$cyan.'" stroke-width="2" opacity="0.78"/>'
            .'<path d="M-8 22 L0 12 L8 22" fill="none" stroke="'.$violet.'" stroke-width="2" opacity="0.72"/>';
    }

    private static function motifHome(string $accent, string $soft, string $violet, string $cyan): string
    {
        return self::churchBuilding($accent, $soft, $violet, $cyan);
    }

    private static function motifWelcome(string $accent, string $soft, string $violet, string $cyan): string
    {
        return self::neonRing($cyan, 92, 0.22)
            .self::churchBuilding($accent, $soft, $violet, $cyan)
            .'<path d="M-24 64 Q0 52 24 64" fill="none" stroke="'.$cyan.'" stroke-width="2.5" opacity="0.85"/>'
            .'<path d="M-36 74 L0 88 L36 74" fill="none" stroke="'.$soft.'" stroke-width="2" opacity="0.7"/>';
    }

    private static function motifHeritage(string $accent, string $soft, string $violet, string $cyan): string
    {
        return self::neonRing($violet, 94, 0.22)
            .'<circle cx="0" cy="-8" r="46" fill="none" stroke="'.$soft.'" stroke-width="2.5" opacity="0.55"/>'
            .'<ellipse cx="0" cy="-8" rx="46" ry="18" fill="none" stroke="'.$accent.'" stroke-width="2"/>'
            .'<path d="M-46 -8h92" stroke="'.$accent.'" stroke-width="1.5" opacity="0.45"/>'
            .'<path d="M0 -54 L0 38" stroke="'.$soft.'" stroke-width="2.5"/>'
            .'<path d="M0 -54 L-12 -34h24z" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<path d="M-34 28h68v20c-34 8-68 8-68 0z" fill="none" stroke="'.$cyan.'" stroke-width="2"/>'
            .'<path d="M0 28v20" stroke="'.$accent.'" stroke-width="1.5"/>';
    }

    private static function motifMissionVision(string $accent, string $soft, string $cyan): string
    {
        return self::neonRing($cyan, 90, 0.24)
            .'<circle cx="0" cy="0" r="50" fill="none" stroke="'.$soft.'" stroke-width="2" opacity="0.4"/>'
            .'<path d="M0 -58 L0 58M-58 0 L58 0" stroke="'.$accent.'" stroke-width="2" opacity="0.5"/>'
            .'<path d="M0 -58 L-12 -38h24z" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<path d="M-40 -40 L40 40M40 -40 L-40 40" stroke="'.$cyan.'" stroke-width="1.5" opacity="0.45"/>'
            .'<circle cx="0" cy="0" r="12" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>';
    }

    private static function motifLeadership(string $accent, string $soft, string $violet): string
    {
        return self::neonRing($violet, 88, 0.24)
            .'<path d="M-38 48 L-38 -8 L38 -8 L38 48" fill="none" stroke="'.$soft.'" stroke-width="2.5"/>'
            .'<path d="M-22 48v-24h44v24" fill="none" stroke="'.$accent.'" stroke-width="2"/>'
            .'<path d="M-42 -8h84v-12H-42z" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<path d="M-18 -20h36v-18H-18z" fill="none" stroke="'.$soft.'" stroke-width="2"/>'
            .'<path d="M-10 -14h20M-10 -8h14" stroke="'.$accent.'" stroke-width="1.5" opacity="0.65"/>'
            .'<path d="M0 -58 L0 -32M-8 -45 L8 -45" stroke="'.$accent.'" stroke-width="2.5"/>';
    }

    private static function motifServiceTimes(string $accent, string $soft, string $violet): string
    {
        return self::neonRing($violet, 86, 0.24)
            .'<circle cx="0" cy="0" r="52" fill="none" stroke="'.$soft.'" stroke-width="2.5"/>'
            .'<path d="M0 0 L0 -34" stroke="'.$accent.'" stroke-width="3" stroke-linecap="round"/>'
            .'<path d="M0 0 L26 12" stroke="'.$accent.'" stroke-width="2.5" stroke-linecap="round"/>'
            .'<circle cx="0" cy="0" r="4" fill="'.$accent.'"/>'
            .'<path d="M0 -72 L0 -58M-8 -65 L8 -65" stroke="'.$accent.'" stroke-width="2" opacity="0.75"/>';
    }

    private static function motifWorshipGathering(string $accent, string $soft, string $violet, string $cyan): string
    {
        return self::neonRing($violet, 90, 0.22)
            .self::motifCommunion($accent, $soft)
            .'<path d="M-62 28 L-48 8 L-34 28" fill="none" stroke="'.$cyan.'" stroke-width="2" opacity="0.55"/>'
            .'<path d="M34 28 L48 8 L62 28" fill="none" stroke="'.$cyan.'" stroke-width="2" opacity="0.55"/>';
    }

    private static function motifChoir(string $accent, string $soft, string $violet): string
    {
        return self::neonRing($violet, 92, 0.28)
            .self::neonRing($accent, 72, 0.42)
            .'<ellipse cx="0" cy="-18" rx="52" ry="16" fill="none" stroke="'.$accent.'" stroke-width="3"/>'
            .'<path d="M-52 -18v72M52 -18v72" stroke="'.$soft.'" stroke-width="3"/>'
            .'<path d="M-38 18 Q0 -8 38 18 Q0 44 -38 18" fill="none" stroke="'.$accent.'" stroke-width="2.5" opacity="0.85"/>'
            .'<path d="M-18 -42 Q-8 -58 0 -42 Q8 -58 18 -42" fill="none" stroke="'.$soft.'" stroke-width="2"/>'
            .'<path d="M-8 -52 L0 -62 L8 -52" fill="none" stroke="'.$accent.'" stroke-width="2"/>';
    }

    private static function motifSundaySchool(string $accent, string $soft, string $cyan): string
    {
        return self::neonRing($cyan, 88, 0.22)
            .'<path d="M-42 -48h84v96H-42z" fill="none" stroke="'.$soft.'" stroke-width="3"/>'
            .'<path d="M0 -48v96" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<path d="M-28 -20h56M-28 0h56M-28 20h40" stroke="'.$accent.'" stroke-width="2" opacity="0.55"/>'
            .'<path d="M0 -62 L0 -48M-6 -55 L6 -55" stroke="'.$accent.'" stroke-width="2"/>'
            .'<circle cx="48" cy="-58" r="10" fill="none" stroke="'.$cyan.'" stroke-width="2"/>';
    }

    private static function motifYouth(string $accent, string $soft, string $violet, string $cyan): string
    {
        return self::neonRing($violet, 90, 0.3)
            .self::neonRing($cyan, 62, 0.35)
            .'<circle cx="-28" cy="8" r="18" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<circle cx="28" cy="8" r="18" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<path d="M-28 26v38M28 26v38" stroke="'.$soft.'" stroke-width="2.5"/>'
            .'<path d="M-8 -30 L0 -52 L8 -30" fill="none" stroke="'.$cyan.'" stroke-width="2.5"/>'
            .'<path d="M0 -52 L0 -62M-6 -57 L6 -57" stroke="'.$accent.'" stroke-width="2"/>';
    }

    private static function motifWomensFellowship(string $accent, string $soft, string $violet): string
    {
        return self::neonRing($violet, 92, 0.24)
            .'<circle cx="0" cy="8" r="34" fill="none" stroke="'.$accent.'" stroke-width="2.5" opacity="0.55"/>'
            .'<circle cx="-24" cy="0" r="16" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<circle cx="24" cy="0" r="16" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<circle cx="0" cy="-22" r="16" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<path d="M-24 16v28M24 16v28M0 -6v34" stroke="'.$soft.'" stroke-width="2.5"/>'
            .'<path d="M0 -48 L0 -34M-7 -41 L7 -41" stroke="'.$accent.'" stroke-width="2" opacity="0.75"/>';
    }

    private static function motifPrayer(string $accent, string $soft, string $violet): string
    {
        return self::neonRing($violet, 86, 0.28)
            .'<path d="M-28 18 Q-28 -8 0 -18 Q28 -8 28 18" fill="none" stroke="'.$soft.'" stroke-width="3"/>'
            .'<path d="M-38 18 L-28 18 M28 18 L38 18" stroke="'.$soft.'" stroke-width="3" stroke-linecap="round"/>'
            .'<path d="M0 -58 L0 42" stroke="'.$soft.'" stroke-width="3"/>'
            .'<path d="M0 -58 L-14 -34h28z" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<path d="M-52 10 Q-26 -20 0 10 Q26 -20 52 10" fill="none" stroke="'.$accent.'" stroke-width="2.5" opacity="0.8"/>'
            .'<rect x="38" y="-32" width="8" height="48" rx="2" fill="none" stroke="'.$soft.'" stroke-width="2"/>'
            .'<path d="M42 -38 C38 -48 46 -48 42 -38 C46 -44 38 -44 42 -38" fill="none" stroke="'.$accent.'" stroke-width="2"/>'
            .'<path d="M42 -38 L42 -46" stroke="'.$accent.'" stroke-width="2"/>';
    }

    private static function motifPrayerRequest(string $accent, string $soft, string $violet, string $cyan): string
    {
        return self::motifPrayer($accent, $soft, $violet)
            .'<path d="M-34 34h68v28c-34 8-68 8-68 0z" fill="none" stroke="'.$soft.'" stroke-width="2" opacity="0.75"/>'
            .'<path d="M-18 42h36M-12 52h24" stroke="'.$accent.'" stroke-width="1.5" opacity="0.55"/>'
            .'<path d="M0 28 C-8 20 -16 28 0 40 C16 28 8 20 0 28z" fill="none" stroke="'.$cyan.'" stroke-width="2.5"/>';
    }

    private static function motifMemberSignIn(string $accent, string $soft, string $violet, string $cyan): string
    {
        return self::neonRing($violet, 88, 0.24)
            .'<path d="M-28 48 L-28 -24 Q-28 -48 0 -48 Q28 -48 28 -24 L28 48" fill="none" stroke="'.$soft.'" stroke-width="2.75"/>'
            .'<path d="M-12 48 L-12 8 Q-12 -4 0 -4 Q12 -4 12 8 L12 48" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<circle cx="8" cy="20" r="4" fill="none" stroke="'.$accent.'" stroke-width="2"/>'
            .'<path d="M0 -48 L0 -62 M-8 -55 L8 -55" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<path d="M42 -12 L52 -2 L42 8 M52 -2 L38 -2" fill="none" stroke="'.$cyan.'" stroke-width="2.5" stroke-linecap="round"/>'
            .'<circle cx="-38" cy="-8" r="10" fill="none" stroke="'.$violet.'" stroke-width="2" opacity="0.75"/>';
    }

    private static function motifMemberJoin(string $accent, string $soft, string $violet, string $cyan): string
    {
        return self::neonRing($cyan, 92, 0.22)
            .self::churchBuilding($accent, $soft, $violet, $cyan)
            .'<path d="M-48 48 L-48 8 Q-48 -8 -32 -8 Q-16 -8 -16 8 L-16 48" fill="none" stroke="'.$cyan.'" stroke-width="2.5" opacity="0.9"/>'
            .'<circle cx="-28" cy="72" r="14" fill="none" stroke="'.$soft.'" stroke-width="2.5"/>'
            .'<circle cx="0" cy="76" r="16" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<circle cx="28" cy="72" r="14" fill="none" stroke="'.$soft.'" stroke-width="2.5"/>'
            .'<path d="M-28 86v22M0 92v16M28 86v22" stroke="'.$soft.'" stroke-width="2.5"/>'
            .'<path d="M0 58 L0 48M-6 52 L6 52" stroke="'.$cyan.'" stroke-width="2" opacity="0.85"/>';
    }

    private static function motifStream(string $accent, string $soft, string $cyan, string $violet): string
    {
        return self::neonRing($cyan, 88, 0.32)
            .'<rect x="-56" y="-36" width="112" height="72" rx="16" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<path d="M-16 -8 L12 8 L36 -18" fill="none" stroke="'.$soft.'" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>'
            .'<path d="M-72 42 Q-36 28 0 42 T72 42" fill="none" stroke="'.$violet.'" stroke-width="2" opacity="0.55"/>'
            .'<path d="M0 -52 L0 -42M-6 -47 L6 -47" stroke="'.$accent.'" stroke-width="2" opacity="0.7"/>';
    }

    private static function motifCommunion(string $accent, string $soft): string
    {
        return self::neonRing($accent, 84, 0.35)
            .'<path d="M-28 -40h56v72c0 18-56 18-56 0z" fill="none" stroke="'.$soft.'" stroke-width="2.5"/>'
            .'<ellipse cx="0" cy="-40" rx="28" ry="8" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<circle cx="38" cy="12" r="16" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<path d="M38 -4 L38 28M30 8 L46 8" stroke="'.$soft.'" stroke-width="2"/>';
    }

    private static function motifSermon(string $accent, string $soft, string $violet): string
    {
        return self::neonRing($violet, 86, 0.26)
            .'<path d="M-58 -36h116v72H-58z" fill="none" stroke="'.$soft.'" stroke-width="2.5"/>'
            .'<path d="M-42 -12h84M-42 8h64M-42 28h48" stroke="'.$accent.'" stroke-width="2" opacity="0.65"/>'
            .'<path d="M48 -48 L68 -28 L48 -8" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<path d="M0 -58 L0 -36M-8 -47 L8 -47" stroke="'.$accent.'" stroke-width="2" opacity="0.65"/>';
    }

    private static function motifNews(string $accent, string $violet, string $cyan): string
    {
        return self::neonRing($cyan, 82, 0.28)
            .'<path d="M-64 -32h128v64H-64z" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<path d="M-48 -12h96M-48 4h72M-48 20h56" stroke="'.$violet.'" stroke-width="2" opacity="0.7"/>'
            .'<path d="M-56 -32 L-56 32 L-64 32 L-64 -32z" fill="none" stroke="'.$accent.'" stroke-width="2"/>';
    }

    private static function motifEvent(string $accent, string $soft, string $violet, string $cyan): string
    {
        return self::neonRing($violet, 88, 0.3)
            .'<rect x="-52" y="-44" width="104" height="88" rx="12" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<path d="M-52 -18h104" stroke="'.$accent.'" stroke-width="2"/>'
            .'<path d="M-36 -44v-12M36 -44v-12" stroke="'.$soft.'" stroke-width="2.5"/>'
            .'<circle cx="-24" cy="12" r="6" fill="'.$cyan.'" opacity="0.8"/>'
            .'<circle cx="8" cy="12" r="6" fill="'.$accent.'" opacity="0.8"/>'
            .'<circle cx="40" cy="12" r="6" fill="'.$violet.'" opacity="0.8"/>';
    }

    private static function motifLiturgy(string $accent, string $soft, string $violet): string
    {
        return self::neonRing($violet, 84, 0.24)
            .'<path d="M-40 -52h80v108c-40 10-80 10-80 0z" fill="none" stroke="'.$soft.'" stroke-width="2.5"/>'
            .'<path d="M0 -52v108" stroke="'.$accent.'" stroke-width="2"/>'
            .'<path d="M-24 -24h48M-24 0h48M-24 24h32" stroke="'.$accent.'" stroke-width="2" opacity="0.6"/>'
            .'<path d="M0 -68 L0 -52M-8 -60 L8 -60" stroke="'.$accent.'" stroke-width="2"/>';
    }

    private static function motifLectionary(string $accent, string $soft, string $cyan): string
    {
        return self::neonRing($cyan, 84, 0.24)
            .'<rect x="-48" y="-44" width="96" height="88" rx="10" fill="none" stroke="'.$soft.'" stroke-width="2.5"/>'
            .'<path d="M-48 -18h96" stroke="'.$accent.'" stroke-width="2"/>'
            .'<path d="M-32 -6h64M-32 10h48M-32 26h56" stroke="'.$accent.'" stroke-width="1.5" opacity="0.55"/>'
            .'<path d="M28 -44v88" stroke="'.$cyan.'" stroke-width="2" opacity="0.65"/>';
    }

    private static function motifScroll(string $accent, string $soft, string $violet): string
    {
        return self::neonRing($violet, 84, 0.24)
            .'<path d="M-36 -52h72v104c-24 8-48 8-72 0z" fill="none" stroke="'.$soft.'" stroke-width="2.5"/>'
            .'<path d="M-20 -28h40M-20 -8h40M-20 12h28" stroke="'.$accent.'" stroke-width="2" opacity="0.6"/>';
    }

    private static function motifGallery(string $accent, string $soft, string $violet, string $cyan): string
    {
        return self::neonRing($violet, 86, 0.22)
            .'<rect x="-58" y="-42" width="52" height="42" rx="6" fill="none" stroke="'.$soft.'" stroke-width="2.5"/>'
            .'<rect x="6" y="-28" width="52" height="42" rx="6" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<path d="M-52 -18 L-38 -6 L-24 -18 L-10 -6" fill="none" stroke="'.$cyan.'" stroke-width="2" opacity="0.65"/>'
            .'<circle cx="24" cy="-8" r="8" fill="none" stroke="'.$cyan.'" stroke-width="2"/>'
            .'<path d="M12 14 L28 0 L44 14" fill="none" stroke="'.$soft.'" stroke-width="2"/>';
    }

    private static function motifGive(string $accent, string $soft, string $violet): string
    {
        return self::neonRing($violet, 86, 0.24)
            .'<path d="M-34 18 Q-34 -6 0 -18 Q34 -6 34 18" fill="none" stroke="'.$soft.'" stroke-width="3"/>'
            .'<path d="M-44 18 L-34 18 M34 18 L44 18" stroke="'.$soft.'" stroke-width="3" stroke-linecap="round"/>'
            .'<ellipse cx="0" cy="34" rx="38" ry="10" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<path d="M0 -34 L0 -18M-8 -26 L8 -26" stroke="'.$accent.'" stroke-width="2.5"/>';
    }

    private static function motifContact(string $accent, string $soft, string $violet): string
    {
        return self::neonRing($violet, 86, 0.24)
            .'<rect x="-52" y="-32" width="104" height="64" rx="10" fill="none" stroke="'.$soft.'" stroke-width="2.5"/>'
            .'<path d="M-52 -32 L0 8 L52 -32" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<path d="M0 24 C-28 8 -28 -8 0 -8 C28 -8 28 8 0 24z" fill="none" stroke="'.$accent.'" stroke-width="2" opacity="0.75"/>'
            .'<path d="M48 -48 L48 -28 L68 -28" fill="none" stroke="'.$soft.'" stroke-width="2.5" stroke-linecap="round"/>'
            .'<path d="M62 -42 C58 -48 52 -48 48 -42" fill="none" stroke="'.$accent.'" stroke-width="2"/>'
            .'<path d="M0 -58 L0 -44 M-7 -51 L7 -51" stroke="'.$accent.'" stroke-width="2" opacity="0.7"/>'
            .'<circle cx="-36" cy="42" r="8" fill="none" stroke="'.$violet.'" stroke-width="2"/>'
            .'<path d="M-36 50 C-36 58 -28 62 -36 68" fill="none" stroke="'.$violet.'" stroke-width="2"/>';
    }

    private static function motifSafeguarding(string $accent, string $soft, string $violet): string
    {
        return self::neonRing($violet, 88, 0.26)
            .'<path d="M0 -58 L-48 -18 L-48 24 C-48 48 0 68 0 68 C0 68 48 48 48 24 L48 -18z" fill="none" stroke="'.$soft.'" stroke-width="2.75"/>'
            .'<path d="M0 -58 L0 48" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<path d="M0 -58 L-12 -38h24z" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<path d="M-18 8 L-4 22 L22 -8" fill="none" stroke="'.$accent.'" stroke-width="2.5" stroke-linecap="round"/>';
    }

    private static function motifDocument(string $accent, string $soft, string $violet): string
    {
        return self::neonRing($violet, 82, 0.2)
            .'<path d="M-34 -52h68v112H-34z" fill="none" stroke="'.$soft.'" stroke-width="2.5"/>'
            .'<path d="M-18 -28h36M-18 -8h36M-18 12h24" stroke="'.$accent.'" stroke-width="2" opacity="0.55"/>'
            .'<circle cx="24" cy="38" r="14" fill="none" stroke="'.$accent.'" stroke-width="2"/>'
            .'<path d="M18 38 L22 42 L30 34" fill="none" stroke="'.$accent.'" stroke-width="2" stroke-linecap="round"/>';
    }

    private static function motifMission(string $accent, string $soft, string $cyan): string
    {
        return self::neonRing($cyan, 90, 0.26)
            .'<circle cx="0" cy="0" r="48" fill="none" stroke="'.$soft.'" stroke-width="2" opacity="0.35"/>'
            .'<ellipse cx="0" cy="0" rx="48" ry="18" fill="none" stroke="'.$accent.'" stroke-width="2"/>'
            .'<path d="M0 -58 L0 58M-58 0 L58 0" stroke="'.$accent.'" stroke-width="2" opacity="0.45"/>'
            .'<path d="M0 -58 L-12 -38h24z" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>';
    }

    private static function motifCare(string $accent, string $soft, string $violet): string
    {
        return self::motifContact($accent, $soft, $violet);
    }

    private static function motifFellowship(string $accent, string $soft, string $violet): string
    {
        return self::neonRing($violet, 92, 0.24)
            .'<circle cx="-32" cy="4" r="22" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<circle cx="32" cy="4" r="22" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<circle cx="0" cy="-24" r="18" fill="none" stroke="'.$accent.'" stroke-width="2.5" opacity="0.75"/>'
            .'<path d="M-32 26v32M32 26v32M0 -6v38" stroke="'.$soft.'" stroke-width="2.5"/>';
    }

    private static function motifServe(string $accent, string $soft, string $cyan): string
    {
        return self::neonRing($cyan, 88, 0.26)
            .'<path d="M-48 28 L0 -48 L48 28z" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<circle cx="0" cy="8" r="14" fill="none" stroke="'.$soft.'" stroke-width="2.5"/>'
            .'<path d="M0 -48 L0 -58M-6 -53 L6 -53" stroke="'.$accent.'" stroke-width="2"/>';
    }

    private static function motifLocations(string $accent, string $cyan, string $violet): string
    {
        return self::neonRing($cyan, 86, 0.3)
            .'<path d="M0 -52 C-28 -52 -42 -28 -42 0c0 32 42 64 42 64s42-32 42-64c0-28-14-52-42-52z" fill="none" stroke="'.$accent.'" stroke-width="2.5"/>'
            .'<circle cx="0" cy="-4" r="14" fill="none" stroke="'.$violet.'" stroke-width="2"/>'
            .'<path d="M0 -18 L0 10M-8 0 L8 0" stroke="'.$accent.'" stroke-width="2"/>';
    }

    private static function motifFromHash(
        string $accent,
        string $soft,
        string $violet,
        string $cyan,
        int $hash,
    ): string {
        return match ($hash % 5) {
            0 => self::churchBuilding($accent, $soft, $violet, $cyan),
            1 => self::motifSundaySchool($accent, $soft, $cyan),
            2 => self::motifPrayer($accent, $soft, $violet),
            3 => self::motifCommunion($accent, $soft),
            default => self::motifFellowship($accent, $soft, $violet),
        };
    }
}
