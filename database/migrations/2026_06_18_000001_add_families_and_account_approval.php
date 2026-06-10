<?php

use App\Enums\AccountStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('preferred_worship_location')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('account_status')->default(AccountStatus::Approved->value)->after('role');
            $table->foreignId('family_id')->nullable()->after('account_status')->constrained()->nullOnDelete();
            $table->boolean('is_family_admin')->default(false)->after('family_id');
            $table->string('family_relationship')->nullable()->after('is_family_admin');
            $table->timestamp('approved_at')->nullable()->after('family_relationship');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn([
                'account_status',
                'family_id',
                'is_family_admin',
                'family_relationship',
                'approved_at',
            ]);
        });

        Schema::dropIfExists('families');
    }
};
