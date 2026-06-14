<?php

namespace App\Support;

use Illuminate\Support\Str;

final class SiteTopicArt
{
    /**
     * @var array<string, string>
     */
    private const TOPIC_FILES = [
        'sunday-school' => 'sunday-school.svg',
        'youth-fellowship' => 'youth-fellowship.svg',
        'womens-fellowship' => 'womens-fellowship.svg',
        'choir' => 'choir.svg',
        'prayer-groups' => 'prayer-groups.svg',
        'evangelism-mission' => 'evangelism-mission.svg',
        'pastoral-care' => 'pastoral-care.svg',
        'community-fellowship' => 'community-fellowship.svg',
        'ministry-default' => 'ministry-default.svg',
        'worship' => 'worship.svg',
        'worship-location' => 'worship-location.svg',
        'fellowship' => 'fellowship.svg',
        'communion' => 'communion.svg',
        'event' => 'event.svg',
        'news' => 'news.svg',
        'sermon' => 'sermon.svg',
        'resource' => 'resource.svg',
        'prayer' => 'prayer.svg',
        'mission' => 'mission.svg',
        'default' => 'default.svg',
        // CMS page slug aliases — each page gets its own topic + base illustration
        'home' => 'worship.svg',
        'welcome' => 'community-fellowship.svg',
        'our-church' => 'worship.svg',
        'steci-heritage' => 'mission.svg',
        'mission-vision' => 'mission.svg',
        'leadership' => 'pastoral-care.svg',
        'uk-locations' => 'worship-location.svg',
        'service-times' => 'worship-location.svg',
        'online-worship' => 'worship.svg',
        'sermons' => 'sermon.svg',
        'ministries' => 'ministry-default.svg',
        'events' => 'event.svg',
        'news' => 'news.svg',
        'gallery' => 'community-fellowship.svg',
        'resources' => 'resource.svg',
        'liturgy' => 'resource.svg',
        'lectionary' => 'resource.svg',
        'safeguarding' => 'pastoral-care.svg',
        'contact' => 'pastoral-care.svg',
        'prayer-request' => 'prayer.svg',
        'new-member' => 'community-fellowship.svg',
        'privacy-policy' => 'resource.svg',
        'terms-of-use' => 'resource.svg',
        'give' => 'communion.svg',
        'login' => 'community-fellowship.svg',
        'account' => 'community-fellowship.svg',
        'register' => 'community-fellowship.svg',
        'forgot-password' => 'pastoral-care.svg',
        'reset-password' => 'pastoral-care.svg',
    ];

    /**
     * @var list<array{topic: string, needles: list<string>}>
     */
    private const KEYWORD_RULES = [
        ['topic' => 'sunday-school', 'needles' => ['sunday school', 'vacation bible', 'bible school', 'vbs', 'children', 'kids', 'youth club', 'catechism']],
        ['topic' => 'youth-fellowship', 'needles' => ['youth', 'young people', 'teen', 'student']],
        ['topic' => 'womens-fellowship', 'needles' => ['women', "women's", 'ladies']],
        ['topic' => 'choir', 'needles' => ['choir', 'music', 'hymn', 'worship team', 'singing']],
        ['topic' => 'prayer-groups', 'needles' => ['prayer group', 'prayer meeting', 'monthly prayer', 'intercession', 'prayer chain']],
        ['topic' => 'communion', 'needles' => ['good friday', 'easter', 'maundy', 'communion', 'eucharist', 'lord\'s table', 'sacrament', 'passover']],
        ['topic' => 'worship', 'needles' => ['worship service', 'holy communion service', 'holy communion', 'liturgy', 'sunday service', 'worship night', 'service times', 'service-times', 'online worship', 'online-worship', 'live stream', 'word · worship', 'word • worship', 'word, worship']],
        ['topic' => 'evangelism-mission', 'needles' => ['evangel', 'outreach', 'gospel rally', 'gospel witness', 'evangelistic']],
        ['topic' => 'pastoral-care', 'needles' => ['pastoral', 'visitation', 'counsel', 'bereavement', 'hospital']],
        ['topic' => 'community-fellowship', 'needles' => ['fellowship day', 'fellowship days', 'community', 'meal', 'picnic', 'potluck', 'fellowship']],
        ['topic' => 'sermon', 'needles' => ['sermon', 'preaching', 'expository', 'bible teaching', 'homily', 'sermons']],
        ['topic' => 'prayer', 'needles' => ['prayer', 'intercede', 'fasting', 'vigil', 'prayer request', 'prayer-request']],
        ['topic' => 'mission', 'needles' => ['mission', 'missionary', 'mission field', 'mission vision', 'mission-vision', 'heritage', 'steci']],
        ['topic' => 'resource', 'needles' => ['resource', 'download', 'liturgy', 'lectionary', 'form', 'document', 'pdf', 'booklet', 'resources']],
        ['topic' => 'pastoral-care', 'needles' => ['leadership team', 'privacy policy', 'privacy-policy', 'terms of use', 'terms-of-use', 'gdpr', 'data protection', 'membership enquiry']],
        ['topic' => 'evangelism-mission', 'needles' => ['our church', 'our-church', 'beliefs', 'welcome', 'about', 'heritage']],
        ['topic' => 'news', 'needles' => ['news', 'announcement', 'update', 'notice', 'bulletin', 'parish news']],
        ['topic' => 'event', 'needles' => ['event', 'gathering', 'conference', 'retreat', 'celebration', 'day', 'camp', 'calendar', 'schedule', 'agm', 'annual general', 'festival', 'banquet']],
    ];

