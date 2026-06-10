<?php

namespace App\Models;

use App\Enums\ServiceScheduleType;
use App\Services\SiteCache;
use App\Support\ServiceMapLink;
use App\Support\ServiceSchedule;
use App\Support\UkAddressFormatter;
use App\Support\UkPostcode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'location',
        'address',
        'address_line_1',
        'address_line_2',
        'city',
        'county',
        'postcode',
        'service_day',
        'service_time',
        'frequency',
        'schedule_type',
        'schedule_data',
        'language',
        'description',
        'map_link',
        'latitude',
        'longitude',
        'online_stream_link',
        'contact_person',
        'contact_email',
        'contact_phone',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'schedule_data' => 'array',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $service): void {
            if (filled($service->postcode)) {
                $service->postcode = UkPostcode::normalize($service->postcode)
                    ?? strtoupper(trim((string) $service->postcode));
            }

            if (Schema::hasColumn('services', 'schedule_type')) {
                $type = ServiceScheduleType::tryFrom((string) $service->schedule_type)
                    ?? ServiceScheduleType::Recurring;

                $service->schedule_type = $type->value;

                if ($service->isDirty('schedule_data') || $service->isDirty('schedule_type')) {
                    $service->schedule_data = ServiceSchedule::normalizeData($type, $service->schedule_data ?? []);
                } elseif (blank($service->schedule_data) || $service->isDirty(['service_day', 'service_time', 'frequency'])) {
                    $service->schedule_data = ServiceSchedule::normalizeData(
                        $type,
                        ServiceSchedule::legacyToScheduleData(
                            (string) $service->service_day,
                            (string) $service->service_time,
                            (string) $service->frequency,
                        ),
                    );
                } else {
                    $service->schedule_data = ServiceSchedule::normalizeData($type, $service->schedule_data ?? []);
                }

                $legacy = ServiceSchedule::syncLegacyFields($type, $service->schedule_data);
                $service->service_day = $legacy['service_day'];
                $service->service_time = $legacy['service_time'];
                $service->frequency = $legacy['frequency'];
            }

            $formatted = $service->formattedAddress();

            if ($formatted !== '') {
                $service->address = $formatted;
            }

            if (Schema::hasColumn('services', 'latitude')) {
                ServiceMapLink::syncCoordinates($service);
                ServiceMapLink::syncMapLink($service);
            }
        });

        static::saved(fn () => SiteCache::forgetPublicContent());
        static::deleted(fn () => SiteCache::forgetPublicContent());
    }

    public function scheduleType(): ServiceScheduleType
    {
        return ServiceScheduleType::tryFrom((string) $this->schedule_type)
            ?? ServiceScheduleType::Recurring;
    }

    public function scheduleSummary(): string
    {
        return ServiceSchedule::summary($this->scheduleType(), $this->schedule_data);
    }

    /**
     * @return array{headline: string, details: list<string>, frequency: ?string, date_lines: list<string>}
     */
    public function publicScheduleBlocks(): array
    {
        return ServiceSchedule::publicScheduleBlocks(
            $this->scheduleType(),
            $this->schedule_data,
            (string) $this->service_day,
            (string) $this->service_time,
            (string) $this->frequency,
        );
    }

    public function formattedAddress(): string
    {
        return UkAddressFormatter::format(
            line1: $this->address_line_1 ?: $this->address,
            line2: $this->address_line_2,
            city: $this->city,
            county: $this->county,
            postcode: $this->postcode,
        );
    }

    public function mapEmbedUrl(): ?string
    {
        return ServiceMapLink::embedUrl($this->latitude, $this->longitude);
    }
}
