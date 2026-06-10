<?php

namespace App\Support;

/**
 * Canonical reference copy for STECI UK Parish settings and core pages.
 * Applied on deploy via content migrations — not via site:sync-reference-data.
 */
class ReferenceSiteContent
{
    /**
     * @return array<string, array{value: string, group: string}>
     */
    public static function settings(): array
    {
        return [
            'gospel_reminder_reference' => [
                'value' => 'Revelation 1:9',
                'group' => 'general',
            ],
            'seo_default_description' => [
                'value' => 'UK Parish of the St. Thomas Evangelical Church of India (STECI) — an evangelical Oriental Protestant church in the Saint Thomas Christian tradition. Monthly worship in Manchester, Leicester, Dartford, Sunderland, and Bristol. Registered Charity 1143030. Member of the Evangelical Alliance.',
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
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function pageFields(): array
    {
        return [
            'home' => [
                'seo_description' => 'UK Parish of the St. Thomas Evangelical Church of India (STECI). Evangelical Oriental Protestant worship across Manchester, Leicester, Dartford, Sunderland, and Bristol — in person and online.',
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function homeContentBlockPatches(): array
    {
        return [
            'welcome-quote' => [
                'quote' => 'We welcome you to worship centred on Scripture, the Gospel of Jesus Christ, and the sacraments of the Church — in the warm fellowship of an evangelical Oriental Protestant parish rooted in the Saint Thomas Christian tradition.',
            ],
        ];
    }

    public static function welcome(): string
    {
        return <<<'HTML'
<p>Welcome to the UK Parish of the <strong>St. Thomas Evangelical Church of India</strong> (STECI). We are delighted that you are visiting our website and hope you will feel drawn to join us in worship, prayer, and fellowship.</p>

<p>Our parish serves approximately <strong>ninety families</strong> spread across the United Kingdom, gathering for monthly worship at five locations: <strong>Manchester, Leicester, Dartford, Sunderland, and Bristol</strong>. Members join both <strong>in person and online</strong>. Though geographically dispersed, we are united by our faith in Jesus Christ, our commitment to the authority of the Holy Bible, and our heritage in the Saint Thomas Christian tradition of Kerala, India.</p>

<p>The <strong>St. Thomas Evangelical Church of India</strong> was founded on <strong>26 January 1961</strong> and is headquartered at <strong>Manjadi, Thiruvalla, Kerala</strong>. STECI is an evangelical <strong>Oriental Protestant</strong> church in the Saint Thomas Syrian Christian tradition, with episcopal church order and a missionary calling to proclaim the Gospel in India and among diaspora communities worldwide. The UK Parish is a registered charity (No. <strong>1143030</strong>) and a member of the <strong>Evangelical Alliance</strong>.</p>

<p>Whether you are exploring Christianity for the first time, reconnecting with your faith, or seeking a spiritual home within the Saint Thomas Christian tradition, you are warmly welcome. Please explore our service times, ministries, and events — or <a href="/contact">contact us</a> directly. We would love to hear from you.</p>

<p><em>"For the Word of God and for the testimony of Jesus Christ."</em> — Revelation 1:9</p>
HTML;
    }

    public static function ourChurch(): string
    {
        return <<<'HTML'
<h2>Who We Are</h2>
<p>The St. Thomas Evangelical Church of India – UK Parish is part of <strong>STECI</strong>, a global evangelical Oriental Protestant church with episcopal church order and deep roots in the Saint Thomas Christian community of Kerala. Founded on <strong>26 January 1961</strong>, STECI emerged from a reform movement within the <strong>Malankara Mar Thoma Syrian Church</strong>, seeking stricter adherence to biblical authority, <em>sola scriptura</em>, and evangelical doctrine. Our UK Parish gathers families across Britain under episcopal oversight from STECI headquarters at <strong>Manjadi, Thiruvalla, Kerala</strong>, and is a member of the <strong>Evangelical Alliance</strong>. Learn more about the parent church at <a href="https://steci.org/" target="_blank" rel="noopener noreferrer">steci.org</a>.</p>

<h2>What We Believe</h2>
<p>STECI confesses the historic Christian faith as revealed in Holy Scripture and summarised in the <strong>Nicene Creed</strong>. Our core convictions include:</p>
<ul>
<li>The <strong>Holy Trinity</strong> — Father, Son, and Holy Spirit</li>
<li><strong>Sola Scriptura</strong> — the sixty-six books of the Bible as the inspired, supreme authority for faith and practice</li>
<li>Jesus Christ as Lord and Saviour; salvation by <strong>grace through faith in Christ alone</strong>, not by works</li>
<li>The <strong>Second Coming</strong> of Christ, the resurrection of the dead, and the call to holy living</li>
<li>The <strong>priesthood of all believers</strong> — prayer to God through Christ alone, without veneration of saints or prayers for the dead</li>
<li>Two sacraments instituted by Christ: <strong>Baptism</strong> (a sign of spiritual rebirth for professing believers and for children of Christian parents) and the <strong>Lord's Supper</strong> (a memorial of Christ's sacrifice)</li>
<li>The <strong>Great Commission</strong> — every member called to evangelise and make disciples in the United Kingdom and beyond</li>
</ul>

<h2>How We Worship</h2>
<p>Our worship follows STECI's evangelical, Scripture-centred pattern: Bible readings, preaching, congregational hymns, prayer, and the Lord's Supper — without elaborate ritual. In the UK Parish, services are conducted primarily in <strong>English</strong>, with Malayalam hymns and readings at many gatherings, reflecting the diaspora context described in STECI parishes worldwide.</p>

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
<p>Tensions over biblical authority and worship practice intensified within the Mar Thoma Syrian Church in the 1950s. Reform-minded believers, including the <strong>Pathiopadesa Samathy</strong> (Organisation for Sound Doctrine), called for strict <em>sola scriptura</em> and rejection of practices they considered unbiblical. After four presbyters were suspended in <strong>November 1960</strong>, the reformists constituted the <strong>St. Thomas Evangelical Church of India</strong> as an independent church on <strong>26 January 1961</strong> at <strong>Thaimala, Thiruvalla</strong>, with headquarters at <strong>Manjadi, Thiruvalla, Kerala</strong>.</p>

<h2>Evangelical Oriental Protestant Identity</h2>
<p>STECI is an <strong>evangelical Oriental Protestant</strong> church with <strong>episcopal</strong> polity, governed by its representative synod (<strong>Prathinidhi Sabha</strong>) and organised into <strong>seven dioceses</strong> — four in Kerala plus dioceses for other regions of India, the Gulf &amp; Singapore, and North America &amp; Europe. STECI was formed to:</p>
<ul>
<li><strong>Safeguard sound doctrine</strong> according to Scripture</li>
<li><strong>Live a holy life</strong> in obedience to Christ</li>
<li><strong>Obey the Great Commission</strong> to evangelise India and the nations</li>
</ul>
<p>Today STECI serves more than <strong>350 congregations</strong> and approximately <strong>100,000 members</strong> worldwide, with extensive missionary work and institutions such as <strong>Jubilee Memorial Bible College</strong> (Chennai) and the church publication <em>Suvishesha Prakasini</em>. The church is united by the motto: <em>For the Word of God and for the testimony of Jesus Christ</em> (Revelation 1:9).</p>

<h2>STECI in the United Kingdom</h2>
<p>The UK Parish serves the STECI diaspora community across Britain — approximately <strong>ninety families</strong> gathering monthly in five cities, in person and online — providing spiritual home, pastoral care, and worship while remaining part of STECI's global fellowship. Official information about the parent church is available at <a href="https://steci.org/" target="_blank" rel="noopener noreferrer">steci.org</a>.</p>
HTML;
    }

    public static function missionVision(): string
    {
        return <<<'HTML'
<h2>Our Mission</h2>
<p>As the UK Parish of STECI, we share in the church's founding mission: to <strong>safeguard sound doctrine</strong>, encourage <strong>holy living</strong>, and fulfil the <strong>Great Commission</strong> — glorifying God through worship, proclaiming the Gospel of Jesus Christ, nurturing believers in discipleship, and serving our community across the United Kingdom in partnership with the wider STECI fellowship.</p>

<h2>Our Vision</h2>
<p>A vibrant, spiritually mature parish community across the United Kingdom where families of all generations:</p>
<ul>
<li>Worship God in spirit and truth</li>
<li>Grow deep in knowledge of Scripture</li>
<li>Build strong Christian homes and friendships</li>
<li>Reach neighbours and nations with the love of Christ</li>
<li>Remain connected to the heritage and mission of STECI</li>
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
<p>Our parish family of approximately <strong>ninety households</strong> gathers for monthly worship at five locations across the United Kingdom. Church members attend services <strong>both physically and online</strong>. Each congregation enjoys local fellowship while sharing in parish-wide events, prayer, and mission.</p>

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

<p>For addresses, contact details, and current service schedules, please visit our <a href="/service-times">Service Times</a> page or contact the parish office at <a href="mailto:admin@steciuk.org">admin@steciuk.org</a>.</p>
HTML;
    }
}
