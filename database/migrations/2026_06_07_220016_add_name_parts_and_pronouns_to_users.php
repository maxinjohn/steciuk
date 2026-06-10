<?php

use App\Support\UserName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('pronouns', 50)->nullable()->after('last_name');
        });

        foreach (DB::table('users')->orderBy('id')->get(['id', 'name']) as $user) {
            $parts = UserName::split((string) ($user->name ?? ''));

            DB::table('users')->where('id', $user->id)->update([
                'first_name' => $parts['first_name'] !== '' ? $parts['first_name'] : (string) ($user->name ?? ''),
                'last_name' => $parts['last_name'],
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'pronouns']);
        });
    }
};
