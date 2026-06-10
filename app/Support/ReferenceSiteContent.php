<?php

namespace App\Support;

/**
 * Canonical reference copy for STECI UK Parish.
 * Applied on deploy via migrations (ReferenceSiteContentMigrator).
 *
 * Sources: Charity Commission 1143030, EAUK member listing, Wikipedia / Grokipedia (STECI).
 */
class ReferenceSiteContent
{
    public const CHARITY_NUMBER = '1143030';

    public const EAUK_CHURCH_URL = 'https://www.eauk.org/churches/st-thomas-evangelical-church-of-india-uk-parish';

    /**
     * @return array<string, array{value: string, group: string}>
     */
    public static function settings(): array
    {
        return [
            'church_name' => [
                'value' => 'St. Thomas Evangelical Church of India – UK Parish',
                'group' => 'general',
            ],
            'motto' => [
                'value' => 'For the Word of God and for the testimony of Jesus Christ',
                'group' => 'general',
            ],
            'contact_email' => [
                'value' => 'admin@steciuk.org',
                'group' => 'contact',
            ],
            'phone' => [
                'value' => '07578 189530',
                'group' => 'contact',
            ],
            'charity_number' => [
                'value' => self::CHARITY_NUMBER,
                'group' => 'contact',
            ],
            'main_address' => [
                'value' => 'United Kingdom',
                'group' => 'contact',
            ],
            'gospel_reminder_reference' => [
                'value' => 'Revelation 1:9',
                'group' => 'general',
            ],
            'gospel_reminder_kicker' => [
                'value' => 'For the Word of God · and the testimony of Jesus Christ',
                'group' => 'general',
            ],
            'seo_default_title' => [
                'value' => 'St. Thomas Evangelical Church of India – UK Parish',
                'group' => 'seo',
            ],
            'seo_default_description' => [
                'value' => 'UK Parish of the St. Thomas Evangelical Church of India (STECI) — an evangelical Oriental Protestant church in the Saint Thomas Christian tradition. Monthly worship in Manchester, Leicester, Dartford, Sunderland, and Bristol — in person and online. Registered Charity '.self::CHARITY_NUMBER.'. Member of the Evangelical Alliance.',
                'group' => 'seo',
            ],
            'footer_text' => [
                'value' => 'For the Word of God and for the testimony of Jesus Christ — Word, worship, and witness across the United Kingdom. Member of the Evangelical Alliance.',
                'group' => 'general',
            ],
            'footer_tagline' => [
                'value' => 'For the Word of God and for the testimony of Jesus Christ — Word, worship, and witness.',
                'group' => 'general',
            ],
            'service_times_heading' => [
                'value' => 'Service Times',
                'group' => 'general',
            ],
            'service_times_intro' => [
                'value' => 'Monthly Holy Communion and worship across five UK cities — Manchester, Leicester, Dartford, Sunderland, and Bristol. Contact the parish office to confirm dates and venues.',
                'group' => 'general',
            ],
            'contact_office_heading' => [
                'value' => 'Parish Office',
                'group' => 'contact',
            ],
            'contact_office_intro' => [
                'value' => 'Questions about monthly worship, Holy Communion, prayer, or joining our parish family of approximately ninety families — we would love to hear from you.',
                'group' => 'contact',
            ],
            'contact_form_heading' => [
                'value' => 'Send a Message',
                'group' => 'contact',
            ],
            'contact_form_intro' => [
                'value' => 'Whether you need pastoral support, information about worship in your area, or wish to join our parish — write to us and we will respond as soon as we can.',
                'group' => 'contact',
            ],
            'faith_sanctuary_kicker' => [
                'value' => 'Abide in Christ',
                'group' => 'faith',
            ],
            'faith_sanctuary_note' => [
                'value' => 'Go in peace — the Lord goes with you. Grace and peace from our parish family.',
                'group' => 'faith',
            ],
            'faith_sanctuary_verses' => [
                'value' => json_encode(self::faithSanctuaryVerses(), JSON_UNESCAPED_UNICODE),
                'group' => 'faith',
            ],
            'faith_comfort_kicker' => [
                'value' => 'For every believer',
                'group' => 'faith',
            ],
            'faith_comfort_heading' => [
                'value' => 'Rest in the Lord',
                'group' => 'faith',
            ],
            'faith_comfort_subheading' => [
                'value' => 'Scripture, prayer, and Holy Communion — anchors for daily faith in Christ',
                'group' => 'faith',
            ],
            'faith_comfort_cards' => [
                'value' => json_encode(self::faithComfortCards(), JSON_UNESCAPED_UNICODE),
                'group' => 'faith',
            ],
        ];
    }

