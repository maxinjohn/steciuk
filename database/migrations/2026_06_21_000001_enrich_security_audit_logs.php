<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('security_audit_logs', function (Blueprint $table) {
            $table->string('actor_name')->nullable()->after('user_id');
            $table->string('actor_email')->nullable()->after('actor_name');
            $table->string('actor_role')->nullable()->after('actor_email');
            $table->string('subject_type')->nullable()->after('action');
            $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');
            $table->string('subject_label')->nullable()->after('subject_id');
            $table->text('summary')->nullable()->after('subject_label');

            $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::table('security_audit_logs', function (Blueprint $table) {
            $table->dropIndex(['subject_type', 'subject_id']);
            $table->dropColumn([
                'actor_name',
                'actor_email',
                'actor_role',
                'subject_type',
                'subject_id',
                'subject_label',
                'summary',
            ]);
        });
    }
};
