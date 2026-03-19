<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('organization_invites')) {
            return;
        }

        Schema::table('organization_invites', function (Blueprint $table): void {
            if (! Schema::hasColumn('organization_invites', 'status')) {
                $table->string('status', 32)->default('pending')->after('token')->index();
            }

            if (! Schema::hasColumn('organization_invites', 'invited_by')) {
                $table->foreignId('invited_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('organization_invites', 'accepted_by')) {
                $table->foreignId('accepted_by')->nullable()->after('invited_by')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('organization_invites', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('accepted_by');
            }

            if (! Schema::hasColumn('organization_invites', 'accepted_at')) {
                $table->timestamp('accepted_at')->nullable()->after('expires_at');
            }

            if (! Schema::hasColumn('organization_invites', 'created_at')) {
                $table->timestamp('created_at')->nullable()->after('accepted_at');
            }

            if (! Schema::hasColumn('organization_invites', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('organization_invites')) {
            return;
        }

        Schema::table('organization_invites', function (Blueprint $table): void {
            $columns = array_values(array_filter([
                Schema::hasColumn('organization_invites', 'status') ? 'status' : null,
                Schema::hasColumn('organization_invites', 'invited_by') ? 'invited_by' : null,
                Schema::hasColumn('organization_invites', 'accepted_by') ? 'accepted_by' : null,
                Schema::hasColumn('organization_invites', 'expires_at') ? 'expires_at' : null,
                Schema::hasColumn('organization_invites', 'accepted_at') ? 'accepted_at' : null,
                Schema::hasColumn('organization_invites', 'created_at') ? 'created_at' : null,
                Schema::hasColumn('organization_invites', 'updated_at') ? 'updated_at' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
