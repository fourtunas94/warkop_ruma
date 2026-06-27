<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPromoFieldsToPesanan extends Migration
{
    protected string $tableName = 'pesanans';

    public function up()
    {
        if (! $this->db->tableExists($this->tableName)) {
            return;
        }

        $fields = [];

        if (! $this->db->fieldExists('kode_promo', $this->tableName)) {
            $fields['kode_promo'] = [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ];
        }

        if (! $this->db->fieldExists('potongan', $this->tableName)) {
            $fields['potongan'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ];
        }

        if (! $this->db->fieldExists('ppn', $this->tableName)) {
            $fields['ppn'] = [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
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

        if ($this->db->fieldExists('kode_promo', $this->tableName)) {
            $this->forge->dropColumn($this->tableName, 'kode_promo');
        }

        if ($this->db->fieldExists('potongan', $this->tableName)) {
            $this->forge->dropColumn($this->tableName, 'potongan');
        }

        if ($this->db->fieldExists('ppn', $this->tableName)) {
            $this->forge->dropColumn($this->tableName, 'ppn');
        }
    }
}