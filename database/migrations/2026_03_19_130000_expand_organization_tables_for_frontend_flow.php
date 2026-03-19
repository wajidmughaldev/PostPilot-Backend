<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table): void {
            if (! Schema::hasColumn('organizations', 'timezone')) {
                $table->string('timezone')->default('UTC')->after('name');
            }

            if (! Schema::hasColumn('organizations', 'contact_person_name')) {
                $table->string('contact_person_name')->nullable()->after('status');
            }

            if (! Schema::hasColumn('organizations', 'contact_person_email')) {
                $table->string('contact_person_email')->nullable()->after('contact_person_name');
            }

            if (! Schema::hasColumn('organizations', 'contact_person_phone')) {
                $table->string('contact_person_phone')->nullable()->after('contact_person_email');
            }

            if (! Schema::hasColumn('organizations', 'website')) {
                $table->string('website')->nullable()->after('contact_person_phone');
            }

            if (! Schema::hasColumn('organizations', 'bio')) {
                $table->text('bio')->nullable()->after('website');
            }

            if (! Schema::hasColumn('organizations', 'location')) {
                $table->string('location')->nullable()->after('bio');
            }

            if (! Schema::hasColumn('organizations', 'industry')) {
                $table->string('industry')->nullable()->after('location');
            }

            if (! Schema::hasColumn('organizations', 'organization_size')) {
                $table->string('organization_size', 32)->nullable()->after('industry');
            }
        });

        Schema::table('organization_access_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('organization_access_requests', 'contact_person_name')) {
                $table->string('contact_person_name')->nullable()->after('requested_organization_name');
            }

            if (! Schema::hasColumn('organization_access_requests', 'timezone')) {
                $table->string('timezone')->nullable()->after('contact_phone');
            }

            if (! Schema::hasColumn('organization_access_requests', 'bio')) {
                $table->text('bio')->nullable()->after('website_url');
            }

            if (! Schema::hasColumn('organization_access_requests', 'location')) {
                $table->string('location')->nullable()->after('bio');
            }

            if (! Schema::hasColumn('organization_access_requests', 'industry')) {
                $table->string('industry')->nullable()->after('location');
            }

            if (! Schema::hasColumn('organization_access_requests', 'organization_size')) {
                $table->string('organization_size', 32)->nullable()->after('industry');
            }
        });
    }

    public function down(): void
    {
        Schema::table('organization_access_requests', function (Blueprint $table): void {
            $columns = array_filter([
                Schema::hasColumn('organization_access_requests', 'contact_person_name') ? 'contact_person_name' : null,
                Schema::hasColumn('organization_access_requests', 'timezone') ? 'timezone' : null,
                Schema::hasColumn('organization_access_requests', 'bio') ? 'bio' : null,
                Schema::hasColumn('organization_access_requests', 'location') ? 'location' : null,
                Schema::hasColumn('organization_access_requests', 'industry') ? 'industry' : null,
                Schema::hasColumn('organization_access_requests', 'organization_size') ? 'organization_size' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('organizations', function (Blueprint $table): void {
            $columns = array_filter([
                Schema::hasColumn('organizations', 'timezone') ? 'timezone' : null,
                Schema::hasColumn('organizations', 'contact_person_name') ? 'contact_person_name' : null,
                Schema::hasColumn('organizations', 'contact_person_email') ? 'contact_person_email' : null,
                Schema::hasColumn('organizations', 'contact_person_phone') ? 'contact_person_phone' : null,
                Schema::hasColumn('organizations', 'website') ? 'website' : null,
                Schema::hasColumn('organizations', 'bio') ? 'bio' : null,
                Schema::hasColumn('organizations', 'location') ? 'location' : null,
                Schema::hasColumn('organizations', 'industry') ? 'industry' : null,
                Schema::hasColumn('organizations', 'organization_size') ? 'organization_size' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
