<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // บันทึกวันเวลาที่ทำเครื่องหมาย "ส่งแล้ว" สำหรับแต่ละประเภทประเด็น
            $table->timestamp('ofi_sent_at')->nullable()->after('summary');
            $table->timestamp('minor_sent_at')->nullable()->after('ofi_sent_at');
            $table->timestamp('major_sent_at')->nullable()->after('minor_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['ofi_sent_at', 'minor_sent_at', 'major_sent_at']);
        });
    }
};
