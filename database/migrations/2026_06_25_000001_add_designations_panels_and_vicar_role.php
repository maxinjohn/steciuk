<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('designations', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('panels', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('designation_id')
                ->nullable()
                ->after('role')
                ->constrained('designations')
                ->nullOnDelete();
        });

        Schema::create('panel_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('panel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['panel_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panel_user');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('designation_id');
        });

        Schema::dropIfExists('panels');
        Schema::dropIfExists('designations');
    }
};
