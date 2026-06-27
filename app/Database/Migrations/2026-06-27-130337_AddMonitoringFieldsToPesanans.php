<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMonitoringFieldsToPesanans extends Migration
{
    protected string $tableName = 'pesanans';

    public function up()
    {
        if (! $this->db->tableExists($this->tableName)) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('nomor_antrian', $this->tableName)) {
            $fields['nomor_antrian'] = [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'after'      => 'kode_pesanan',
            ];
        }

        if (! $this->db->fieldExists('status_pesanan', $this->tableName)) {
            $fields['status_pesanan'] = [
                'type'       => 'ENUM',
                'constraint' => ['menunggu', 'siap', 'selesai'],
                'default'    => 'menunggu',
                'after'      => 'status',
            ];
        }

        if (! $this->db->fieldExists('waktu_siap', $this->tableName)) {
            $fields['waktu_siap'] = [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'status_pesanan',
            ];
        }

        if (! empty($fields)) {
            $this->forge->addColumn($this->tableName, $fields);
        }
    }

    public function down()
    {
        if (! $this->db->tableExists($this->tableName)) {
            return;
        }

        if ($this->db->fieldExists('waktu_siap', $this->tableName)) {
            $this->forge->dropColumn($this->tableName, 'waktu_siap');
        }

        if ($this->db->fieldExists('status_pesanan', $this->tableName)) {
            $this->forge->dropColumn($this->tableName, 'status_pesanan');
        }

        if ($this->db->fieldExists('nomor_antrian', $this->tableName)) {
            $this->forge->dropColumn($this->tableName, 'nomor_antrian');
        }
    }
}