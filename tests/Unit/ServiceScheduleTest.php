<?php

namespace Tests\Unit;

use App\Enums\ServiceScheduleType;
use App\Support\ServiceSchedule;
use Tests\TestCase;

class ServiceScheduleTest extends TestCase
{
    public function test_legacy_monthly_schedule_maps_to_flexible_recurring_pattern(): void
    {
        $data = ServiceSchedule::legacyToScheduleData(
            'Monthly',
            'Contact admin@steciuk.org for the current monthly date, time, and venue',
            'Monthly worship service',
        );

        $this->assertSame('monthly', $data['interval_unit']);
        $this->assertSame('flexible', $data['monthly_mode']);
        $this->assertStringContainsString('admin@steciuk.org', $data['note']);
    }

    public function test_recurring_weekly_schedule_syncs_legacy_display_fields(): void
    {
        $legacy = ServiceSchedule::syncLegacyFields(ServiceScheduleType::Recurring, [
            'interval_unit' => 'weekly',
            'interval' => 1,
            'weekday' => 'sunday',
            'monthly_mode' => 'flexible',
            'start_time' => '10:30',
            'end_time' => '12:00',
            'note' => '',
        ]);

        $this->assertSame('Weekly on Sunday', $legacy['service_day']);
        $this->assertSame('10:30 AM – 12:00 PM', $legacy['service_time']);
        $this->assertSame('Weekly worship service', $legacy['frequency']);
    }

    public function test_multiple_dates_build_public_preview(): void
    {
        $legacy = ServiceSchedule::syncLegacyFields(ServiceScheduleType::Multiple, [
            'occurrences' => [
                ['date' => '2026-07-12', 'start_time' => '10:30', 'end_time' => null, 'note' => ''],
                ['date' => '2026-08-09', 'start_time' => '11:00', 'end_time' => null, 'note' => ''],
            ],
        ]);

        $this->assertSame('2 scheduled dates', $legacy['service_day']);
        $this->assertStringContainsString('12 Jul 2026', $legacy['service_time']);
        $this->assertStringContainsString('9 Aug 2026', $legacy['service_time']);
    }

    public function test_one_time_schedule_uses_selected_date(): void
    {
        $legacy = ServiceSchedule::syncLegacyFields(ServiceScheduleType::OneTime, [
            'occurrences' => [
                ['date' => '2026-12-25', 'start_time' => '09:30', 'end_time' => null, 'note' => 'Christmas worship'],
            ],
        ]);

        $this->assertStringContainsString('25 December 2026', $legacy['service_day']);
        $this->assertSame('9:30 AM', $legacy['service_time']);
        $this->assertSame('One-off worship service', $legacy['frequency']);
    }

    public function test_preview_from_form_shows_helpful_prompt_when_incomplete(): void
    {
        $this->assertStringContainsString(
            'Pick a service date',
            ServiceSchedule::previewFromForm(ServiceScheduleType::OneTime->value, ['occurrences' => [ServiceSchedule::blankOccurrence()]]),
        );
    }

    public function test_preview_from_form_shows_public_summary_when_complete(): void
    {
        $summary = ServiceSchedule::previewFromForm(ServiceScheduleType::Recurring->value, [
            'interval_unit' => 'weekly',
            'interval' => 1,
            'weekday' => 'sunday',
            'monthly_mode' => 'flexible',
            'start_time' => '10:30',
            'end_time' => null,
            'note' => '',
        ]);

        $this->assertStringContainsString('Sunday', $summary);
        $this->assertStringContainsString('10:30 AM', $summary);
    }

    public function test_public_schedule_blocks_lists_multiple_dates(): void
    {
        $blocks = ServiceSchedule::publicScheduleBlocks(ServiceScheduleType::Multiple, [
            'occurrences' => [
                ['date' => '2026-07-12', 'start_time' => '10:30', 'end_time' => null, 'note' => ''],
                ['date' => '2026-08-09', 'start_time' => '11:00', 'end_time' => null, 'note' => ''],
            ],
        ]);

        $this->assertSame('2 scheduled dates', $blocks['headline']);
        $this->assertCount(2, $blocks['date_lines']);
        $this->assertStringContainsString('12 Jul 2026', $blocks['date_lines'][0]);
        $this->assertStringContainsString('9 Aug 2026', $blocks['date_lines'][1]);
    }
}
