<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('id_user', 20)->nullable()->unique()->after('id');
            $table->string('nama_user', 100)->nullable()->after('name');
            $table->string('username', 50)->nullable()->unique()->after('email');
            $table->integer('id_role')->nullable()->after('password');

            $table->foreign('id_role')->references('id_role')->on('role')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_role']);
            $table->dropColumn(['id_user', 'nama_user', 'username', 'id_role']);
        });
    }
};
