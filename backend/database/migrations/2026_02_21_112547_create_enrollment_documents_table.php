<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollment_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->string('document_type');
            $table->boolean('delivered')->default(false);
            $table->date('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['enrollment_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_documents');
    }
};