    /**
     * @return array<string, string>
     */
    public static function topicFiles(): array
    {
        return self::TOPIC_FILES;
    }

    public static function resolve(
        ?string $slug = null,
        ?string $title = null,
        string $context = 'default',
        ?string $category = null,
        ?string $contentHint = null,
    ): string {
        $slugKey = Str::slug((string) $slug);

        if ($slugKey !== '' && isset(self::TOPIC_FILES[$slugKey])) {
            return $slugKey;
        }

        if ($context === 'service') {
            return 'worship-location';
        }

        $haystack = strtolower(trim(implode(' ', array_filter([
            $slugKey,
            (string) $title,
            (string) $category,
            self::normalizeContentHint($contentHint),
        ]))));

        foreach (self::KEYWORD_RULES as $rule) {
            foreach ($rule['needles'] as $needle) {
                if ($needle !== '' && str_contains($haystack, $needle)) {
                    return $rule['topic'];
                }
            }
        }

        return match ($context) {
            'ministry' => 'ministry-default',
            'event' => 'event',
            'news' => 'news',
            'sermon' => 'sermon',
            'resource' => 'resource',
            'gallery' => 'gallery',
            'service' => 'worship-location',
            'page' => 'default',
            default => 'default',
        };
    }

    private static function normalizeContentHint(?string $contentHint): string
    {
        if ($contentHint === null || trim($contentHint) === '') {
            return '';
        }

        return Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($contentHint))), 400, '');
    }

    /**
     * Merge page fields, excerpts, and labels into one resolver/motif hint.
     *
     * @param  string|null  ...$parts
     */
    public static function buildContentHint(?string ...$parts): string
    {
        $merged = trim(implode(' ', array_filter(array_map(
            static fn (?string $part): string => $part !== null ? trim(strip_tags($part)) : '',
            $parts,
        ), static fn (string $part): bool => $part !== '')));

        return self::normalizeContentHint($merged !== '' ? $merged : null);
    }

    public static function seed(?string $slug = null, ?string $title = null, ?string $contentHint = null): string
    {
        $seed = Str::slug(trim(implode('-', array_filter([
            (string) $slug,
            Str::slug((string) $title),
        ]))));

        if ($contentHint !== null && trim($contentHint) !== '') {
            $seed .= '-'.substr(md5(self::normalizeContentHint($contentHint)), 0, 8);
        }

        return $seed !== '' ? $seed : 'default';
    }

    /**
     * Dynamic per-item art URL — new events/news/ministries pick topic + unique tint automatically.
     */
    public static function mediaUrl(
        ?string $slug = null,
        ?string $title = null,
        string $context = 'default',
        ?string $category = null,
        ?string $contentHint = null,
    ): string {
        $topic = self::resolve($slug, $title, $context, $category, $contentHint);

        return route('topic-art.show', array_filter([
            'topic' => $topic,
            'seed' => self::seed($slug, $title, $contentHint),
            't' => Str::limit((string) $title, 80, ''),
            'c' => self::normalizeContentHint($contentHint) !== ''
                ? Str::limit(self::normalizeContentHint($contentHint), 120, '')
                : null,
        ]));
    }

    public static function url(string $topic): string
    {
        $file = self::TOPIC_FILES[$topic] ?? self::TOPIC_FILES['default'];

        return asset('images/topics/'.$file);
    }

    /**
     * @return list<string>
     */
    public static function topics(): array
    {
        return array_keys(self::TOPIC_FILES);
    }
}
