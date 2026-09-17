<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('review_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->unsignedSmallInteger('round_number')->default(1);
            $table->string('status')->default('in_progress'); // in_progress, completed
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->index(['submission_id', 'round_number']);
        });

        Schema::create('reviewer_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_round_id')->constrained('review_rounds')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->string('invitation_token')->nullable()->unique();
            $table->string('response_status')->default('pending'); // pending, accepted, declined, completed
            $table->date('due_date')->nullable();
            $table->string('recommendation')->nullable(); // accept, minor_revision, major_revision, reject
            $table->text('comments_for_author')->nullable();
            $table->text('confidential_comments_to_editor')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->index(['reviewer_id', 'response_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviewer_assignments');
        Schema::dropIfExists('review_rounds');
    }
};
