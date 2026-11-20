<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quests', function (Blueprint $table) {
            $table->id();
            $table->string('number', 20)->nullable()->unique();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('category', 20);
            $table->string('description', 200);
            $table->string('photo_before');
            $table->string('photo_after')->nullable();
            // active, in_progress, resolved, rejected
            $table->string('status', 20)->default('active')->index();
            $table->boolean('is_raid_boss')->default(false);
            $table->timestamp('raid_boss_at')->nullable();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->string('technician_note', 500)->nullable();
            $table->string('flagged_reason')->nullable();
            $table->timestamp('flagged_at')->nullable();
            $table->foreignId('moderator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->string('reject_reason')->nullable();
            $table->text('moderation_note')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'category', 'status']);
        });

        Schema::create('quest_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['quest_id', 'user_id']);
        });

        Schema::create('xp_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quest_id')->nullable()->constrained()->nullOnDelete();
            // quest_created, discoverer_bonus, party, rejected_penalty
            $table->string('type', 30);
            $table->integer('amount');
            $table->string('description');
            $table->timestamps();

            // Setiap jenis XP hanya sekali per quest per orang.
            $table->unique(['user_id', 'quest_id', 'type']);
        });

        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tier', 10);
            $table->string('serial', 30)->unique();
            $table->integer('xp_at_issue');
            $table->unsignedInteger('valid_reports');
            $table->timestamp('issued_at');
            $table->timestamps();

            $table->unique(['user_id', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('xp_transactions');
        Schema::dropIfExists('quest_confirmations');
        Schema::dropIfExists('quests');
    }
};
