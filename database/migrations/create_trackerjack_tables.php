<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trackerjack_visits', function (Blueprint $table) {
            $table->id();
            $table->char('visitor_id', 40)->index();
            $table->string('url');
            $table->string('referrer')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('trackerjack_events', function (Blueprint $table) {
            $table->id();
            $table->char('visitor_id', 40)->index();
            $table->string('event_name');
            $table->json('payload')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('email')->nullable();
            $table->index('event_name');
            $table->index('user_id');
            $table->index('email');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trackerjack_events');
        Schema::dropIfExists('trackerjack_visits');
    }
};
