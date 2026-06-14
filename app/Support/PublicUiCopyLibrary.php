<?php

namespace App\Support;

/**
 * Prefilled defaults for public Gen Z / heavenly UI blocks (editable in admin).
 */
final class PublicUiCopyLibrary
{
    /**
     * @return array{kicker: string, items: list<array{label: string, ref: string, href: string}>}
     */
    public static function sparkStrip(): array
    {
        return [
            'kicker' => 'Anchored in Christ',
            'items' => [
                ['label' => 'Word', 'ref' => 'Rev 1:9', 'href' => '/our-church'],
                ['label' => 'Worship', 'ref' => 'John 4:24', 'href' => '/service-times'],
                ['label' => 'Witness', 'ref' => 'Matt 28:19', 'href' => '/our-church#what-we-believe'],
                ['label' => 'Grace', 'ref' => 'Eph 2:8–9', 'href' => '/our-church#what-we-believe'],
                ['label' => 'Prayer', 'ref' => 'Phil 4:6', 'href' => '/prayer-request'],
                ['label' => 'Peace', 'ref' => 'John 14:27', 'href' => '/our-church'],
                ['label' => 'Scripture', 'ref' => '2 Tim 3:16', 'href' => '/sermons'],
                ['label' => 'Communion', 'ref' => '1 Cor 11:26', 'href' => '/service-times'],
            ],
        ];
    }

    /**
     * @return list<array{text: string, ref: string}>
     */
    public static function divineWhispers(): array
    {
        return ContextScripture::defaultDivineWhispers();
    }

    /**
     * @return array{kicker: string, items: list<array{label: string, desc: string, href: string, icon: string, tone: string}>}
     */
    public static function actionStrip(): array
    {
        return [
            'kicker' => 'Draw near · Worship · Pray',
            'items' => [
                ['label' => 'Holy Communion', 'desc' => 'Monthly worship · 5 cities', 'href' => '/service-times', 'icon' => '✝', 'tone' => 'gold'],
                ['label' => 'Expository Preaching', 'desc' => 'Sermons from Scripture', 'href' => '/sermons', 'icon' => '📖', 'tone' => 'navy'],
                ['label' => 'Intercessory Prayer', 'desc' => 'Submit a request', 'href' => '/prayer-request', 'icon' => '🕊', 'tone' => 'rose'],
                ['label' => 'Our Beliefs', 'desc' => 'Grace, Scripture & STECI faith', 'href' => '/our-church', 'icon' => '⛪', 'tone' => 'violet'],
                ['label' => 'Online Worship', 'desc' => 'Live stream & archive', 'href' => '/online-worship', 'icon' => '▶', 'tone' => 'sky'],
            ],
        ];
    }

    /**
     * @return array{kicker: string, scripture: string, scripture_ref: string}
     */
    public static function pageIntro(): array
    {
        return [
            'kicker' => 'Evangelical Oriental Protestant Parish',
            'scripture' => 'Your word is a lamp to my feet and a light to my path.',
            'scripture_ref' => 'Psalm 119:105',
        ];
    }

    /**
     * @return array{label: string, url: string, aria_label: string}
     */
    public static function prayerFab(): array
    {
        return [
            'label' => 'Pray',
            'url' => '/prayer-request',
            'aria_label' => 'Submit a prayer request',
        ];
    }

    /**
     * @return array{enabled: bool, speculation_rules: bool, reading_progress: bool, heavenly_atmosphere: bool}
     */
    public static function experienceToggles(): array
    {
        return [
            'enabled' => true,
            'speculation_rules' => false,
            'reading_progress' => true,
            'heavenly_atmosphere' => true,
        ];
    }

    /**
     * @return list<array{route: string, slug: string, kicker: string, text: string, ref: string}>
     */
    public static function contextScripture(): array
    {
        return [
            ['route' => 'home', 'slug' => '', 'kicker' => 'Home', 'text' => 'For the Word of God and for the testimony of Jesus Christ.', 'ref' => 'Revelation 1:9'],
            ['route' => 'events.*', 'slug' => '', 'kicker' => 'Fellowship', 'text' => 'Let us not give up meeting together, as some are in the habit of doing, but let us encourage one another.', 'ref' => 'Hebrews 10:25'],
            ['route' => 'news.*', 'slug' => '', 'kicker' => 'Parish life', 'text' => 'They devoted themselves to the apostles\' teaching and to fellowship, to the breaking of bread and to prayer.', 'ref' => 'Acts 2:42'],
            ['route' => 'sermons.*', 'slug' => '', 'kicker' => 'The Word', 'text' => 'Faith comes from hearing the message, and the message is heard through the word about Christ.', 'ref' => 'Romans 10:17'],
            ['route' => 'gallery.*', 'slug' => '', 'kicker' => 'Worship', 'text' => 'Come, let us bow down in worship, let us kneel before the Lord our Maker.', 'ref' => 'Psalm 95:6'],
            ['route' => 'ministries.*', 'slug' => '', 'kicker' => 'Serve', 'text' => 'To equip his people for works of service, so that the body of Christ may be built up.', 'ref' => 'Ephesians 4:12'],
            ['route' => 'give', 'slug' => '', 'kicker' => 'Generosity', 'text' => 'Each of you should give what you have decided in your heart to give, not reluctantly or under compulsion, for God loves a cheerful giver.', 'ref' => '2 Corinthians 9:7'],
            ['route' => 'services.*', 'slug' => '', 'kicker' => 'Worship', 'text' => 'God is spirit, and his worshippers must worship in the Spirit and in truth.', 'ref' => 'John 4:24'],
            ['route' => 'resources.*', 'slug' => '', 'kicker' => 'Scripture', 'text' => 'All Scripture is God-breathed and is useful for teaching, rebuking, correcting and training in righteousness.', 'ref' => '2 Timothy 3:16'],
            ['route' => 'pages.show', 'slug' => 'contact', 'kicker' => 'Draw near', 'text' => 'The Lord is near to all who call on him, to all who call on him in truth.', 'ref' => 'Psalm 145:18'],
            ['route' => 'pages.show', 'slug' => 'prayer-request', 'kicker' => 'Prayer', 'text' => 'The prayer of a righteous person is powerful and effective.', 'ref' => 'James 5:16'],
            ['route' => 'pages.show', 'slug' => 'online-worship', 'kicker' => 'Gathered', 'text' => 'For where two or three gather in my name, there am I with them.', 'ref' => 'Matthew 18:20'],
            ['route' => 'default', 'slug' => '', 'kicker' => 'Grace', 'text' => 'But God demonstrates his own love for us in this: While we were still sinners, Christ died for us.', 'ref' => 'Romans 5:8'],
        ];
    }
}
