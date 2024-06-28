<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('integration')->constrained('integrations')->cascadeOnDelete();
            $table->string('code');
            $table->string('title');
            $table->jsonb('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('product_fields');
    }
};
