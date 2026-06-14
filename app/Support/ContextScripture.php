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

        if ($request->routeIs('home')) {
            return self::entry('Home', 'For the Word of God and for the testimony of Jesus Christ.', 'Revelation 1:9');
        }

        if ($request->routeIs('events.*')) {
            return self::entry('Fellowship', 'Let us not give up meeting together, as some are in the habit of doing, but let us encourage one another.', 'Hebrews 10:25');
        }

        if ($request->routeIs('news.*')) {
            return self::entry('Parish life', 'They devoted themselves to the apostles\' teaching and to fellowship, to the breaking of bread and to prayer.', 'Acts 2:42');
        }

        if ($request->routeIs('sermons.*')) {
            return self::entry('The Word', 'Faith comes from hearing the message, and the message is heard through the word about Christ.', 'Romans 10:17');
        }

        if ($request->routeIs('gallery.*')) {
            return self::entry('Worship', 'Come, let us bow down in worship, let us kneel before the Lord our Maker.', 'Psalm 95:6');
        }

        if ($request->routeIs('ministries.*')) {
            return self::entry('Serve', 'To equip his people for works of service, so that the body of Christ may be built up.', 'Ephesians 4:12');
        }

        if ($request->routeIs('give')) {
            return self::entry('Generosity', 'Each of you should give what you have decided in your heart to give, not reluctantly or under compulsion, for God loves a cheerful giver.', '2 Corinthians 9:7');
        }

        if ($request->routeIs('services.*')) {
            return self::entry('Worship', 'God is spirit, and his worshippers must worship in the Spirit and in truth.', 'John 4:24');
        }

        if ($request->routeIs('resources.*')) {
            return self::entry('Scripture', 'All Scripture is God-breathed and is useful for teaching, rebuking, correcting and training in righteousness.', '2 Timothy 3:16');
        }

        if ($request->routeIs('pages.show')) {
            $slug = (string) $request->route('slug', '');

            return match ($slug) {
                'contact' => self::entry('Draw near', 'The Lord is near to all who call on him, to all who call on him in truth.', 'Psalm 145:18'),
                'prayer-request' => self::entry('Prayer', 'The prayer of a righteous person is powerful and effective.', 'James 5:16'),
                'online-worship' => self::entry('Gathered', 'For where two or three gather in my name, there am I with them.', 'Matthew 18:20'),
                default => self::entry('Light', 'Your word is a lamp to my feet and a light to my path.', 'Psalm 119:105'),
            };
        }

        return self::entry('Grace', 'But God demonstrates his own love for us in this: While we were still sinners, Christ died for us.', 'Romans 5:8');
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
            default => ['text' => 'Wait for the Lord; be strong and take heart and wait for the Lord.', 'ref' => 'Psalm 27:14'],
        };
    }

    /**
     * @return list<array{text: string, ref: string}>
     */
    public static function divineWhispers(): array
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
