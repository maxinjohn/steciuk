<?php

use App\Enums\ServiceScheduleType;
use App\Models\Service;
use App\Support\ServiceSchedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('services', 'schedule_type')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('schedule_type')->default(ServiceScheduleType::Recurring->value)->after('language');
                $table->json('schedule_data')->nullable()->after('schedule_type');
            });
        }

        if (! Schema::hasColumn('services', 'latitude')) {
            Schema::table('services', function (Blueprint $table) {
                $table->decimal('latitude', 10, 7)->nullable()->after('map_link');
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            });
        }

        if (! Schema::hasTable('services')) {
            return;
        }

        Service::query()->orderBy('id')->each(function (Service $service): void {
            $service->forceFill([
                'schedule_type' => ServiceScheduleType::Recurring->value,
                'schedule_data' => ServiceSchedule::legacyToScheduleData(
                    (string) $service->service_day,
                    (string) $service->service_time,
                    (string) $service->frequency,
                ),
            ])->saveQuietly();
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'schedule_type',
                'schedule_data',
                'latitude',
                'longitude',
            ]);
        });
    }
};