    /**
     * @return list<array{text: string, ref: string}>
     */
    public static function faithSanctuaryVerses(): array
    {
        return [
            ['text' => 'Be still, and know that I am God.', 'ref' => 'Psalm 46:10'],
            ['text' => 'Come to me, all you who are weary and burdened, and I will give you rest.', 'ref' => 'Matthew 11:28'],
            ['text' => 'Peace I leave with you; my peace I give you.', 'ref' => 'John 14:27'],
            ['text' => 'The Lord is my shepherd; I shall not want.', 'ref' => 'Psalm 23:1'],
            ['text' => 'Cast all your anxiety on him because he cares for you.', 'ref' => '1 Peter 5:7'],
            ['text' => 'Trust in the Lord with all your heart and lean not on your own understanding.', 'ref' => 'Proverbs 3:5'],
            ['text' => 'The Lord bless you and keep you; the Lord make his face shine on you.', 'ref' => 'Numbers 6:24–25'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function faithComfortCards(): array
    {
        return [
            [
                'icon' => '🕊',
                'title' => 'Peace in Christ',
                'text' => 'His peace guards heart and mind — a gift for every believer who draws near in worship and prayer.',
                'ref' => 'Philippians 4:7',
            ],
            [
                'icon' => '🙏',
                'title' => 'Rest in Prayer',
                'text' => 'Bring every burden to the Lord. Our parish family intercedes with you in faith.',
                'ref' => 'Matthew 11:28',
                'link' => '/prayer-request',
                'linkLabel' => 'Submit a prayer request',
            ],
            [
                'icon' => '📖',
                'title' => 'Hope in Scripture',
                'text' => 'Holy Scripture nourishes faith — through preaching, reading, and Holy Communion at the Lord\'s table.',
                'ref' => 'Romans 15:4',
                'link' => '/sermons',
                'linkLabel' => 'Listen to a sermon',
            ],
            [
                'icon' => '✝',
                'title' => 'Assurance in Grace',
                'text' => 'Salvation is by grace through faith in Christ alone — not by works, but by the mercy of God.',
                'ref' => 'Ephesians 2:8–9',
                'link' => '/our-church',
                'linkLabel' => 'Read our beliefs',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function pageBodies(): array
    {
        return [
            'welcome' => self::welcome(),
            'our-church' => self::ourChurch(),
            'steci-heritage' => self::heritage(),
            'mission-vision' => self::missionVision(),
            'leadership' => self::leadership(),
            'uk-locations' => self::locations(),
            'service-times' => self::serviceTimes(),
            'online-worship' => self::onlineWorship(),
            'sermons' => self::sermons(),
            'ministries' => self::ministries(),
            'sunday-school' => self::sundaySchool(),
            'youth-fellowship' => self::youthFellowship(),
            'womens-fellowship' => self::womensFellowship(),
            'choir' => self::choir(),
            'prayer-groups' => self::prayerGroups(),
            'events' => self::events(),
            'news' => self::news(),
            'gallery' => self::gallery(),
            'resources' => self::resources(),
            'liturgy' => self::liturgy(),
            'lectionary' => self::lectionary(),
            'safeguarding' => self::safeguarding(),
            'contact' => self::contact(),
            'prayer-request' => self::prayerRequest(),
            'new-member' => self::newMember(),
            'privacy-policy' => self::privacyPolicy(),
            'terms-of-use' => self::termsOfUse(),
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function pageFields(): array
    {
        return [
            'home' => [
                'seo_title' => 'St. Thomas Evangelical Church of India – UK Parish',
                'seo_description' => 'UK Parish of the St. Thomas Evangelical Church of India (STECI). Evangelical Oriental Protestant worship across Manchester, Leicester, Dartford, Sunderland, and Bristol — in person and online.',
            ],
            'service-times' => [
                'seo_description' => 'Monthly worship and Holy Communion at Manchester, Leicester, Dartford, Sunderland, and Bristol. Contact the parish office for current schedules.',
            ],
            'our-church' => [
                'seo_description' => 'An evangelical Oriental Protestant UK parish in the Saint Thomas Christian tradition. Member of the Evangelical Alliance. Registered Charity '.self::CHARITY_NUMBER.'.',
            ],
            'steci-heritage' => [
                'seo_description' => 'STECI heritage: Saint Thomas Christian tradition, founded 26 January 1961, evangelical Oriental Protestant faith centred on Scripture.',
            ],
            'contact' => [
                'seo_description' => 'Contact the STECI UK Parish office — admin@steciuk.org · 07578 189530 · Charity '.self::CHARITY_NUMBER.'.',
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function homeContentBlockPatches(): array
    {
        return [
            'hero' => self::homeHeroContent(),
            'locations' => [
                'heading' => 'Gathered Worship Across Britain',
                'subheading' => 'Monthly Holy Communion and biblical preaching in five cities',
                'locations' => ['Manchester', 'Leicester', 'Dartford', 'Sunderland', 'Bristol'],
                'link_url' => '/service-times',
                'link_label' => 'View All Service Times',
            ],
            'welcome-quote' => [
                'quote' => 'Draw near to God in worship, Word, and prayer — united as believers in Christ across our UK parish family, rooted in the evangelical Oriental Protestant faith of STECI.',
            ],
        ];
    }

    /**
     * Reference navigation menus (header, mobile, footer). Applied via migrate.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public static function menus(): array
    {
        $header = [
            ['label' => 'Home', 'slug' => 'home', 'seed_key' => 'home'],
            [
                'label' => 'About',
                'seed_key' => 'about',
                'children' => [
                    ['label' => 'Welcome', 'slug' => 'welcome', 'seed_key' => 'about.welcome'],
                    ['label' => 'Our Church', 'slug' => 'our-church', 'seed_key' => 'about.our-church'],
                    ['label' => 'STECI Heritage', 'slug' => 'steci-heritage', 'seed_key' => 'about.steci-heritage'],
                    ['label' => 'Mission & Vision', 'slug' => 'mission-vision', 'seed_key' => 'about.mission-vision'],
                    ['label' => 'Leadership', 'slug' => 'leadership', 'seed_key' => 'about.leadership'],
                    ['label' => 'Locations', 'slug' => 'uk-locations', 'seed_key' => 'about.uk-locations'],
                ],
            ],
            [
                'label' => 'Worship',
                'seed_key' => 'worship',
                'children' => [
                    ['label' => 'Service Times', 'slug' => 'service-times', 'seed_key' => 'worship.service-times'],
                    ['label' => 'Online Worship', 'slug' => 'online-worship', 'seed_key' => 'worship.online-worship'],
                    ['label' => 'Sermons', 'slug' => 'sermons', 'seed_key' => 'worship.sermons'],
                ],
            ],
            [
                'label' => 'Ministries',
                'seed_key' => 'ministries',
                'children' => [
                    ['label' => 'Overview', 'slug' => 'ministries', 'seed_key' => 'ministries.overview'],
                    ['label' => 'Sunday School', 'slug' => 'sunday-school', 'seed_key' => 'ministries.sunday-school'],
                    ['label' => 'Youth Fellowship', 'slug' => 'youth-fellowship', 'seed_key' => 'ministries.youth-fellowship'],
                    ['label' => "Women's Fellowship", 'slug' => 'womens-fellowship', 'seed_key' => 'ministries.womens-fellowship'],
                    ['label' => 'Choir', 'slug' => 'choir', 'seed_key' => 'ministries.choir'],
                    ['label' => 'Prayer Groups', 'slug' => 'prayer-groups', 'seed_key' => 'ministries.prayer-groups'],
                ],
            ],
            ['label' => 'Events', 'slug' => 'events', 'seed_key' => 'events'],
            ['label' => 'News', 'slug' => 'news', 'seed_key' => 'news'],
            [
                'label' => 'Resources',
                'seed_key' => 'resources',
                'children' => [
                    ['label' => 'Overview', 'slug' => 'resources', 'seed_key' => 'resources.overview'],
                    ['label' => 'Liturgy', 'slug' => 'liturgy', 'seed_key' => 'resources.liturgy'],
                    ['label' => 'Lectionary', 'slug' => 'lectionary', 'seed_key' => 'resources.lectionary'],
                    ['label' => 'Gallery', 'slug' => 'gallery', 'seed_key' => 'resources.gallery'],
                ],
            ],
            [
                'label' => 'Contact',
                'seed_key' => 'contact',
                'children' => [
                    ['label' => 'Contact Us', 'slug' => 'contact', 'seed_key' => 'contact.contact-us'],
                    ['label' => 'Prayer Request', 'slug' => 'prayer-request', 'seed_key' => 'contact.prayer-request'],
                    ['label' => 'New Member', 'slug' => 'new-member', 'seed_key' => 'contact.new-member'],
                ],
            ],
        ];

        $footer = [
            ['label' => 'Home', 'slug' => 'home', 'seed_key' => 'home'],
            ['label' => 'Welcome', 'slug' => 'welcome', 'seed_key' => 'welcome'],
            ['label' => 'Service Times', 'slug' => 'service-times', 'seed_key' => 'service-times'],
            ['label' => 'Events', 'slug' => 'events', 'seed_key' => 'events'],
            ['label' => 'News', 'slug' => 'news', 'seed_key' => 'news'],
            ['label' => 'Contact', 'slug' => 'contact', 'seed_key' => 'contact'],
            ['label' => 'Safeguarding', 'slug' => 'safeguarding', 'seed_key' => 'safeguarding'],
            ['label' => 'Privacy Policy', 'slug' => 'privacy-policy', 'seed_key' => 'privacy-policy'],
            ['label' => 'Terms of Use', 'slug' => 'terms-of-use', 'seed_key' => 'terms-of-use'],
        ];

        return [
            'header' => $header,
            'mobile' => $header,
            'footer' => $footer,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function homeHeroContent(): array
    {
        return [
            'eyebrow' => 'St. Thomas Evangelical Church of India',
            'headline' => 'Word · Worship · Witness',
            'subtitle' => 'For the Word of God and for the testimony of Jesus Christ',
            'badge' => 'UK Parish',
            'stats' => [
                ['value' => '5', 'label' => 'Worship Locations'],
                ['value' => '90+', 'label' => 'Parish Families'],
                ['value' => '1961', 'label' => 'STECI Founded'],
            ],
            'primary_cta_label' => 'Plan a Visit',
            'primary_cta_url' => '/service-times',
            'secondary_cta_label' => 'Our Beliefs',
            'secondary_cta_url' => '/our-church',
            'tertiary_cta_label' => 'Watch a Sermon',
            'tertiary_cta_url' => '/sermons',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function services(): array
    {
        $contact = 'Contact admin@steciuk.org or 07578 189530 for the current monthly date, time, and venue';

        return [
            self::serviceRecord('Manchester', 'Greater Manchester & the North West', 'The Manchester congregation gathers for monthly worship, Holy Communion, fellowship, and prayer. Families from across Greater Manchester and the North West are warmly welcome.', 1, $contact),
            self::serviceRecord('Leicester', 'Leicestershire & the East Midlands', 'Our Leicester fellowship meets for monthly worship and fellowship, serving families across Leicestershire and the East Midlands.', 2, $contact),
            self::serviceRecord('Dartford', 'Kent & South East London', 'The Dartford congregation serves families across Kent and South East London with monthly worship, prayer, and fellowship.', 3, $contact),
            self::serviceRecord('Sunderland', 'the North East of England', 'Our Sunderland fellowship gathers monthly for worship and pastoral fellowship, welcoming families across the North East.', 4, $contact),
            self::serviceRecord('Bristol', 'the South West of England', 'The Bristol congregation meets monthly for worship, Holy Communion, and fellowship, serving families across the South West.', 5, $contact),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function serviceRecord(string $city, string $region, string $description, int $sortOrder, string $scheduleNote): array
    {
        return [
            'title' => "{$city} Worship Service",
            'location' => $city,
            'address' => "United Kingdom ({$region}) — venue details from the parish office",
            'service_day' => 'Monthly',
            'service_time' => $scheduleNote,
            'frequency' => 'Monthly worship service',
            'language' => 'English & Malayalam',
            'description' => $description,
            'map_link' => 'https://maps.google.com/?q='.rawurlencode("{$city}, UK"),
            'online_stream_link' => 'https://youtube.com/@steciuk/live',
            'contact_person' => 'Parish Office',
            'contact_email' => 'admin@steciuk.org',
            'contact_phone' => '07578 189530',
            'sort_order' => $sortOrder,
            'status' => 'active',
        ];
    }

    public static function welcome(): string
    {
        return <<<'HTML'
<p>Welcome to the UK Parish of the <strong>St. Thomas Evangelical Church of India</strong> (STECI). We are delighted that you are visiting our website and hope you will feel drawn to join us in worship, prayer, and fellowship.</p>

<p>Our parish serves approximately <strong>ninety families</strong> spread across the United Kingdom, gathering for <strong>monthly worship</strong> at five locations: <strong>Manchester, Leicester, Dartford, Sunderland, and Bristol</strong>. Church members attend services <strong>both in person and online</strong>. Though geographically dispersed, we are united by our faith in Jesus Christ, our commitment to the authority of the Holy Bible, and our heritage in the Saint Thomas Christian tradition of Kerala, India.</p>

<p>The <strong>St. Thomas Evangelical Church of India</strong> was founded on <strong>26 January 1961</strong> and is headquartered at <strong>Manjadi, Thiruvalla, Kerala</strong>. STECI is an evangelical <strong>Oriental Protestant</strong> church in the Saint Thomas Syrian Christian tradition — episcopal in church order, democratic in governance, and missionary in calling. The UK Parish is a registered charity (No. <strong>1143030</strong>) and a <a href="https://www.eauk.org/churches/st-thomas-evangelical-church-of-india-uk-parish" target="_blank" rel="noopener noreferrer">member of the Evangelical Alliance</a>.</p>

<p>Whether you are exploring Christianity for the first time, reconnecting with your faith, or seeking a spiritual home within the Saint Thomas Christian tradition, you are warmly welcome. Please explore our service times, ministries, and events — or <a href="/contact">contact us</a> directly. We would love to hear from you.</p>

<p><em>"For the Word of God and for the testimony of Jesus Christ."</em> — Revelation 1:9</p>
HTML;
    }

    public static function ourChurch(): string
    {
        return <<<'HTML'
<h2>Who We Are</h2>
<p>The St. Thomas Evangelical Church of India – UK Parish is part of <strong>STECI</strong>, a global evangelical Oriental Protestant church with deep roots in the Saint Thomas Christian community of Kerala. Founded on <strong>26 January 1961</strong>, STECI emerged from a reform movement within the <strong>Malankara Mar Thoma Syrian Church</strong>, seeking stricter adherence to biblical authority, <em>sola scriptura</em>, and evangelical doctrine. Our UK Parish gathers approximately ninety families across Britain for monthly worship — in person and online — under episcopal oversight from STECI headquarters at <strong>Manjadi, Thiruvalla, Kerala</strong>. We are a <a href="https://www.eauk.org/churches/st-thomas-evangelical-church-of-india-uk-parish" target="_blank" rel="noopener noreferrer">member of the Evangelical Alliance</a> (Registered Charity No. <strong>1143030</strong>).</p>

<h2>What We Believe</h2>
<p>We confess the evangelical Oriental Protestant faith of STECI — the historic Christian faith as revealed in Holy Scripture and summarised in the <strong>Nicene Creed</strong>. Our motto is <em>For the Word of God and for the testimony of Jesus Christ</em> (Revelation 1:9). Core convictions include:</p>
<ul>
<li>The <strong>Holy Trinity</strong> — one God: Father, Son, and Holy Spirit</li>
<li><strong>Sola Scriptura</strong> — the sixty-six books of the Bible as the inspired, supreme authority for faith and practice</li>
<li><strong>Salvation by grace through faith</strong> in Jesus Christ alone — not by human works</li>
<li>Jesus Christ as <strong>Lord and Saviour</strong>; his <strong>second coming</strong>, the resurrection of the dead, and the call to holy living</li>
<li>The <strong>priesthood of all believers</strong> — prayer to God through Christ alone, without veneration of saints or prayers for the dead</li>
<li>Two sacraments instituted by Christ: <strong>Baptism</strong> (including the baptism of children born to Christian parents) and the <strong>Lord's Supper</strong> — Holy Communion observed as a memorial of Christ's sacrifice</li>
<li>The <strong>Great Commission</strong> — safeguarding sound doctrine, living a holy life, and making disciples in the United Kingdom and beyond</li>
</ul>

<h3>What We Reject</h3>
<p>In continuity with the reform vision of <strong>Abraham Malpan</strong> and the founding of STECI, we do not practise veneration of saints, prayers for the dead, auricular confession, idolatry, or worship directed to icons or statues. Our faith is centred on Christ and Scripture alone.</p>

<h2>How We Worship</h2>
<p>Our worship follows STECI's evangelical, Scripture-centred pattern: Bible readings, expository preaching, congregational hymns, prayer, and Holy Communion — without elaborate ritual or ceremonial excess. In the UK Parish, services are conducted primarily in <strong>English</strong>, with Malayalam hymns and readings at many gatherings.</p>

<h2>Our UK Parish</h2>
<p>With worship locations in five cities, our parish brings together families from diverse backgrounds who share a common faith and cultural heritage. We support one another through pastoral care, Sunday School, youth and women's fellowships, choir, prayer groups, and community events — both in person and online.</p>
HTML;
    }

    public static function heritage(): string
    {
        return <<<'HTML'
<h2>The Saint Thomas Christian Tradition</h2>
<p>The Saint Thomas Christians trace their origins to the missionary activity of the <strong>Apostle Thomas</strong> in India around <strong>AD 52</strong>, forming the ancient Malankara Church in Kerala. Across centuries this community preserved a distinctive Syriac Christian witness, shaped by reforms in the 19th century — notably those led by <strong>Abraham Malpan</strong> (1796–1845), who emphasised scriptural preaching, vernacular worship, and evangelical faith.</p>

<h2>Founding of STECI</h2>
<p>Tensions over biblical authority and worship practice intensified within the Mar Thoma Syrian Church in the 1950s. Reform-minded believers, including the <strong>Pathiopadesa Samathy</strong> (St. Thomas Organisation for Sound Doctrine), called for strict <em>sola scriptura</em> and rejection of practices they considered unbiblical. After four presbyters were suspended in <strong>November 1960</strong>, the reformists constituted the <strong>St. Thomas Evangelical Church of India</strong> as an independent church on <strong>26 January 1961</strong> at <strong>Thaimala, Thiruvalla</strong>, with headquarters at <strong>Manjadi, Thiruvalla, Kerala</strong>.</p>

<h2>Evangelical Oriental Protestant Identity</h2>
<p>STECI is an <strong>evangelical Oriental Protestant</strong> church with <strong>episcopal</strong> polity, governed by its representative synod (<strong>Prathinidhi Sabha</strong>) and organised into <strong>seven dioceses</strong> — four in Kerala plus dioceses for other regions of India, the Gulf &amp; Singapore, and North America &amp; Europe. STECI was formed to:</p>
<ul>
<li><strong>Safeguard sound doctrine</strong> according to Scripture</li>
<li><strong>Live a holy life</strong> in obedience to Christ</li>
<li><strong>Obey the Great Commission</strong> to evangelise India and the nations</li>
</ul>
<p>Today STECI serves more than <strong>350 congregations</strong> and approximately <strong>100,000 members</strong> worldwide, with extensive missionary work and institutions such as <strong>Jubilee Memorial Bible College</strong> (Chennai) and the church publication <em>Suvishesha Prakasini</em>. The church is united by the motto: <em>For the Word of God and for the testimony of Jesus Christ</em> (Revelation 1:9).</p>

<h2>STECI in the United Kingdom</h2>
<p>The UK Parish serves the STECI diaspora community across Britain — approximately <strong>ninety families</strong> gathering monthly in five cities, in person and online — providing spiritual home, pastoral care, and worship while remaining part of STECI's global fellowship under episcopal oversight from headquarters at <strong>Manjadi, Thiruvalla, Kerala</strong>.</p>
HTML;
    }

    public static function missionVision(): string
    {
        return <<<'HTML'
<h2>Our Mission</h2>
<p>As the UK Parish of the St. Thomas Evangelical Church of India, we share STECI's founding mission — to <strong>safeguard sound doctrine</strong>, encourage <strong>holy living</strong>, and fulfil the <strong>Great Commission</strong> — glorifying God through Scripture-centred worship, proclaiming the Gospel of Jesus Christ, nurturing believers in discipleship, and serving our community across the United Kingdom.</p>

<h2>Our Vision</h2>
<p>A vibrant, spiritually mature parish community across the United Kingdom where families of all generations:</p>
<ul>
<li>Worship God in spirit and truth</li>
<li>Grow deep in knowledge of Scripture</li>
<li>Build strong Christian homes and friendships</li>
<li>Reach neighbours and nations with the love of Christ</li>
<li>Remain faithful to STECI's evangelical Oriental Protestant identity and Saint Thomas Christian heritage</li>
</ul>

<h2>Our Values</h2>
<ul>
<li><strong>Scripture</strong> — The Bible shapes our beliefs, worship, and daily living</li>
<li><strong>Worship</strong> — Gathered praise, prayer, and sacrament at the centre of parish life</li>
<li><strong>Fellowship</strong> — Authentic community across our five UK locations</li>
<li><strong>Discipleship</strong> — Equipping every member to follow Christ faithfully</li>
<li><strong>Evangelism</strong> — Sharing the Good News locally and supporting global mission</li>
<li><strong>Service</strong> — Caring for one another and serving those in need</li>
</ul>
HTML;
    }

    public static function leadership(): string
    {
        return <<<'HTML'
<p>The UK Parish operates under the constitution of the St. Thomas Evangelical Church of India – UK Parish (Registered Charity No. 1143030). A parish committee supports the Vicar in planning worship, coordinating ministries, and overseeing parish affairs across our five locations — always under the Word of God and for the testimony of Jesus Christ.</p>

<p>Contact the parish office at <a href="mailto:admin@steciuk.org">admin@steciuk.org</a> if you need to reach any member of the leadership team.</p>
HTML;
    }

    public static function locations(): string
    {
        return <<<'HTML'
<h2>Five Locations Across the UK</h2>
<p>Our parish family of approximately <strong>ninety families</strong> gathers for <strong>monthly worship</strong> at five locations across the United Kingdom. Church members attend services <strong>both in person and online</strong>. Each congregation enjoys local fellowship while sharing in parish-wide events, prayer, and mission.</p>

<h3>Manchester</h3>
<p>Serving families across Greater Manchester and the North West of England.</p>

<h3>Leicester</h3>
<p>Serving families across Leicestershire and the East Midlands.</p>

<h3>Dartford</h3>
<p>Serving families across Kent and South East London.</p>

<h3>Sunderland</h3>
<p>Serving families across the North East of England.</p>

<h3>Bristol</h3>
<p>Serving families across the South West of England.</p>

<p>For current service dates, venue details, and contact information, please visit our <a href="/service-times">Service Times</a> page or contact the parish office at <a href="mailto:admin@steciuk.org">admin@steciuk.org</a> or <strong>07578 189530</strong>.</p>
HTML;
    }

    public static function serviceTimes(): string
    {
        return <<<'HTML'
<h2>Monthly Worship Across the UK</h2>
<p>As registered with the Charity Commission (No. <strong>1143030</strong>), our UK Parish holds <strong>regular monthly services</strong> in <strong>Manchester, Leicester, Dartford, Sunderland, and Bristol</strong>. Church members attend <strong>both physically and online</strong>.</p>

<p>Services typically include worship, Scripture readings, a sermon, and Holy Communion, following STECI's evangelical Oriental Protestant tradition.</p>

<p><strong>Please contact the parish office before planning your visit.</strong> Monthly service dates, times, and venue details are confirmed by the parish office and may vary seasonally.</p>

<p>Email <a href="mailto:admin@steciuk.org">admin@steciuk.org</a> or call <strong>07578 189530</strong> for the latest schedule at your nearest location.</p>

<p>Location cards below show each city congregation. Online stream information is shared ahead of services where available.</p>
HTML;
    }

    public static function onlineWorship(): string
    {
        return <<<'HTML'
<h2>Join Us Online</h2>
<p>Unable to attend in person? You can participate in our parish worship online. Live streams are available during scheduled monthly worship services, and recorded sermons are published on our <a href="/sermons">Sermons</a> page.</p>

<h2>Live Stream</h2>
<p>Our online worship stream is available during monthly services. Please check our <a href="/events">Events</a> page or contact the parish office for the next scheduled live stream.</p>

<p>YouTube: <a href="https://youtube.com/@steciuk" target="_blank" rel="noopener noreferrer">youtube.com/@steciuk</a></p>

<h2>Online Prayer & Fellowship</h2>
<p>Our prayer groups also meet online, keeping our dispersed parish family of approximately ninety families connected in intercession and Bible study. Contact the parish office to receive meeting links and schedules.</p>
HTML;
    }

    public static function sermons(): string
    {
        return <<<'HTML'
<h2>Sermons & Biblical Teaching</h2>
<p>Explore recent messages preached at our UK Parish worship services. Sermons are rooted in the exposition of Scripture and applied to daily Christian living.</p>

<p>Recent sermons are listed below. Many include video recordings via YouTube and may also offer audio or PDF downloads where available.</p>
HTML;
    }

    public static function ministries(): string
    {
        return <<<'HTML'
<h2>Ministries of the UK Parish</h2>
<p>Our parish offers a range of ministries to help every member — from children to seniors — grow in faith and serve others. Explore our ministries below and contact the parish office if you would like to get involved.</p>

<ul>
<li><a href="/sunday-school">Sunday School</a> — Bible teaching for children</li>
<li><a href="/youth-fellowship">Youth Fellowship</a> — Discipleship for young people</li>
<li><a href="/womens-fellowship">Women's Fellowship</a> — Prayer, fellowship, and service</li>
<li><a href="/choir">Choir</a> — Leading worship through music</li>
<li><a href="/prayer-groups">Prayer Groups</a> — United intercession across the UK</li>
<li>Evangelism &amp; Mission — Gospel outreach and mission support</li>
<li>Pastoral Care — Spiritual support for families</li>
<li>Community Fellowship — Building friendships between worship gatherings</li>
</ul>
HTML;
    }

    public static function sundaySchool(): string
    {
        return <<<'HTML'
<p>Sunday School helps children grow in the knowledge of the Bible and Christian faith through age-appropriate teaching, songs, activities, and fellowship.</p>
<p>Classes are organised by age group and meet during parish worship gatherings across our UK locations. Dedicated teachers volunteer their time to nurture young hearts in the love of Christ.</p>
<p>To enrol your child or volunteer as a teacher, please <a href="/contact">contact the parish office</a>.</p>
HTML;
    }

    public static function youthFellowship(): string
    {
        return <<<'HTML'
<p>Youth Fellowship encourages young people to grow in Christ, build friendships, study the Bible, serve the church, and participate in mission.</p>
<p>Our youth programme combines worship, Bible study, discussion, and social activities for teenagers and young adults across the UK Parish.</p>
<p>Contact the parish office to find out about youth events in your area.</p>
HTML;
    }

    public static function womensFellowship(): string
    {
        return <<<'HTML'
<p>Women's Fellowship supports spiritual growth, prayer, family life, fellowship, charity, and service among the women of our parish.</p>
<p>Meetings include devotional sharing, intercessory prayer, hymn singing, and practical outreach to families in need.</p>
<p>All women of the parish are warmly invited to participate. Contact the parish office for meeting details.</p>
HTML;
    }

    public static function choir(): string
    {
        return <<<'HTML'
<p>The parish choir leads the congregation in worship through songs, liturgy, and music, drawing on English hymnody and the musical traditions of the Saint Thomas Christian community.</p>
<p>If you enjoy singing and wish to serve in worship, we would love to hear from you. Rehearsals are arranged ahead of monthly worship services.</p>
HTML;
    }

    public static function prayerGroups(): string
    {
        return <<<'HTML'
<p>Prayer Groups help families across the UK stay connected through prayer, Bible study, and fellowship — both in local gatherings and through online meetings.</p>
<p>In a parish spanning five cities, prayer is the glue that binds us together. Join a group near you or connect online.</p>
<p>Contact the parish office for meeting times and links.</p>
HTML;
    }

    public static function events(): string
    {
        return <<<'HTML'
<h2>Parish Events</h2>
<p>Stay up to date with fellowship gatherings, special services, prayer meetings, and community events across our UK Parish. Upcoming events are listed below.</p>
<p>For enquiries about any event, please <a href="/contact">contact us</a>.</p>
HTML;
    }

    public static function news(): string
    {
        return <<<'HTML'
<h2>News & Announcements</h2>
<p>Latest news, announcements, and updates from the St. Thomas Evangelical Church of India – UK Parish.</p>
HTML;
    }

    public static function gallery(): string
    {
        return <<<'HTML'
<h2>Photo Gallery</h2>
<p>Browse photos from worship services, fellowship events, and parish life across our five UK locations.</p>
HTML;
    }

    public static function resources(): string
    {
        return <<<'HTML'
<h2>Parish Resources</h2>
<p>Download liturgical materials, parish forms, notices, reports, safeguarding documents, and newsletters. Resources are organised by category below.</p>
<ul>
<li><a href="/liturgy">Liturgy</a></li>
<li><a href="/lectionary">Lectionary</a></li>
<li><a href="/safeguarding">Safeguarding</a></li>
</ul>
HTML;
    }

    public static function liturgy(): string
    {
        return <<<'HTML'
<h2>Liturgical Resources</h2>
<p>Order of service templates, liturgical texts, and worship resources used in STECI UK Parish services. Documents are available for download below.</p>
<p>Our worship follows STECI's evangelical Oriental Protestant tradition — Scripture, hymns, prayer, preaching, and Holy Communion — without elaborate ritual.</p>
HTML;
    }

    public static function lectionary(): string
    {
        return <<<'HTML'
<h2>Lectionary</h2>
<p>Scripture readings appointed for worship services according to the church lectionary. Download the current lectionary schedule below.</p>
<p>Readings are selected to guide the congregation through the breadth of Scripture across the liturgical year.</p>
HTML;
    }

    public static function safeguarding(): string
    {
        return <<<'HTML'
<h2>Safeguarding Policy</h2>
<p>The St. Thomas Evangelical Church of India – UK Parish is committed to safeguarding children, young people, and vulnerable adults. We take our responsibilities seriously and follow best practice in recruitment, training, and reporting.</p>

<h3>Our Commitment</h3>
<ul>
<li>All leaders working with children and vulnerable adults undergo appropriate checks</li>
<li>A designated safeguarding officer oversees policy and reporting</li>
<li>Concerns are handled promptly, confidentially, and in accordance with statutory requirements</li>
<li>Safeguarding policies are reviewed regularly by parish leadership</li>
</ul>

<h3>Reporting a Concern</h3>
<p>If you have a safeguarding concern, please contact the parish office immediately at <a href="mailto:admin@steciuk.org">admin@steciuk.org</a> or call <strong>07578 189530</strong>.</p>

<p>For emergencies, contact the police on <strong>999</strong>. The NSPCC helpline is available at <strong>0808 800 5000</strong>.</p>

<p>Safeguarding policy documents are available for download below.</p>
HTML;
    }

    public static function contact(): string
    {
        return <<<'HTML'
<h2>Get in Touch</h2>
<p>We would love to hear from you. Whether you have a question about monthly worship, want pastoral support, or are interested in joining our parish, please reach out using the contact form below or the details listed.</p>

<h3>Parish Office</h3>
<p><strong>Email:</strong> <a href="mailto:admin@steciuk.org">admin@steciuk.org</a><br>
<strong>Phone:</strong> 07578 189530<br>
<strong>Charity No:</strong> 1143030<br>
<strong>Registered with:</strong> <a href="https://register-of-charities.charitycommission.gov.uk/en/charity-search/-/charity-details/5016600" target="_blank" rel="noopener noreferrer">Charity Commission for England &amp; Wales</a><br>
<strong>Evangelical Alliance:</strong> <a href="https://www.eauk.org/churches/st-thomas-evangelical-church-of-india-uk-parish" target="_blank" rel="noopener noreferrer">Member church listing</a><br>
<strong>Address:</strong> United Kingdom</p>

<h3>Monthly Worship Locations</h3>
<p>Manchester · Leicester · Dartford · Sunderland · Bristol</p>
HTML;
    }

    public static function prayerRequest(): string
    {
        return <<<'HTML'
<h2>Submit a Prayer Request</h2>
<p>Our prayer team would be honoured to pray with you. Share your request using the form below and know that you are held in the prayers of our parish family.</p>
<p>All prayer requests are treated with confidentiality. A member of our prayer ministry team will respond where appropriate.</p>
HTML;
    }

    public static function newMember(): string
    {
        return <<<'HTML'
<h2>Register as a New Member</h2>
<p>Welcome! If you would like to join the St. Thomas Evangelical Church of India – UK Parish, please complete the registration form below. A member of our leadership team will be in touch to welcome you and help you connect with worship and fellowship in your area.</p>
<p>Membership is open to those who profess faith in Jesus Christ and wish to participate in the life and mission of our parish.</p>
HTML;
    }

    public static function privacyPolicy(): string
    {
        return <<<'HTML'
<h2>Privacy Policy</h2>
<p><em>Last updated: placeholder — please review with legal counsel before publication.</em></p>

<h3>Who We Are</h3>
<p>The St. Thomas Evangelical Church of India – UK Parish (Registered Charity No. 1143030) operates the website steciuk.org.</p>

<h3>Information We Collect</h3>
<p>We may collect personal information when you submit contact forms, prayer requests, membership registrations, or event enquiries. This may include your name, email address, phone number, and any information you choose to provide.</p>

<h3>How We Use Your Information</h3>
<ul>
<li>To respond to your enquiries and requests</li>
<li>To provide pastoral care and parish communications</li>
<li>To administer events and membership</li>
<li>To improve our website and services</li>
</ul>

<h3>Data Security</h3>
<p>We take appropriate measures to protect your personal data. We do not sell or share your information with third parties for marketing purposes.</p>

<h3>Your Rights</h3>
<p>Under UK data protection law, you have rights to access, correct, or request deletion of your personal data. Contact <a href="mailto:admin@steciuk.org">admin@steciuk.org</a> to exercise these rights.</p>

<h3>Cookies</h3>
<p>This website may use essential cookies for functionality. Analytics cookies, if used, will be disclosed here.</p>
HTML;
    }

    public static function termsOfUse(): string
    {
        return <<<'HTML'
<h2>Terms of Use</h2>
<p><em>Last updated: placeholder — please review with legal counsel before publication.</em></p>

<h3>Acceptance of Terms</h3>
<p>By accessing steciuk.org, you agree to these terms of use. If you do not agree, please do not use this website.</p>

<h3>Use of Content</h3>
<p>Content on this website is provided for informational and spiritual purposes. Sermons, liturgical materials, and written content may be reproduced for personal or parish use with appropriate attribution. Commercial use requires written permission.</p>

<h3>Accuracy</h3>
<p>We endeavour to keep information accurate and up to date but make no warranties regarding completeness or accuracy. Service times, events, and leadership details may change — please contact the parish office to confirm.</p>

<h3>External Links</h3>
<p>This website may contain links to external sites. We are not responsible for the content or privacy practices of linked websites.</p>

<h3>Limitation of Liability</h3>
<p>The St. Thomas Evangelical Church of India – UK Parish shall not be liable for any damages arising from use of this website.</p>

<h3>Contact</h3>
<p>Questions about these terms? Email <a href="mailto:admin@steciuk.org">admin@steciuk.org</a>.</p>
HTML;
    }
}
