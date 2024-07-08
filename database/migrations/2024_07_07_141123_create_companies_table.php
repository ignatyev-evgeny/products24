<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('companies', function (Blueprint $table) {
            $table->foreignId('integration_id')->constrained('integrations')->cascadeOnDelete();
            $table->integer('bitrix_id');
            $table->string('title')->nullable();
            $table->string('type')->nullable();
        });
    }

    public function down(): void {
        Schema::dropIfExists('companies');
    }
};
