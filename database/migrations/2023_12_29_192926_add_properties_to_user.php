<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('city', 50)->nullable()->after('address2');
            $table->string('state', 50)->nullable()->after('city');
            $table->string('zip', 10)->nullable()->after('state');
            $table->string('country', 50)->nullable()->after('zip');
            $table->string('company', 100)->nullable()->after('country');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['city', 'state', 'zip', 'country', 'company']);
        });
    }
};
