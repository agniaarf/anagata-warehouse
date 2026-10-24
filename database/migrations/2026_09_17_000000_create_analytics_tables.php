<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_key')->unique();
            $table->uuid('visitor_key')->index();
            $table->timestamp('started_at')->useCurrent()->index();
            $table->timestamp('last_activity_at')->useCurrent()->index();
            $table->string('landing_path', 255)->default('/');
            $table->string('referrer_domain', 253)->nullable();
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->string('device_type', 12)->default('unknown');
            $table->timestamps();

            $table->index(['visitor_key', 'started_at']);
        });

        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analytics_session_id')->constrained('analytics_sessions')->cascadeOnDelete();
            $table->uuid('event_key')->unique();
            $table->string('event_type', 20);
            $table->string('target', 60);
            $table->timestamp('created_at')->index();

            $table->index(['event_type', 'created_at']);
            $table->index(['analytics_session_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('analytics_sessions');
    }
};
