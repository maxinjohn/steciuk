<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'title' => 'Manchester Worship Service',
                'location' => 'Manchester',
                'address' => 'St. Thomas Church Centre, 42 Wilmslow Road, Manchester, M14 5TP',
                'service_day' => 'Monthly',
                'service_time' => 'Please contact parish office for current schedule',
                'frequency' => 'Monthly worship service',
                'language' => 'English & Malayalam',
                'description' => 'The Manchester congregation gathers for monthly worship, Holy Communion, fellowship, and prayer. Families from across Greater Manchester and the North West are warmly welcome.',
                'map_link' => 'https://maps.google.com/?q=Manchester+UK',
                'online_stream_link' => 'https://youtube.com/@steciuk/live',
                'contact_person' => 'Parish Office',
                'contact_email' => 'admin@steciuk.org',
                'contact_phone' => '07578 189530',
                'sort_order' => 1,
                'status' => 'active',
            ],
            [
                'title' => 'Leicester Worship Service',
                'location' => 'Leicester',
                'address' => 'Community Hall, 18 London Road, Leicester, LE2 0RA',
                'service_day' => 'Monthly',
                'service_time' => 'Please contact parish office for current schedule',
                'frequency' => 'Monthly worship service',
                'language' => 'English & Malayalam',
                'description' => 'Our Leicester fellowship meets for monthly worship and fellowship, serving families across Leicestershire and the East Midlands.',
                'map_link' => 'https://maps.google.com/?q=Leicester+UK',
                'online_stream_link' => 'https://youtube.com/@steciuk/live',
                'contact_person' => 'Parish Office',
                'contact_email' => 'admin@steciuk.org',
                'contact_phone' => '07578 189530',
                'sort_order' => 2,
                'status' => 'active',
            ],
            [
                'title' => 'Dartford Worship Service',
                'location' => 'Dartford',
                'address' => 'St. Thomas Fellowship Hall, 7 High Street, Dartford, DA1 1DE',
                'service_day' => 'Monthly',
                'service_time' => 'Please contact parish office for current schedule',
                'frequency' => 'Monthly worship service',
                'language' => 'English & Malayalam',
                'description' => 'The Dartford congregation serves families across Kent and South East London with monthly worship, prayer, and fellowship.',
                'map_link' => 'https://maps.google.com/?q=Dartford+UK',
                'online_stream_link' => 'https://youtube.com/@steciuk/live',
                'contact_person' => 'Parish Office',
                'contact_email' => 'admin@steciuk.org',
                'contact_phone' => '07578 189530',
                'sort_order' => 3,
                'status' => 'active',
            ],
            [
                'title' => 'Sunderland Worship Service',
                'location' => 'Sunderland',
                'address' => 'Methodist Community Centre, 22 Chester Road, Sunderland, SR2 7QA',
                'service_day' => 'Monthly',
                'service_time' => 'Please contact parish office for current schedule',
                'frequency' => 'Monthly worship service',
                'language' => 'English & Malayalam',
                'description' => 'Our Sunderland fellowship gathers monthly for worship and pastoral fellowship, welcoming families across the North East.',
                'map_link' => 'https://maps.google.com/?q=Sunderland+UK',
                'online_stream_link' => 'https://youtube.com/@steciuk/live',
                'contact_person' => 'Parish Office',
                'contact_email' => 'admin@steciuk.org',
                'contact_phone' => '07578 189530',
                'sort_order' => 4,
                'status' => 'active',
            ],
            [
                'title' => 'Bristol Worship Service',
                'location' => 'Bristol',
                'address' => 'Community Church Hall, 55 Gloucester Road, Bristol, BS7 8AD',
                'service_day' => 'Monthly',
                'service_time' => 'Please contact parish office for current schedule',
                'frequency' => 'Monthly worship service',
                'language' => 'English & Malayalam',
                'description' => 'The Bristol congregation meets monthly for worship, Holy Communion, and fellowship, serving families across the South West.',
                'map_link' => 'https://maps.google.com/?q=Bristol+UK',
                'online_stream_link' => 'https://youtube.com/@steciuk/live',
                'contact_person' => 'Parish Office',
                'contact_email' => 'admin@steciuk.org',
                'contact_phone' => '07578 189530',
                'sort_order' => 5,
                'status' => 'active',
            ],
        ];

        foreach ($services as $service) {
            Service::query()->updateOrCreate(
                ['location' => $service['location']],
                $service,
            );
        }
    }
}
