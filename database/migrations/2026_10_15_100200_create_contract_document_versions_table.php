<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Each HR document upload is stored as an independent version.
 * Corrections produce a new version; the previous is preserved.
 * Only one version can be in 'accepted' state per request at a time.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('contract_document_versions', function (Blueprint $t) {
            $t->id();

            $t->foreignId('request_id')
              ->constrained('contract_approval_requests')
              ->cascadeOnDelete();

            $t->unsignedTinyInteger('version_number')->default(1);

            // File metadata.
            $t->string('storage_path');           // private disk path
            $t->string('original_filename');
            $t->string('mime_type', 64)->nullable();
            $t->unsignedInteger('file_size')->nullable(); // bytes
            $t->string('integrity_hash', 64)->nullable(); // sha256

            // Uploader.
            $t->unsignedBigInteger('uploaded_by');
            $t->text('upload_note')->nullable();
            $t->timestamp('uploaded_at');

            // Linked to a specific approved revision.
            $t->unsignedBigInteger('associated_revision_id')->nullable();

            // Principal acceptance / correction.
            $t->timestamp('accepted_at')->nullable();
            $t->unsignedBigInteger('accepted_by')->nullable();
            $t->text('correction_note')->nullable();
            $t->timestamp('correction_requested_at')->nullable();

            // Teacher signing.
            $t->timestamp('signed_at')->nullable();
            $t->unsignedBigInteger('signed_by')->nullable();
            $t->string('signature_text')->nullable();
            $t->string('signature_ip', 45)->nullable();

            $t->timestamps();

            $t->index('request_id');
            $t->index('uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_document_versions');
    }
};
