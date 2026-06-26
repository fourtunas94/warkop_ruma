<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPaymentAndPromoToPesanans extends Migration
{
    public function up()
    {
        $fields = [
            'metode_pembayaran' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'default'    => 'tunai',
                'after'      => 'status', // Posisi kolom setelah kolom status
            ],
            'kode_promo' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'metode_pembayaran',
            ],
            'potongan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'after'      => 'kode_promo',
            ],
        ];
        
        $this->forge->addColumn('pesanans', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('pesanans', ['metode_pembayaran', 'kode_promo', 'potongan']);
    }
}