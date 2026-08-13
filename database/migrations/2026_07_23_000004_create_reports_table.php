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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->date('audit_date');
            $table->string('department_name');
            $table->string('category')->default('general');
            $table->string('auditor_name')->nullable();
            $table->string('auditee_name')->nullable();
            $table->json('summary')->nullable(); // Store counts for YES, OFI, NC Minor, NC Major
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
