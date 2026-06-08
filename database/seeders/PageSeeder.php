<?php

namespace Database\Seeders;

use App\Enums\ContentBlockType;
use App\Enums\PublishStatus;
use App\Models\ContentBlock;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::query()->where('email', 'admin@steciuk.org')->value('id');

        if (! $adminId) {
            throw new \RuntimeException('Admin user must be seeded before pages.');
        }

        foreach ($this->pages() as $pageData) {
            $blocks = $pageData['content_blocks'] ?? [];
            unset($pageData['content_blocks']);

            $page = Page::query()->updateOrCreate(
                ['slug' => $pageData['slug']],
                array_merge($pageData, [
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                    'status' => PublishStatus::Published,
                ]),
            );

            foreach ($blocks as $index => $block) {
                ContentBlock::query()->updateOrCreate(
                    [
                        'page_id' => $page->id,
                        'type' => $block['type'],
                        'sort_order' => $block['sort_order'] ?? ($index + 1),
                    ],
                    [
                        'title' => $block['title'] ?? null,
                        'content' => $block['content'] ?? [],
                        'is_visible' => $block['is_visible'] ?? true,
                    ],
                );
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pages(): array
    {
        return [
            $this->homePage(),
            $this->simplePage('Welcome', 'welcome', 'about', 'Welcome to Our Parish', 'A warm invitation to worship and fellowship', $this->welcomeContent()),
            $this->simplePage('Our Church', 'our-church', 'about', 'Our Church', 'Who we are and what we believe', $this->ourChurchContent()),
            $this->simplePage('STECI Heritage', 'steci-heritage', 'about', 'STECI Heritage', 'Rooted in the Saint Thomas Christian tradition', $this->heritageContent()),
            $this->simplePage('Mission & Vision', 'mission-vision', 'about', 'Mission & Vision', 'Our calling to worship, witness, and service', $this->missionVisionContent()),
            $this->simplePage('Leadership', 'leadership', 'about', 'Parish Leadership', 'Those who serve our UK Parish community', $this->leadershipContent()),
            $this->simplePage('UK Locations', 'uk-locations', 'about', 'UK Parish Locations', 'Five worship locations across the United Kingdom', $this->locationsContent()),
            $this->simplePage('Service Times', 'service-times', 'default', 'Service Times', 'Find worship near you', $this->serviceTimesContent()),
            $this->simplePage('Online Worship', 'online-worship', 'default', 'Online Worship', 'Join us from wherever you are', $this->onlineWorshipContent()),
            $this->simplePage('Sermons', 'sermons', 'default', 'Sermons & Messages', 'Biblical teaching from our parish', $this->sermonsContent()),
            $this->simplePage('Ministries', 'ministries', 'default', 'Our Ministries', 'Serving God and one another across the UK', $this->ministriesContent()),
            $this->simplePage('Sunday School', 'sunday-school', 'default', 'Sunday School', 'Nurturing children in faith', $this->sundaySchoolContent()),
            $this->simplePage('Youth Fellowship', 'youth-fellowship', 'default', 'Youth Fellowship', 'Growing together in Christ', $this->youthFellowshipContent()),
            $this->simplePage("Women's Fellowship", 'womens-fellowship', 'default', "Women's Fellowship", 'Prayer, fellowship, and service', $this->womensFellowshipContent()),
            $this->simplePage('Choir', 'choir', 'default', 'Parish Choir', 'Worship through music', $this->choirContent()),
            $this->simplePage('Prayer Groups', 'prayer-groups', 'default', 'Prayer Groups', 'United in prayer across the UK', $this->prayerGroupsContent()),
            $this->simplePage('Events', 'events', 'default', 'Parish Events', 'Upcoming gatherings and celebrations', $this->eventsContent()),
            $this->simplePage('News', 'news', 'default', 'News & Announcements', 'Latest updates from the UK Parish', $this->newsContent()),
            $this->simplePage('Gallery', 'gallery', 'default', 'Photo Gallery', 'Moments from our parish life', $this->galleryContent()),
            $this->simplePage('Resources', 'resources', 'default', 'Resources & Downloads', 'Liturgy, forms, and parish documents', $this->resourcesContent()),
            $this->simplePage('Liturgy', 'liturgy', 'default', 'Liturgy', 'Order of worship and liturgical resources', $this->liturgyContent()),
            $this->simplePage('Lectionary', 'lectionary', 'default', 'Lectionary', 'Scripture readings for worship', $this->lectionaryContent()),
            $this->simplePage('Safeguarding', 'safeguarding', 'default', 'Safeguarding', 'Protecting children and vulnerable adults', $this->safeguardingContent()),
            $this->simplePage('Contact', 'contact', 'contact', 'Contact Us', 'We would love to hear from you', $this->contactContent()),
            $this->simplePage('Prayer Request', 'prayer-request', 'form', 'Prayer Request', 'Share your prayer needs with us', $this->prayerRequestContent()),
            $this->simplePage('New Member', 'new-member', 'form', 'New Member Registration', 'Join our parish family', $this->newMemberContent()),
            $this->simplePage('Privacy Policy', 'privacy-policy', 'default', 'Privacy Policy', 'How we handle your personal data', $this->privacyPolicyContent()),
            $this->simplePage('Terms of Use', 'terms-of-use', 'default', 'Terms of Use', 'Website terms and conditions', $this->termsOfUseContent()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function simplePage(string $title, string $slug, string $template, string $heroTitle, string $heroSubtitle, string $content): array
    {
        return [
            'title' => $title,
            'slug' => $slug,
            'hero_title' => $heroTitle,
            'hero_subtitle' => $heroSubtitle,
            'content' => $content,
            'seo_title' => "{$title} | STECI UK Parish",
            'seo_description' => strip_tags(substr($content, 0, 160)),
            'template' => $template,
            'sort_order' => 0,
            'is_home' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function homePage(): array
    {
        return [
            'title' => 'Home',
            'slug' => 'home',
            'hero_title' => null,
            'hero_subtitle' => null,
            'show_hero' => false,
            'content' => null,
            'seo_title' => 'St. Thomas Evangelical Church of India – UK Parish',
            'seo_description' => 'Welcome to the UK Parish of STECI. Join us for worship in Manchester, Leicester, Dartford, Sunderland, and Bristol.',
            'template' => 'home',
            'sort_order' => 0,
            'is_home' => true,
            'content_blocks' => [
                [
                    'type' => ContentBlockType::Hero,
                    'title' => 'Hero Banner',
                    'sort_order' => 1,
                    'content' => [
                        'eyebrow' => 'St. Thomas Evangelical Church of India',
                        'headline' => 'Welcome to Our UK Parish',
                        'subtitle' => 'For the Word of God and for the testimony of Jesus Christ',
                        'badge' => 'UK Parish',
                        'stats' => [
                            ['value' => '5', 'label' => 'UK Locations'],
                            ['value' => '90+', 'label' => 'Families'],
                            ['value' => '1961', 'label' => 'STECI Founded'],
                        ],
                        'primary_cta_label' => 'Plan Your Visit',
                        'primary_cta_url' => '/service-times',
                        'secondary_cta_label' => 'Watch Online',
                        'secondary_cta_url' => '/online-worship',
                        'tertiary_cta_label' => 'View Events',
                        'tertiary_cta_url' => '/events',
                    ],
                ],
                [
                    'type' => ContentBlockType::Location,
                    'title' => 'Service Locations',
                    'sort_order' => 2,
                    'content' => [
                        'heading' => 'Worship Across the UK',
                        'subheading' => 'Monthly worship services in five locations',
                        'locations' => ['Manchester', 'Leicester', 'Dartford', 'Sunderland', 'Bristol'],
                        'link_url' => '/service-times',
                        'link_label' => 'View All Service Times',
                    ],
                ],
                [
                    'type' => ContentBlockType::Quote,
                    'title' => 'Welcome Message',
                    'sort_order' => 3,
                    'content' => [
                        'quote' => 'It is a joy to welcome you to our parish family. Whether you join us in person at one of our five UK locations or online, we pray you will find a warm community rooted in Scripture, worship, and the rich heritage of the Saint Thomas Christian tradition.',
                        'attribution' => 'Rev. [Name Placeholder], UK Parish Vicar',
                        'link_url' => '/welcome',
                        'link_label' => 'Read Full Welcome Message',
                    ],
                ],
                [
                    'type' => ContentBlockType::MinistryCards,
                    'title' => 'Our Ministries',
                    'sort_order' => 4,
                    'content' => [
                        'heading' => 'Serving Together',
                        'subheading' => 'Discover how you can grow and serve in our parish',
                        'limit' => 4,
                        'link_url' => '/ministries',
                        'link_label' => 'View All Ministries',
                    ],
                ],
                [
                    'type' => ContentBlockType::EventList,
                    'title' => 'Upcoming Events',
                    'sort_order' => 5,
                    'content' => [
                        'heading' => 'Upcoming Events',
                        'limit' => 3,
                        'link_url' => '/events',
                        'link_label' => 'See All Events',
                    ],
                ],
                [
                    'type' => ContentBlockType::TextImage,
                    'title' => 'Latest News',
                    'sort_order' => 6,
                    'content' => [
                        'heading' => 'Latest News',
                        'body' => 'Stay connected with parish announcements, fellowship updates, and community news from across our UK locations.',
                        'link_url' => '/news',
                        'link_label' => 'Read All News',
                        'image_alt' => 'Parish community gathering',
                    ],
                ],
                [
                    'type' => ContentBlockType::SermonList,
                    'title' => 'Recent Sermons',
                    'sort_order' => 7,
                    'content' => [
                        'heading' => 'Recent Sermons',
                        'limit' => 3,
                        'link_url' => '/sermons',
                        'link_label' => 'Browse All Sermons',
                    ],
                ],
                [
                    'type' => ContentBlockType::Gallery,
                    'title' => 'Gallery Preview',
                    'sort_order' => 8,
                    'content' => [
                        'heading' => 'Parish Life in Pictures',
                        'limit' => 6,
                        'link_url' => '/gallery',
                        'link_label' => 'View Full Gallery',
                    ],
                ],
                [
                    'type' => ContentBlockType::Cta,
                    'title' => 'Prayer Request CTA',
                    'sort_order' => 9,
                    'content' => [
                        'heading' => 'Need Prayer?',
                        'body' => 'Our prayer team would be honoured to pray with you. Submit a prayer request and know that you are held in the prayers of our parish family.',
                        'button_label' => 'Submit a Prayer Request',
                        'button_url' => '/prayer-request',
                        'style' => 'primary',
                    ],
                ],
                [
                    'type' => ContentBlockType::Cta,
                    'title' => 'New Member CTA',
                    'sort_order' => 10,
                    'content' => [
                        'heading' => 'Join Our Parish Family',
                        'body' => 'Whether you are new to the area or exploring faith, we welcome you. Register your interest and a member of our leadership team will be in touch.',
                        'button_label' => 'Register as a New Member',
                        'button_url' => '/new-member',
                        'style' => 'secondary',
                    ],
                ],
            ],
        ];
    }

    private function welcomeContent(): string
    {
        return <<<'HTML'
<p>Welcome to the UK Parish of the <strong>St. Thomas Evangelical Church of India</strong> (STECI). We are delighted that you are visiting our website and hope you will feel drawn to join us in worship, prayer, and fellowship.</p>

<p>Our parish serves approximately <strong>ninety families</strong> spread across the United Kingdom, gathering for monthly worship at five locations: <strong>Manchester, Leicester, Dartford, Sunderland, and Bristol</strong>. Though geographically dispersed, we are united by our faith in Jesus Christ, our commitment to the authority of the Holy Bible, and our heritage in the Saint Thomas Christian tradition of Kerala, India.</p>

<p>STECI was founded in <strong>1961</strong> and is headquartered in <strong>Thiruvalla, Kerala</strong>. As an evangelical Episcopal church, we uphold historic Christian faith, episcopal oversight, and a missionary calling to proclaim the Gospel. The UK Parish is a registered charity (No. <strong>1143030</strong>) and exists to worship God, nurture disciples, care for families, and witness to Christ in British society.</p>

<p>Whether you are exploring Christianity for the first time, reconnecting with your faith, or seeking a spiritual home within the Saint Thomas Christian tradition, you are warmly welcome. Please explore our service times, ministries, and events — or <a href="/contact">contact us</a> directly. We would love to hear from you.</p>

<p><em>"For the Word of God and for the testimony of Jesus Christ."</em></p>
HTML;
    }

    private function ourChurchContent(): string
    {
        return <<<'HTML'
<h2>Who We Are</h2>
<p>The St. Thomas Evangelical Church of India – UK Parish is part of a global missionary church with deep roots in the Saint Thomas Christian community of Kerala. We are an <strong>evangelical Episcopal church</strong> — holding to the authority of Scripture, celebrating the sacraments, and living under episcopal spiritual oversight connected with STECI headquarters in Thiruvalla.</p>

<h2>What We Believe</h2>
<ul>
<li>The Holy Bible is the inspired and authoritative Word of God</li>
<li>Jesus Christ is Lord and Saviour — fully God and fully human</li>
<li>Salvation is by grace through faith in Christ alone</li>
<li>The Church is called to worship, fellowship, discipleship, evangelism, and service</li>
<li>We honour the heritage of the Saint Thomas Christian tradition while engaging faithfully with life in the United Kingdom</li>
</ul>

<h2>How We Worship</h2>
<p>Our worship blends Anglican-Episcopal liturgy with evangelical preaching and the warm fellowship characteristic of Indian Christian communities. Services include Scripture readings, hymns, prayers, a sermon, and Holy Communion. Worship is conducted primarily in English, with Malayalam hymns and readings at many gatherings.</p>

<h2>Our UK Parish</h2>
<p>With worship locations in five cities, our parish brings together families from diverse backgrounds who share a common faith and cultural heritage. We support one another through pastoral care, Sunday School, youth and women's fellowships, choir, prayer groups, and community events — both in person and online.</p>
HTML;
    }

    private function heritageContent(): string
    {
        return <<<'HTML'
<h2>The Saint Thomas Christian Tradition</h2>
<p>The Saint Thomas Christians trace their origins to the missionary activity of the Apostle Thomas in India in the first century. This ancient community in Kerala has maintained a distinctive witness to Christ across centuries, blending Indian culture with Christian faith.</p>

<h2>Founding of STECI</h2>
<p>The <strong>St. Thomas Evangelical Church of India</strong> was founded in <strong>1961</strong>, emerging from a renewal movement within the Saint Thomas Christian community that emphasised biblical authority, evangelical faith, and missionary outreach. The church's headquarters is located in <strong>Thiruvalla, Kerala</strong>, from which episcopal oversight, theological training, and mission work are coordinated.</p>

<h2>Evangelical Episcopal Identity</h2>
<p>STECI identifies as an <strong>evangelical Episcopal church</strong>. We uphold:</p>
<ul>
<li>The supreme authority of the Holy Bible in faith and practice</li>
<li>Historic Christian creeds and Episcopal church order</li>
<li>The priesthood of all believers and the call to personal conversion</li>
<li>A missionary vision to proclaim the Gospel in India and among diaspora communities worldwide</li>
</ul>

<h2>STECI in the United Kingdom</h2>
<p>The UK Parish serves the STECI diaspora community across Britain, providing spiritual home, pastoral care, and worship for families who have settled in the United Kingdom while maintaining connection with their church heritage. Our parish is part of STECI's wider global fellowship and shares in its mission and values.</p>
HTML;
    }

    private function missionVisionContent(): string
    {
        return <<<'HTML'
<h2>Our Mission</h2>
<p>To glorify God through worship, proclaim the Gospel of Jesus Christ, nurture believers in discipleship, and serve our community — in the United Kingdom and in partnership with the wider STECI fellowship — as a missionary Episcopal church rooted in the Saint Thomas Christian tradition.</p>

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

    private function leadershipContent(): string
    {
        return <<<'HTML'
<h2>Parish Leadership</h2>
<p>Our UK Parish is served by dedicated leaders who provide spiritual oversight, administration, and governance. The names and details below are placeholders and should be updated with current office holders.</p>

<p>Leadership profiles are displayed dynamically on this page from the parish leadership directory. Please contact the parish office if you need to reach any member of the leadership team.</p>

<h2>Parish Committee</h2>
<p>The UK Parish operates under the constitution of the St. Thomas Evangelical Church of India – UK Parish (Registered Charity No. 1143030). A parish committee supports the Vicar in planning worship, coordinating ministries, and overseeing parish affairs across our five locations.</p>

<p><em>Note: Leadership names and photographs are editable placeholders. Please update via the admin panel with verified information.</em></p>
HTML;
    }

    private function locationsContent(): string
    {
        return <<<'HTML'
<h2>Five Locations Across the UK</h2>
<p>Our parish family of approximately ninety households gathers for monthly worship at five locations across the United Kingdom. Each congregation enjoys local fellowship while sharing in parish-wide events, prayer, and mission.</p>

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

    private function serviceTimesContent(): string
    {
        return <<<'HTML'
<h2>Worship Services</h2>
<p>Our UK Parish holds <strong>monthly worship services</strong> at five locations across the United Kingdom. Services typically include worship, Scripture readings, a sermon, and Holy Communion.</p>

<p><strong>Please contact the parish office for the current schedule at each location.</strong> Service times may vary seasonally and are subject to change. We recommend confirming details before planning your visit.</p>

<p>Service location cards with addresses, map links, and online stream information are displayed below from our service directory.</p>

<p>For pastoral enquiries, email <a href="mailto:admin@steciuk.org">admin@steciuk.org</a> or call <strong>07578 189530</strong>.</p>
HTML;
    }

    private function onlineWorshipContent(): string
    {
        return <<<'HTML'
<h2>Join Us Online</h2>
<p>Unable to attend in person? You can participate in our parish worship online. Live streams are available during scheduled worship services, and recorded sermons are published on our <a href="/sermons">Sermons</a> page.</p>

<h2>Live Stream</h2>
<p>Our online worship stream is available during monthly services. Please check our <a href="/events">Events</a> page or contact the parish office for the next scheduled live stream.</p>

<p><em>YouTube channel placeholder: <a href="https://youtube.com/@steciuk" target="_blank" rel="noopener">youtube.com/@steciuk</a></em></p>

<h2>Online Prayer & Fellowship</h2>
<p>Our prayer groups also meet online weekly, keeping our dispersed parish family connected in intercession and Bible study. Contact the parish office to receive meeting links and schedules.</p>
HTML;
    }

    private function sermonsContent(): string
    {
        return <<<'HTML'
<h2>Sermons & Biblical Teaching</h2>
<p>Explore recent messages preached at our UK Parish worship services. Sermons are rooted in the exposition of Scripture and applied to daily Christian living.</p>

<p>Recent sermons are listed below. Many include video recordings via YouTube and may also offer audio or PDF downloads where available.</p>
HTML;
    }

    private function ministriesContent(): string
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

    private function sundaySchoolContent(): string
    {
        return <<<'HTML'
<p>Sunday School helps children grow in the knowledge of the Bible and Christian faith through age-appropriate teaching, songs, activities, and fellowship.</p>
<p>Classes are organised by age group and meet during parish worship gatherings across our UK locations. Dedicated teachers volunteer their time to nurture young hearts in the love of Christ.</p>
<p>To enrol your child or volunteer as a teacher, please <a href="/contact">contact the parish office</a>.</p>
HTML;
    }

    private function youthFellowshipContent(): string
    {
        return <<<'HTML'
<p>Youth Fellowship encourages young people to grow in Christ, build friendships, study the Bible, serve the church, and participate in mission.</p>
<p>Our youth programme combines worship, Bible study, discussion, and social activities for teenagers and young adults across the UK Parish.</p>
<p>Contact the parish office to find out about youth events in your area.</p>
HTML;
    }

    private function womensFellowshipContent(): string
    {
        return <<<'HTML'
<p>Women's Fellowship supports spiritual growth, prayer, family life, fellowship, charity, and service among the women of our parish.</p>
<p>Meetings include devotional sharing, intercessory prayer, hymn singing, and practical outreach to families in need.</p>
<p>All women of the parish are warmly invited to participate. Contact the parish office for meeting details.</p>
HTML;
    }

    private function choirContent(): string
    {
        return <<<'HTML'
<p>The parish choir leads the congregation in worship through songs, liturgy, and music, drawing on English hymnody and the musical traditions of the Saint Thomas Christian community.</p>
<p>If you enjoy singing and wish to serve in worship, we would love to hear from you. Rehearsals are arranged ahead of monthly worship services.</p>
HTML;
    }

    private function prayerGroupsContent(): string
    {
        return <<<'HTML'
<p>Prayer Groups help families across the UK stay connected through prayer, Bible study, and fellowship — both in local gatherings and through weekly online meetings.</p>
<p>In a parish spanning five cities, prayer is the glue that binds us together. Join a group near you or connect online.</p>
<p>Contact the parish office for meeting times and links.</p>
HTML;
    }

    private function eventsContent(): string
    {
        return <<<'HTML'
<h2>Parish Events</h2>
<p>Stay up to date with fellowship gatherings, special services, prayer meetings, and community events across our UK Parish. Upcoming events are listed below.</p>
<p>For enquiries about any event, please <a href="/contact">contact us</a>.</p>
HTML;
    }

    private function newsContent(): string
    {
        return <<<'HTML'
<h2>News & Announcements</h2>
<p>Latest news, announcements, and updates from the St. Thomas Evangelical Church of India – UK Parish.</p>
HTML;
    }

    private function galleryContent(): string
    {
        return <<<'HTML'
<h2>Photo Gallery</h2>
<p>Browse photos from worship services, fellowship events, and parish life across our five UK locations.</p>
HTML;
    }

    private function resourcesContent(): string
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

    private function liturgyContent(): string
    {
        return <<<'HTML'
<h2>Liturgical Resources</h2>
<p>Order of service templates, liturgical texts, and worship resources used in STECI UK Parish services. Documents are available for download below.</p>
<p>Our worship follows the evangelical Episcopal tradition, incorporating Scripture, hymns, prayers, sermon, and Holy Communion.</p>
HTML;
    }

    private function lectionaryContent(): string
    {
        return <<<'HTML'
<h2>Lectionary</h2>
<p>Scripture readings appointed for worship services according to the church lectionary. Download the current lectionary schedule below.</p>
<p>Readings are selected to guide the congregation through the breadth of Scripture across the liturgical year.</p>
HTML;
    }

    private function safeguardingContent(): string
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

    private function contactContent(): string
    {
        return <<<'HTML'
<h2>Get in Touch</h2>
<p>We would love to hear from you. Whether you have a question about worship times, want pastoral support, or are interested in joining our parish, please reach out using the contact form below or the details listed.</p>

<h3>Parish Office</h3>
<p><strong>Email:</strong> <a href="mailto:admin@steciuk.org">admin@steciuk.org</a><br>
<strong>Phone:</strong> 07578 189530<br>
<strong>Charity No:</strong> 1143030<br>
<strong>Address:</strong> United Kingdom</p>

<h3>Service Locations</h3>
<p>Manchester · Leicester · Dartford · Sunderland · Bristol</p>
HTML;
    }

    private function prayerRequestContent(): string
    {
        return <<<'HTML'
<h2>Submit a Prayer Request</h2>
<p>Our prayer team would be honoured to pray with you. Share your request using the form below and know that you are held in the prayers of our parish family.</p>
<p>All prayer requests are treated with confidentiality. A member of our prayer ministry team will respond where appropriate.</p>
HTML;
    }

    private function newMemberContent(): string
    {
        return <<<'HTML'
<h2>Register as a New Member</h2>
<p>Welcome! If you would like to join the St. Thomas Evangelical Church of India – UK Parish, please complete the registration form below. A member of our leadership team will be in touch to welcome you and help you connect with worship and fellowship in your area.</p>
<p>Membership is open to those who profess faith in Jesus Christ and wish to participate in the life and mission of our parish.</p>
HTML;
    }

    private function privacyPolicyContent(): string
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

    private function termsOfUseContent(): string
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
