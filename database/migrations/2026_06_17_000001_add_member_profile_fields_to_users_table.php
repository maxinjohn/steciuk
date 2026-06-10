<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->after('role');
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->string('address_line_1')->nullable()->after('date_of_birth');
            $table->string('address_line_2')->nullable()->after('address_line_1');
            $table->string('city')->nullable()->after('address_line_2');
            $table->string('county')->nullable()->after('city');
            $table->string('postcode', 12)->nullable()->after('county');
            $table->string('preferred_worship_location')->nullable()->after('postcode');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'date_of_birth',
                'address_line_1',
                'address_line_2',
                'city',
                'county',
                'postcode',
                'preferred_worship_location',
            ]);
        });
    }
};
