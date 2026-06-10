<?php

namespace App\Filament\Resources\Services\Concerns;

use App\Enums\ServiceScheduleType;
use App\Support\ServiceSchedule;

trait ManagesServiceRecord
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $type = ServiceScheduleType::tryFrom((string) ($data['schedule_type'] ?? ''))
            ?? ServiceScheduleType::Recurring;

        $data['schedule_type'] = $type->value;
        $data['schedule_data'] = ServiceSchedule::normalizeData(
            $type,
            is_array($data['schedule_data'] ?? null)
                ? $data['schedule_data']
                : ServiceSchedule::legacyToScheduleData(
                    (string) ($data['service_day'] ?? ''),
                    (string) ($data['service_time'] ?? ''),
                    (string) ($data['frequency'] ?? ''),
                ),
        );

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->normalizeScheduleFormData($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->normalizeScheduleFormData($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeScheduleFormData(array $data): array
    {
        $type = ServiceScheduleType::tryFrom((string) ($data['schedule_type'] ?? ''))
            ?? ServiceScheduleType::Recurring;

        $data['schedule_type'] = $type->value;
        $scheduleData = is_array($data['schedule_data'] ?? null) ? $data['schedule_data'] : [];
        $data['schedule_data'] = ServiceSchedule::normalizeData($type, $scheduleData);

        if ($type === ServiceScheduleType::OneTime) {
            $occurrence = $scheduleData['occurrences'][0] ?? $scheduleData;
            $data['schedule_data'] = ServiceSchedule::normalizeData($type, [
                'occurrences' => [is_array($occurrence) ? $occurrence : ServiceSchedule::blankOccurrence()],
            ]);
        }

        unset($data['address_lookup_pick'], $data['address_lookup_options']);

        return $data;
    }
}
