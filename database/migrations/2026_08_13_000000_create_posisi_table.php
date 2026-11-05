<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posisi', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150)->unique();
        });

        $posisi = [
            'ACCOUNT PAYABLE (AP)',
            'ACCOUNT RECEIVABLE (AR)',
            'ACCOUNTING (A)',
            'ADMIN GUDANG (AG)',
            'ADMIN MARKETING & SOSMED',
            'Admin penjualan (SA)',
            'ADMIN PPIC (APP)',
            'ADMIN QC (AQC)',
            'ADMIN SCM (ASCM)',
            'DRIVER (DVR)',
            'General Affair (GA)',
            'HCP',
            'HRD Payroll (HRP)',
            'HRD Recruitment (HRR)',
            'Job Planner (JPL)',
            'Kas kecil (KAS)',
            'Kepala Gudang (KG)',
            'Khusus',
            'Logistik (LGT)',
            'MARKETING (M)',
            'PIC Audit Team',
            'QUALITY CONTROL ANALIS (QCA)',
            'Sales (SLS)',
            'Sales Distribusi (SAD)',
            'Sales marketing (SMK)',
            'SCM-FG (SFG)',
            'STAFF ACCOUNTING & TAX (SAT)',
            'Staff Gudang',
            'Staff Import (SIM)',
            'Staff legal (SLG)',
            'Staff Penjualan dan Digital Marketing',
            'Staff purchasing (SPU)',
            'Staff Sales Executive (SSE)',
            'Staff sekretaris (SS)',
            'Supervisor Sales (SPVS)',
            'Utility (UTL)'
        ];

        foreach ($posisi as $p) {
            DB::table('posisi')->insert(['nama' => $p]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posisi');
    }
};
