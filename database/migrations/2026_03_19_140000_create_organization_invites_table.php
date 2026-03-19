<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('organization_invites')) {
            return;
        }

        Schema::create('organization_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('email')->index();
            $table->string('role', 32)->default('editor');
            $table->string('token', 80)->unique();
            $table->string('status', 32)->default('pending')->index();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'email', 'status'], 'organization_invites_pending_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('organization_invites')) {
            return;
        }

        Schema::dropIfExists('organization_invites');
    }
};
