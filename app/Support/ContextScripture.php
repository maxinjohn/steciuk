<?php

namespace App\Support;

use Illuminate\Http\Request;

class ContextScripture
{
    /**
     * @return array{kicker: string, text: string, ref: string}
     */
    public static function forRequest(?Request $request = null): array
    {
        $request ??= request();

        $resolved = PublicUiContent::contextScriptureForRequest($request);

        if ($resolved !== null) {
            return $resolved;
        }

        return self::entry(
            'Grace',
            'But God demonstrates his own love for us in this: While we were still sinners, Christ died for us.',
            'Romans 5:8',
        );
    }

    /**
     * @return array{text: string, ref: string}
     */
    public static function emptyStateFor(string $context): array
    {
        return match ($context) {
            'events' => ['text' => 'The Lord himself goes before you and will be with you.', 'ref' => 'Deuteronomy 31:8'],
            'news' => ['text' => 'How beautiful on the mountains are the feet of those who bring good news.', 'ref' => 'Isaiah 52:7'],
            'sermons' => ['text' => 'Man shall not live on bread alone, but on every word that comes from the mouth of God.', 'ref' => 'Matthew 4:4'],
            'gallery' => ['text' => 'I was glad when they said to me, "Let us go to the house of the Lord."', 'ref' => 'Psalm 122:1'],
            'ministries' => ['text' => 'Serve one another humbly in love.', 'ref' => 'Galatians 5:13'],
            'services' => ['text' => 'I was glad when they said to me, "Let us go to the house of the Lord."', 'ref' => 'Psalm 122:1'],
            'resources' => ['text' => 'All Scripture is God-breathed and is useful for teaching, rebuking, correcting and training in righteousness.', 'ref' => '2 Timothy 3:16'],
            'give' => ['text' => 'Each of you should give what you have decided in your heart to give, not reluctantly or under compulsion, for God loves a cheerful giver.', 'ref' => '2 Corinthians 9:7'],
            default => ['text' => 'Wait for the Lord; be strong and take heart and wait for the Lord.', 'ref' => 'Psalm 27:14'],
        };
    }

    /**
     * @return list<array{text: string, ref: string}>
     */
    public static function divineWhispers(): array
    {
        return PublicUiContent::divineWhispers();
    }

    /**
     * @return list<array{text: string, ref: string}>
     */
    public static function defaultDivineWhispers(): array
    {
        return [
            ['text' => 'Be still, and know that I am God.', 'ref' => 'Psalm 46:10'],
            ['text' => 'The Lord is my shepherd; I shall not want.', 'ref' => 'Psalm 23:1'],
            ['text' => 'Peace I leave with you; my peace I give you.', 'ref' => 'John 14:27'],
            ['text' => 'Cast all your anxiety on him because he cares for you.', 'ref' => '1 Peter 5:7'],
            ['text' => 'Come to me, all you who are weary, and I will give you rest.', 'ref' => 'Matthew 11:28'],
            ['text' => 'Trust in the Lord with all your heart.', 'ref' => 'Proverbs 3:5'],
            ['text' => 'The Lord bless you and keep you.', 'ref' => 'Numbers 6:24'],
            ['text' => 'Rejoice in the Lord always.', 'ref' => 'Philippians 4:4'],
        ];
    }

    /**
     * @return array{kicker: string, text: string, ref: string}
     */
    private static function entry(string $kicker, string $text, string $ref): array
    {
        return [
            'kicker' => $kicker,
            'text' => $text,
            'ref' => $ref,
        ];
    }
}
