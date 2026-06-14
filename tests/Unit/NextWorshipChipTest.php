<?php

namespace Tests\Unit;

use App\Enums\ServiceScheduleType;
use App\Models\Service;
use App\Support\NextWorshipChip;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NextWorshipChipTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_next_weekly_worship_occurrence(): void
    {
        Service::query()->create([
            'title' => 'Manchester Worship',
            'location' => 'Manchester',
            'status' => 'active',
            'service_day' => 'Sunday',
            'service_time' => '10:30 AM',
            'frequency' => 'Weekly worship service',
            'schedule_type' => ServiceScheduleType::Recurring->value,
            'schedule_data' => [
                'interval_unit' => 'weekly',
                'interval' => 1,
                'weekday' => 'sunday',
                'start_time' => '10:30',
                'end_time' => '12:00',
                'monthly_mode' => 'flexible',
                'note' => '',
            ],
            'sort_order' => 1,
            'language' => 'English',
        ]);

        NextWorshipChip::forget();
        $chip = NextWorshipChip::resolve();

        $this->assertNotNull($chip);
        $this->assertStringContainsString('Manchester', $chip['detail']);
        $this->assertSame(url('/service-times'), $chip['url']);
    }

    public function test_it_marks_live_worship_and_links_to_online_worship_when_streaming(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-14 10:45:00')); // Sunday

        Service::query()->create([
            'title' => 'Online Worship',
            'location' => 'Live stream',
            'status' => 'active',
            'service_day' => 'Sunday',
            'service_time' => '10:30 AM',
            'frequency' => 'Weekly worship service',
            'schedule_type' => ServiceScheduleType::Recurring->value,
            'schedule_data' => [
                'interval_unit' => 'weekly',
                'interval' => 1,
                'weekday' => 'sunday',
                'start_time' => '10:30',
                'end_time' => '12:00',
                'monthly_mode' => 'flexible',
                'note' => '',
            ],
            'online_stream_link' => 'https://youtube.com/live/example',
            'sort_order' => 1,
            'language' => 'English',
        ]);

        NextWorshipChip::forget();
        $chip = NextWorshipChip::resolve();

        $this->assertNotNull($chip);
        $this->assertTrue($chip['is_live']);
        $this->assertSame('Worship now', $chip['label']);
        $this->assertSame(url('/online-worship'), $chip['url']);

        Carbon::setTestNow();
    }
}
