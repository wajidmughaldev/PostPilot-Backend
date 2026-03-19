<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('posts')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table) {
            if (! Schema::hasColumn('posts', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('posts', 'organization_id')) {
                $table->foreignId('organization_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('posts', 'title')) {
                $table->string('title')->default('Untitled post')->after('organization_id');
            }

            if (! Schema::hasColumn('posts', 'content')) {
                $table->longText('content')->nullable()->after('title');
            }

            if (! Schema::hasColumn('posts', 'status')) {
                $table->string('status', 32)->default('draft')->after('content');
            }

            if (! Schema::hasColumn('posts', 'publish_mode')) {
                $table->string('publish_mode', 32)->default('draft')->after('status');
            }

            if (! Schema::hasColumn('posts', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('publish_mode');
            }

            if (! Schema::hasColumn('posts', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('scheduled_at');
            }

            if (! Schema::hasColumn('posts', 'timezone')) {
                $table->string('timezone', 64)->default('UTC')->after('published_at');
            }

            if (! Schema::hasColumn('posts', 'platforms')) {
                $table->json('platforms')->nullable()->after('timezone');
            }

            if (! Schema::hasColumn('posts', 'account_ids')) {
                $table->json('account_ids')->nullable()->after('platforms');
            }

            if (! Schema::hasColumn('posts', 'account_labels')) {
                $table->json('account_labels')->nullable()->after('account_ids');
            }

            if (! Schema::hasColumn('posts', 'media_name')) {
                $table->string('media_name')->nullable()->after('account_labels');
            }

            if (! Schema::hasColumn('posts', 'media_type')) {
                $table->string('media_type')->nullable()->after('media_name');
            }

            if (! Schema::hasColumn('posts', 'visibility')) {
                $table->string('visibility', 32)->default('public')->after('media_type');
            }

            if (! Schema::hasColumn('posts', 'age_min')) {
                $table->string('age_min', 16)->default('18')->after('visibility');
            }

            if (! Schema::hasColumn('posts', 'age_max')) {
                $table->string('age_max', 16)->default('65+')->after('age_min');
            }

            if (! Schema::hasColumn('posts', 'locations')) {
                $table->json('locations')->nullable()->after('age_max');
            }

            if (! Schema::hasColumn('posts', 'interests')) {
                $table->json('interests')->nullable()->after('locations');
            }

            if (! Schema::hasColumn('posts', 'lookalike_audience')) {
                $table->boolean('lookalike_audience')->default(false)->after('interests');
            }

            if (! Schema::hasColumn('posts', 'failure_reason')) {
                $table->text('failure_reason')->nullable()->after('lookalike_audience');
            }
        });
    }

    public function down(): void
    {
        // Intentionally left empty because this migration only syncs older schemas.
    }
};
