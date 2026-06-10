<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('privacy_policy_accepted_at')->nullable()->after('approved_by');
            $table->string('privacy_policy_version')->nullable()->after('privacy_policy_accepted_at');
            $table->timestamp('terms_accepted_at')->nullable()->after('privacy_policy_version');
            $table->timestamp('household_data_consent_at')->nullable()->after('terms_accepted_at');
            $table->boolean('marketing_consent')->default(false)->after('household_data_consent_at');
            $table->timestamp('marketing_consent_at')->nullable()->after('marketing_consent');
            $table->timestamp('erasure_requested_at')->nullable()->after('marketing_consent_at');
            $table->timestamp('anonymized_at')->nullable()->after('erasure_requested_at');
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->timestamp('accuracy_confirmed_at')->nullable()->after('member_note');
            $table->string('processing_basis')->default('legal_obligation')->after('accuracy_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn(['accuracy_confirmed_at', 'processing_basis']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'privacy_policy_accepted_at',
                'privacy_policy_version',
                'terms_accepted_at',
                'household_data_consent_at',
                'marketing_consent',
                'marketing_consent_at',
                'erasure_requested_at',
                'anonymized_at',
            ]);
        });
    }
};
