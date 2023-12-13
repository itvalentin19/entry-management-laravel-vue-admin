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
        Schema::create('entities', function (Blueprint $table) {
            $table->id();
            $table->string('firm_name')->nullable();
            $table->string('entity_name')->nullable();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();
            $table->string('type')->nullable();
            $table->string('contact_first_name')->nullable();
            $table->string('contact_last_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('services')->nullable();
            $table->integer('annual_fee')->nullable();
            $table->string('first_tax_year')->nullable();
            $table->tinyInteger('directors')->nullable();
            $table->string('ein_number')->nullable();
            $table->string('form_id')->nullable();
            $table->string('date_signed')->nullable();
            $table->string('person')->nullable();
            $table->string('jurisdiction')->nullable();
            $table->string('owners')->nullable();
            $table->string('documents')->nullable();
            $table->string('notes')->nullable();
            $table->string('ref_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entities');
    }
};
