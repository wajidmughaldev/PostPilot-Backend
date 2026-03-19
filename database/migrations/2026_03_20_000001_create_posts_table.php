<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('posts')) {
            return;
        }

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->string('status', 32)->index();
            $table->string('publish_mode', 32);
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->string('timezone', 64)->default('UTC');
            $table->json('platforms');
            $table->json('account_ids');
            $table->json('account_labels');
            $table->string('media_name')->nullable();
            $table->string('media_type')->nullable();
            $table->string('visibility', 32)->default('public');
            $table->string('age_min', 16)->default('18');
            $table->string('age_max', 16)->default('65+');
            $table->json('locations');
            $table->json('interests');
            $table->boolean('lookalike_audience')->default(false);
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
