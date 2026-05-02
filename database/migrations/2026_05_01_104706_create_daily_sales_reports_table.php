<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('daily_sales_reports', function (Blueprint $table) {
        $table->id();

        $table->date('report_date')->unique();

        $table->unsignedInteger('total_orders')->default(0);

        $table->decimal('total_revenue', 12, 2)->default(0);

        $table->decimal('average_order_value', 12, 2)->default(0);

        $table->unsignedInteger('processed_chunks')->default(0);

        $table->unsignedInteger('chunk_size')->default(0);

        $table->decimal('processing_time_ms', 12, 2)->default(0);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_sales_reports');
    }
};
