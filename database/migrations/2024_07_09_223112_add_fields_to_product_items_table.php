<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('product_items', function (Blueprint $table) {
            $table->longText('article')->after('productName')->nullable();
            $table->longText('analogs')->after('article')->nullable();
        });
    }

    public function down(): void {
        Schema::table('product_items', function (Blueprint $table) {
            $table->dropColumn('article');
            $table->dropColumn('analogs');
        });
    }
};
