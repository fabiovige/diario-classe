<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollment_documents', function (Blueprint $table) {
            $table->string('status')->default('not_uploaded')->after('document_type');
            $table->string('file_path')->nullable()->after('notes');
            $table->string('original_filename')->nullable()->after('file_path');
            $table->string('mime_type')->nullable()->after('original_filename');
            $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete()->after('file_size');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('rejection_reason')->nullable()->after('reviewed_at');
        });

        DB::table('enrollment_documents')
            ->where('delivered', true)
            ->update(['status' => 'approved']);

        DB::table('enrollment_documents')
            ->where('delivered', false)
            ->update(['status' => 'not_uploaded']);

        Schema::table('enrollment_documents', function (Blueprint $table) {
            $table->dropColumn(['delivered', 'delivered_at']);
        });
    }

    public function down(): void
    {
        Schema::table('enrollment_documents', function (Blueprint $table) {
            $table->boolean('delivered')->default(false)->after('document_type');
            $table->date('delivered_at')->nullable()->after('delivered');
        });

        DB::table('enrollment_documents')
            ->where('status', 'approved')
            ->update(['delivered' => true, 'delivered_at' => now()]);

        Schema::table('enrollment_documents', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropColumn([
                'status',
                'file_path',
                'original_filename',
                'mime_type',
                'file_size',
                'reviewed_by',
                'reviewed_at',
                'rejection_reason',
            ]);
        });
    }
};
