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

    public const EAUK_HOME_URL = 'https://www.eauk.org/';

    public const EAUK_CHURCH_URL = 'https://www.eauk.org/churches/st-thomas-evangelical-church-of-india-uk-parish';

    public const EAUK_BRAND_URL = 'https://www.eauk.org/brand/logos';

    public const EAUK_MEMBERSHIP_NUMBER = '300947';

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
            'contact_address_line_1' => [
                'value' => '',
                'group' => 'contact',
            ],
            'contact_address_line_2' => [
                'value' => '',
                'group' => 'contact',
            ],
            'contact_city' => [
                'value' => '',
                'group' => 'contact',
            ],
            'contact_county' => [
                'value' => '',
                'group' => 'contact',
            ],
            'contact_postcode' => [
                'value' => '',
                'group' => 'contact',
            ],
            'contact_country' => [
                'value' => 'United Kingdom',
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
                'value' => 'STECI worldwide motto',
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
                'value' => 'An evangelical Oriental Protestant parish in the Saint Thomas Christian tradition — gathering monthly for worship across Manchester, Leicester, Dartford, Sunderland, and Bristol.',
                'group' => 'general',
            ],
            'footer_tagline' => [
                'value' => 'Word, worship, and witness across the United Kingdom.',
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
                'title' => 'Heavenly peace',
                'text' => 'Christ\'s peace guards heart and mind — a gift from above for every believer who draws near in worship.',
                'ref' => 'Philippians 4:7',
            ],
            [
                'icon' => '🙏',
                'title' => 'Bold in prayer',
                'text' => 'Cast every burden on the Lord. Our parish family intercedes with you before the throne of grace.',
                'ref' => 'Matthew 11:28',
                'link' => '/prayer-request',
                'linkLabel' => 'Submit a prayer request',
            ],
            [
                'icon' => '📖',
                'title' => 'Fed by Scripture',
                'text' => 'God\'s Word nourishes faith — through preaching, reading, and Holy Communion at the Lord\'s table.',
                'ref' => 'Romans 15:4',
                'link' => '/sermons',
                'linkLabel' => 'Listen to a sermon',
            ],
            [
                'icon' => '✝',
                'title' => 'Assurance in grace',
                'text' => 'Salvation is by grace through faith in Christ alone — mercy from heaven, not merit from us.',
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
                'label' => 'Member area',
                'seed_key' => 'member-area',
                'children' => [
                    ['label' => 'Sign in', 'url' => '/login', 'seed_key' => 'members.sign-in'],
                    ['label' => 'Join the parish', 'url' => '/register', 'seed_key' => 'members.join'],
                ],
            ],
            [
                'label' => 'Contact',
                'seed_key' => 'contact',
                'children' => [
                    ['label' => 'Contact Us', 'slug' => 'contact', 'seed_key' => 'contact.contact-us'],
                    ['label' => 'Prayer Request', 'slug' => 'prayer-request', 'seed_key' => 'contact.prayer-request'],
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

<p>Whether you are exploring Christianity for the first time, reconnecting with your faith, or seeking a spiritual home within the Saint Thomas Christian tradition, you are warmly welcome. Please explore our <a href="/service-times">service times</a>, <a href="/ministries">ministries</a>, and <a href="/events">events</a> — or read about <a href="/our-church">what we believe</a> and <a href="/steci-heritage">STECI heritage</a>. We would love to hear from you through our <a href="/contact">contact page</a>.</p>

<p><em>"For the Word of God and for the testimony of Jesus Christ."</em> — Revelation 1:9</p>
HTML;
    }

    public static function ourChurch(): string
    {
        $eaukUrl = self::EAUK_CHURCH_URL;
        $eaukBrand = self::EAUK_BRAND_URL;
        $charity = self::CHARITY_NUMBER;

        return <<<HTML
<h2 id="who-we-are">Who We Are</h2>
<p>The <strong>St. Thomas Evangelical Church of India – UK Parish</strong> is the British fellowship of <strong>STECI</strong> — a global evangelical Oriental Protestant church rooted in the Saint Thomas Christian tradition of Kerala. Founded on <strong>26 January 1961</strong>, STECI emerged from a reform movement within the Malankara Mar Thoma Syrian Church, seeking faithful adherence to <em>sola scriptura</em>, the priesthood of all believers, and the evangelical Gospel.</p>
<p>Our UK Parish gathers approximately <strong>ninety families</strong> for monthly worship across five cities — <strong>Manchester, Leicester, Dartford, Sunderland, and Bristol</strong> — meeting <strong>in person and online</strong>. We remain under episcopal oversight from STECI headquarters at <strong>Manjadi, Thiruvalla, Kerala</strong>, united by our motto: <em>For the Word of God and for the testimony of Jesus Christ</em> (Revelation 1:9). We are a registered charity (No. <strong>{$charity}</strong>).</p>

<h2 id="where-we-gather">Where &amp; How We Gather</h2>
<p>Although our families are spread across Britain, we worship as one parish family. Each month we gather for Holy Communion, biblical preaching, congregational prayer, and fellowship — with many members joining online when they cannot travel. Our worship is Scripture-centred and evangelical, conducted primarily in <strong>English</strong> with Malayalam hymns and readings at many gatherings.</p>
<ul>
<li><strong>Manchester</strong> — primary fellowship hub in the North West</li>
<li><strong>Leicester</strong> — East Midlands congregation</li>
<li><strong>Dartford</strong> — South East fellowship near London</li>
<li><strong>Sunderland</strong> — North East gathering</li>
<li><strong>Bristol</strong> — South West congregation</li>
</ul>
<p>Visit our <a href="/service-times">service times page</a> for current schedules, or contact the <a href="/contact">parish office</a> to confirm dates and venues.</p>

<h2 id="evangelical-alliance">Evangelical Alliance Membership</h2>
<p>We are proud to be a <a href="{$eaukUrl}" target="_blank" rel="noopener noreferrer">member church of the Evangelical Alliance</a> — the largest evangelical body in the United Kingdom, uniting Christians who confess the historic evangelical faith. Membership signifies our commitment to sound doctrine, united witness, and accountable church life alongside fellow evangelical congregations.</p>
<p>Our membership is displayed using the official Evangelical Alliance member logo in accordance with the <a href="{$eaukBrand}" target="_blank" rel="noopener noreferrer">EAUK brand guidelines</a>. You can view our public church profile on the Evangelical Alliance website.</p>

<h2 id="what-we-believe">What We Believe</h2>
<p>We confess the evangelical Oriental Protestant faith of STECI — the historic Christian faith as revealed in Holy Scripture and summarised in the <strong>Nicene Creed</strong>. We hold to the supreme authority of the Bible, salvation by grace through faith in Jesus Christ alone, and the call to holy living until Christ returns.</p>

<h3>Holy Scripture</h3>
<p>We believe the sixty-six books of the Old and New Testaments are the inspired, infallible Word of God — our final authority for doctrine, worship, and Christian living. Scripture is living and active, profitable for teaching, rebuke, correction, and training in righteousness (2 Timothy 3:16–17).</p>

<h3>God the Trinity</h3>
<p>We worship one God in three persons: <strong>Father, Son, and Holy Spirit</strong> — co-equal, co-eternal, and of one substance. God created all things, sustains the universe, and redeems sinners through his Son.</p>

<h3>Jesus Christ</h3>
<p>We believe in the full deity and true humanity of Jesus Christ, his virgin birth, sinless life, atoning death on the cross, bodily resurrection, ascension, and his return in glory to judge the living and the dead. He alone is Lord and Saviour — the only mediator between God and humanity (1 Timothy 2:5).</p>

<h3>Salvation by Grace</h3>
<p>Salvation is by <strong>grace through faith</strong> in Christ alone — not earned by human works, church tradition, or merit. All who repent and trust in Jesus are justified, adopted into God's family, and assured of eternal life. We rejoice in the finished work of the cross and the empty tomb.</p>

<h3>The Holy Spirit</h3>
<p>The Spirit convicts the world of sin, regenerates believers, indwells the church, and empowers us for holy living and witness. He distributes gifts for the building up of the body of Christ and produces the fruit of godly character in those who walk by faith.</p>

<h3>The Church</h3>
<p>The church is the body of Christ — a fellowship of believers called out from the world to worship, discipleship, and mission. We uphold the <strong>priesthood of all believers</strong>: every Christian may approach God directly through Christ, without veneration of saints or prayers for the dead.</p>

<h3>Sacraments</h3>
<p>Christ instituted two sacraments: <strong>Baptism</strong> (including the baptism of children born to Christian parents) and the <strong>Lord's Supper</strong>. Holy Communion is observed as a memorial of Christ's sacrifice — a sacred meal of remembrance, thanksgiving, and spiritual nourishment for believers.</p>

<h3>The Last Things</h3>
<p>We await the personal return of Jesus Christ, the resurrection of the dead, the final judgment, and the new heaven and earth where God will dwell with his people forever. Until that day we live as pilgrims, witnesses, and servants of the Gospel.</p>

<h3>The Nicene Creed</h3>
<blockquote>
<p><em>We believe in one God, the Father Almighty, Maker of heaven and earth, and of all things visible and invisible. And in one Lord Jesus Christ, the only-begotten Son of God, begotten of the Father before all worlds; God of God, Light of Light, very God of very God; begotten, not made, being of one substance with the Father, by whom all things were made… And we look for the resurrection of the dead, and the life of the world to come. Amen.</em></p>
</blockquote>

<h3>What We Reject</h3>
<p>In continuity with the reform vision of <strong>Abraham Malpan</strong> and the founding of STECI, we do not practise veneration of saints, prayers for the dead, auricular confession to priests, idolatry, or worship directed to icons or statues. Our faith is centred on Christ and Scripture alone.</p>

<h2 id="how-we-worship">How We Worship</h2>
<p>Our worship follows STECI's evangelical pattern: readings from Holy Scripture, expository preaching, congregational hymns, united prayer, and Holy Communion — without elaborate ritual or ceremonial excess. We gather to hear God's Word, respond in praise, and encourage one another in faith.</p>

<h2 id="parish-life">Parish Life &amp; Mission</h2>
<p>Across our five locations we support one another through pastoral care, Sunday School, youth and women's fellowships, choir, prayer groups, and community events — in person and online. We pray for our neighbours, support global mission, and invite all who seek Christ to worship with us.</p>
<p>Whether you are exploring faith for the first time or seeking a spiritual home within the Saint Thomas Christian tradition, you are warmly welcome. <a href="/contact">Contact the parish office</a>, submit a <a href="/prayer-request">prayer request</a>, or explore our <a href="/ministries">ministries</a> to learn more.</p>
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
<li><a href="/ministries/evangelism-mission">Evangelism &amp; Mission</a> — Gospel outreach and mission support</li>
<li><a href="/ministries/pastoral-care">Pastoral Care</a> — Spiritual support for families</li>
<li><a href="/ministries/community-fellowship">Community Fellowship</a> — Building friendships between worship gatherings</li>
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
<p>We would love to hear from you. Whether you have a question about monthly worship, want pastoral support, or are interested in joining our parish, please use the contact form or the parish office details shown on this page.</p>

<p>Our UK Parish gathers for monthly worship in <strong>Manchester, Leicester, Dartford, Sunderland, and Bristol</strong>. For current dates and venues, visit <a href="/service-times">Service Times</a>.</p>

<p>We are a registered charity (No. <strong>1143030</strong>) and a <a href="https://www.eauk.org/churches/st-thomas-evangelical-church-of-india-uk-parish" target="_blank" rel="noopener noreferrer">member of the Evangelical Alliance</a>.</p>
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
<h2>Join the parish</h2>
<p>Welcome! To join the St. Thomas Evangelical Church of India – UK Parish, <a href="/register">create your parish member account</a> with your UK address and preferred worship location. Registrations are reviewed by the parish leadership team before your account is activated.</p>
<p>Membership is open to those who profess faith in Jesus Christ and wish to participate in the life and mission of our parish.</p>
HTML;
    }

    public static function privacyPolicy(): string
    {
        return <<<'HTML'
<h2>Privacy Policy</h2>
<p><em>Last updated: June 2026 (version 2026-06-v2).</em></p>

<h3>Who we are</h3>
<p>The <strong>St. Thomas Evangelical Church of India – UK Parish</strong> (Registered Charity No. 1143030) is the data controller for personal data collected through steciuk.org and our parish membership systems. For data protection enquiries contact <a href="mailto:admin@steciuk.org">admin@steciuk.org</a>.</p>

<h3>What personal data we collect</h3>
<p>Depending on how you interact with us, we may collect:</p>
<ul>
<li><strong>Account & membership:</strong> name, email, phone, date of birth, UK address, preferred worship location, profile photo, family/household relationship, and account status.</li>
<li><strong>Household members:</strong> names and details of spouses, children, and other household members you register or manage (including children without email addresses).</li>
<li><strong>Giving records:</strong> donation amounts, dates, payment method, bank references, and optional notes (for example Gift Aid declarations).</li>
<li><strong>Website forms:</strong> contact enquiries, prayer requests, and new member enquiries you submit voluntarily.</li>
<li><strong>Technical data:</strong> IP address and security logs when you register, sign in, or use protected areas of the site.</li>
</ul>

<h3>How and why we use your data (lawful bases)</h3>
<p>Under UK GDPR and the Data Protection Act 2018 we rely on the following lawful bases:</p>
<ul>
<li><strong>Contract / steps at your request:</strong> creating and administering your parish member account, household profiles, and portal access.</li>
<li><strong>Legitimate interests:</strong> parish administration, pastoral care, communications about worship and parish life, fraud prevention, and keeping our community safe — balanced against your rights.</li>
<li><strong>Legal obligation:</strong> charity accounting, Gift Aid and financial record-keeping, and responding to lawful requests from regulators.</li>
<li><strong>Consent:</strong> optional marketing emails about parish news and events; you may withdraw consent at any time in your account or by emailing us.</li>
</ul>
<p>Where you register household members (including children), we process their data on the basis that you have authority to provide it and have confirmed this when registering or adding them.</p>

<h3>Children</h3>
<p>Children may be listed on a family account by a parent or guardian with parish approval. We collect only what is needed for parish membership and pastoral care. Children without their own email are not given independent login access unless approved separately.</p>

<h3>Who we share data with</h3>
<p>We do not sell personal data. We may share limited information with:</p>
<ul>
<li>Parish leadership and authorised administrators who need it for membership, pastoral care, or finance.</li>
<li>Service providers who host or support our website (under data processing agreements).</li>
<li>HMRC, auditors, or regulators where required by law (for example Gift Aid or charity reporting).</li>
</ul>

<h3>International transfers</h3>
<p>Our website and email services may use providers outside the UK. Where this occurs, we ensure appropriate safeguards (such as UK adequacy regulations or standard contractual clauses) are in place.</p>

<h3>How long we keep data</h3>
<ul>
<li><strong>Inactive member accounts:</strong> reviewed after 24 months of inactivity; we may anonymise or delete data that is no longer needed.</li>
<li><strong>Giving & finance records:</strong> retained for at least 6 years to meet UK charity and tax requirements; amounts may be kept in anonymised form after account deletion.</li>
<li><strong>Form submissions:</strong> typically retained for up to 24 months unless a longer period is needed for pastoral follow-up.</li>
<li><strong>Security logs:</strong> kept for a limited period for fraud prevention and audit.</li>
</ul>

<h3>Your rights</h3>
<p>You have the right to:</p>
<ul>
<li>Access a copy of your personal data (download available in your member account).</li>
<li>Rectify inaccurate data (update your profile or contact us).</li>
<li>Request erasure in certain circumstances (request deletion in your account; some financial records may be retained in anonymised form where the law requires).</li>
<li>Restrict or object to processing in certain cases.</li>
<li>Withdraw marketing consent at any time.</li>
<li>Lodge a complaint with the <a href="https://ico.org.uk/make-a-complaint/" target="_blank" rel="noopener noreferrer">Information Commissioner's Office (ICO)</a>.</li>
</ul>
<p>To exercise your rights, sign in to your account or email <a href="mailto:admin@steciuk.org">admin@steciuk.org</a>. We will respond within one month.</p>

<h3>Security</h3>
<p>We use access controls, encryption in transit, approval workflows, and audit logging to protect personal data. Only authorised parish staff can access member and giving records.</p>

<h3>Cookies</h3>
<p>Essential cookies and session storage are used so you can sign in and use the member portal. If we introduce non-essential analytics cookies, we will update this policy and ask for consent where required.</p>

<h3>Changes</h3>
<p>We may update this policy when our practices or the law change. Material changes will be reflected on this page with a new version date; continued use of member services after notice may require renewed consent where applicable.</p>
HTML;
    }

    public static function termsOfUse(): string
    {
        return <<<'HTML'
<h2>Terms of Use</h2>
<p><em>Last updated: June 2026.</em></p>

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
