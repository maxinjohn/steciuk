<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('address_line_1')->nullable()->after('location');
            $table->string('address_line_2')->nullable()->after('address_line_1');
            $table->string('city')->nullable()->after('address_line_2');
            $table->string('county')->nullable()->after('city');
            $table->string('postcode', 12)->nullable()->after('county');
        });

        DB::table('services')
            ->select(['id', 'address'])
            ->orderBy('id')
            ->get()
            ->each(function (object $service): void {
                $address = trim((string) ($service->address ?? ''));

                if ($address === '') {
                    return;
                }

                DB::table('services')
                    ->where('id', $service->id)
                    ->update(['address_line_1' => $address]);
            });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'address_line_1',
                'address_line_2',
                'city',
                'county',
                'postcode',
            ]);
        });
    }
};
