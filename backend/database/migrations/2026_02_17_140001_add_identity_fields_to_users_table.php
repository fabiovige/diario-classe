<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('cpf', 11)->unique()->nullable()->after('email');
            $table->string('status')->default('active')->after('password');
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete()->after('status');
            $table->foreignId('school_id')->nullable()->after('role_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['cpf', 'status', 'role_id', 'school_id']);
        });
    }
};
