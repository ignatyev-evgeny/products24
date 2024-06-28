<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('integrations', function (Blueprint $table) {
            $table->id();
            $table->string('domain');
            $table->string('auth_id');
            $table->string('refresh_id');
            $table->integer('expire');
            $table->string('lang');
            $table->jsonb('catalogs')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('integrations');
    }
};
