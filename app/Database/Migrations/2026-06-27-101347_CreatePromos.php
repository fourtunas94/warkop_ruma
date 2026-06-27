<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePromos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'kode' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'nama_promo' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'deskripsi' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'tipe_diskon' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'nominal',
            ],
            'nilai_diskon' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'maksimal_diskon' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'minimal_belanja' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'kuota' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'jumlah_digunakan' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'berlaku_mulai' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'berlaku_sampai' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('kode');
        $this->forge->createTable('promos', true);
    }

    public function down()
    {
        $this->forge->dropTable('promos', true);
    }
}