<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('deals', function (Blueprint $table) {
            $table->integer('company_id')->nullable();
            $table->foreignId('integration_id')->constrained('integrations')->cascadeOnDelete();
            $table->integer('bitrix_id');
            $table->integer('contact_id')->nullable();
            $table->string('title')->nullable();
        });
    }

    public function down(): void {
        Schema::dropIfExists('deals');
    }
};
