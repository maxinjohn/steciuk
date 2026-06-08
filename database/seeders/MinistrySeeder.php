<?php

namespace Database\Seeders;

use App\Models\Ministry;
use Illuminate\Database\Seeder;

class MinistrySeeder extends Seeder
{
    public function run(): void
    {
        $ministries = [
            [
                'name' => 'Sunday School',
                'slug' => 'sunday-school',
                'short_description' => 'Helping children grow in the knowledge of the Bible and Christian faith.',
                'description' => '<p>Sunday School helps children grow in the knowledge of the Bible and Christian faith through age-appropriate teaching, songs, activities, and fellowship. Our teachers are committed to nurturing young hearts in the love of Christ and the traditions of the Saint Thomas Christian heritage.</p><p>Classes are organised by age group and meet alongside parish worship services and fellowship gatherings across our UK locations. Parents are encouraged to participate and support this vital ministry of discipleship.</p>',
                'contact_person' => 'Sunday School Coordinator',
                'contact_email' => 'admin@steciuk.org',
                'meeting_time' => 'During monthly worship services — contact parish office for details',
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'name' => 'Youth Fellowship',
                'slug' => 'youth-fellowship',
                'short_description' => 'Encouraging young people to grow in Christ, build friendships, and serve the church.',
                'description' => '<p>Youth Fellowship encourages young people to grow in Christ, build friendships, study the Bible, serve the church, and participate in mission. Our youth gatherings combine worship, Bible study, discussion, and social activities designed for teenagers and young adults.</p><p>We aim to equip the next generation to live out their faith with courage and compassion in contemporary British society, rooted in the evangelical Episcopal witness of STECI.</p>',
                'contact_person' => 'Youth Fellowship Leader',
                'contact_email' => 'admin@steciuk.org',
                'meeting_time' => 'Monthly — contact parish office for schedule',
                'sort_order' => 2,
                'status' => 'active',
            ],
            [
                'name' => "Women's Fellowship",
                'slug' => 'womens-fellowship',
                'short_description' => 'Supporting spiritual growth, prayer, family life, fellowship, charity, and service.',
                'description' => "<p>Women's Fellowship supports spiritual growth, prayer, family life, fellowship, charity, and service. This ministry brings together women across our UK parish locations for Bible study, intercessory prayer, mutual encouragement, and practical acts of service.</p><p>Meetings often include devotional sharing, hymn singing, and outreach to families in need within our community and beyond.</p>",
                'contact_person' => "Women's Fellowship Secretary",
                'contact_email' => 'admin@steciuk.org',
                'meeting_time' => 'Monthly — contact parish office for schedule',
                'sort_order' => 3,
                'status' => 'active',
            ],
            [
                'name' => 'Choir',
                'slug' => 'choir',
                'short_description' => 'Leading the congregation in worship through songs, liturgy, and music.',
                'description' => '<p>The parish choir leads the congregation in worship through songs, liturgy, and music. Drawing on both English hymnody and the rich musical traditions of the Saint Thomas Christian community, our choir enhances the beauty and reverence of our worship services.</p><p>New members who love to sing and serve are always welcome. Rehearsals are arranged ahead of monthly worship gatherings.</p>',
                'contact_person' => 'Choir Director',
                'contact_email' => 'admin@steciuk.org',
                'meeting_time' => 'Before worship services — contact parish office',
                'sort_order' => 4,
                'status' => 'active',
            ],
            [
                'name' => 'Prayer Groups',
                'slug' => 'prayer-groups',
                'short_description' => 'Helping families across the UK stay connected through prayer, Bible study, and fellowship.',
                'description' => '<p>Prayer Groups help families across the UK stay connected through prayer, Bible study, and fellowship. In a dispersed parish spanning five locations, regular prayer gatherings—both in person and online—keep our community united in intercession and spiritual support.</p><p>Groups meet locally and also connect for parish-wide days of prayer and fasting throughout the year.</p>',
                'contact_person' => 'Prayer Group Coordinator',
                'contact_email' => 'admin@steciuk.org',
                'meeting_time' => 'Weekly online and monthly local — contact parish office',
                'sort_order' => 5,
                'status' => 'active',
            ],
            [
                'name' => 'Evangelism & Mission',
                'slug' => 'evangelism-mission',
                'short_description' => 'Sharing the Gospel and supporting STECI\'s missionary calling in the UK and beyond.',
                'description' => '<p>Evangelism &amp; Mission is at the heart of STECI\'s identity as a missionary church. The UK Parish participates in local outreach, supports mission partners in India and elsewhere, and encourages every member to bear witness to Jesus Christ in their daily life.</p><p>We organise evangelistic events, literature distribution, and support for missionary endeavours connected with the wider STECI fellowship headquartered in Thiruvalla, Kerala.</p>',
                'contact_person' => 'Mission Coordinator',
                'contact_email' => 'admin@steciuk.org',
                'meeting_time' => 'As announced — contact parish office',
                'sort_order' => 6,
                'status' => 'active',
            ],
            [
                'name' => 'Pastoral Care',
                'slug' => 'pastoral-care',
                'short_description' => 'Providing spiritual support, visitation, and care for parish families.',
                'description' => '<p>Pastoral Care provides spiritual support, visitation, and care for parish families across the United Kingdom. Our vicar and lay leaders offer prayer, counsel, hospital and home visits, and support during times of illness, bereavement, and life transition.</p><p>If you or your family need pastoral assistance, please contact the parish office. All requests are treated with confidentiality and compassion.</p>',
                'contact_person' => 'Parish Office',
                'contact_email' => 'admin@steciuk.org',
                'meeting_time' => 'Available by appointment',
                'sort_order' => 7,
                'status' => 'active',
            ],
            [
                'name' => 'Community Fellowship',
                'slug' => 'community-fellowship',
                'short_description' => 'Building friendships and community among STECI UK families between worship gatherings.',
                'description' => '<p>Community Fellowship builds friendships and a sense of belonging among the approximately ninety families that make up our UK Parish. Between monthly worship services, local groups organise shared meals, picnics, cultural celebrations, and social gatherings that strengthen bonds across generations.</p><p>Whether you are new to the parish or have been part of the community for many years, you are warmly invited to participate in fellowship activities in your area.</p>',
                'contact_person' => 'Fellowship Coordinator',
                'contact_email' => 'admin@steciuk.org',
                'meeting_time' => 'Various local events — see news and events pages',
                'sort_order' => 8,
                'status' => 'active',
            ],
        ];

        foreach ($ministries as $ministry) {
            Ministry::query()->updateOrCreate(
                ['slug' => $ministry['slug']],
                $ministry,
            );
        }
    }
}
