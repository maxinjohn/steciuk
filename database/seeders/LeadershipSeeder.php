<?php

namespace Database\Seeders;

use App\Models\LeadershipMember;
use Illuminate\Database\Seeder;

class LeadershipSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'name' => 'Rev. [Name Placeholder]',
                'role' => 'UK Parish Vicar',
                'bio' => 'Placeholder — please update with the current UK Parish Vicar\'s name, photograph, and biography. The Vicar provides spiritual leadership, presides at worship, and offers pastoral care to families across the parish.',
                'email' => 'admin@steciuk.org',
                'phone' => '07578 189530',
                'sort_order' => 1,
                'is_visible' => true,
            ],
            [
                'name' => '[Name Placeholder]',
                'role' => 'Parish Secretary',
                'bio' => 'Placeholder — please update with the Parish Secretary\'s details. The Secretary supports parish administration, communications, and coordination across our five UK locations.',
                'email' => 'admin@steciuk.org',
                'sort_order' => 2,
                'is_visible' => true,
            ],
            [
                'name' => '[Name Placeholder]',
                'role' => 'Treasurer',
                'bio' => 'Placeholder — please update with the Treasurer\'s details. The Treasurer oversees parish finances and reporting in accordance with charity regulations (Registered Charity No. 1143030).',
                'email' => 'admin@steciuk.org',
                'sort_order' => 3,
                'is_visible' => true,
            ],
            [
                'name' => '[Name Placeholder]',
                'role' => 'Trustee',
                'bio' => 'Placeholder — please update with trustee details. Trustees provide governance and oversight for the UK Parish in accordance with the church\'s constitution and charitable obligations.',
                'email' => 'admin@steciuk.org',
                'sort_order' => 4,
                'is_visible' => true,
            ],
        ];

        foreach ($members as $member) {
            LeadershipMember::query()->updateOrCreate(
                ['role' => $member['role']],
                $member,
            );
        }
    }
}
