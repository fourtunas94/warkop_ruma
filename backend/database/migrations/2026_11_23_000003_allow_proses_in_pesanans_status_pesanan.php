<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE pesanans DROP CONSTRAINT IF EXISTS pesanans_status_pesanan_check");
            DB::statement("ALTER TABLE pesanans ADD CONSTRAINT pesanans_status_pesanan_check CHECK (status_pesanan::text = ANY (ARRAY['menunggu'::character varying, 'proses'::character varying, 'siap'::character varying, 'selesai'::character varying]::text[]))");
        }
    }

    public function down(): void
    {
        // Keep permissive check constraint on rollback
    }
};
