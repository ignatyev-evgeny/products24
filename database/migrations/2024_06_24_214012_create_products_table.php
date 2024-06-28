<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration')->constrained('integrations')->cascadeOnDelete();
            $table->integer('bitrix_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('active')->nullable();
            $table->string('preview_picture')->nullable();
            $table->string('detail_picture')->nullable();
            $table->string('sort')->nullable();
            $table->string('xml_id')->nullable();
            $table->string('timestamp_x')->nullable();
            $table->string('date_create')->nullable();
            $table->string('modified_by')->nullable();
            $table->string('created_by')->nullable();
            $table->string('catalog_id')->nullable();
            $table->string('section_id')->nullable();
            $table->string('description')->nullable();
            $table->string('description_type')->nullable();
            $table->string('price')->nullable();
            $table->string('currency_id')->nullable();
            $table->string('vat_id')->nullable();
            $table->string('vat_included')->nullable();
            $table->string('measure')->nullable();
            $table->jsonb('fields')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('products');
    }
};
