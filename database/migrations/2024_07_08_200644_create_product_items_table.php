<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('product_items', function (Blueprint $table) {
            $table->foreignId('integration_id')->constrained('integrations')->cascadeOnDelete();
            $table->integer('company_id')->nullable();
            $table->integer('deal_id');
            $table->integer('bitrix_id');
            $table->integer('productId')->nullable();
            $table->string('productName')->nullable();
            $table->float('price')->nullable();
            $table->float('priceAccount')->nullable();
            $table->float('priceExclusive')->nullable();
            $table->float('priceNetto')->nullable();
            $table->float('priceBrutto')->nullable();
            $table->float('quantity')->nullable();
            $table->integer('discountTypeId')->nullable();
            $table->float('discountRate')->nullable();
            $table->float('discountSum')->nullable();
            $table->float('taxRate')->nullable();
            $table->string('taxIncluded')->nullable();
            $table->string('customized')->nullable();
            $table->integer('measureCode')->nullable();
            $table->string('measureName')->nullable();
            $table->integer('type')->nullable();
        });
    }

    public function down(): void {
        Schema::dropIfExists('product_items');
    }
};
