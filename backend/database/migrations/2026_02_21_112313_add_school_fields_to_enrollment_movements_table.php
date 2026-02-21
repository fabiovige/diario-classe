<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollment_movements', function (Blueprint $table) {
            $table->foreignId('origin_school_id')->nullable()->after('reason')->constrained('schools')->nullOnDelete();
            $table->foreignId('destination_school_id')->nullable()->after('origin_school_id')->constrained('schools')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('enrollment_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('destination_school_id');
            $table->dropConstrainedForeignId('origin_school_id');
        });
    }
};
