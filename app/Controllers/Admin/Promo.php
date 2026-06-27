<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PromoModel;

class Promo extends BaseController
{
    protected PromoModel $promoModel;

    public function __construct()
    {
        $this->promoModel = new PromoModel();
    }

    public function index()
    {
        return view('admin/promo/index', [
            'title'  => 'Promo — ARZANA',
            'promos' => $this->promoModel
                ->orderBy('id', 'DESC')
                ->findAll(),
        ]);
    }

    public function store()
    {
        return $this->savePromo();
    }

    public function update(int $id)
    {
        return $this->savePromo($id);
    }

    public function toggle(int $id)
    {
        $promo = $this->promoModel->find($id);

        if (! $promo) {
            return redirect()->to(base_url('admin/promo'))
                ->with('error', 'Promo tidak ditemukan.');
        }

        $this->promoModel->update($id, [
            'is_active' => ((int) $promo['is_active'] === 1) ? 0 : 1,
        ]);

        return redirect()->to(base_url('admin/promo'))
            ->with('success', 'Status promo berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $promo = $this->promoModel->find($id);

        if (! $promo) {
            return redirect()->to(base_url('admin/promo'))
                ->with('error', 'Promo tidak ditemukan.');
        }

        $this->promoModel->delete($id);

        return redirect()->to(base_url('admin/promo'))
            ->with('success', 'Promo berhasil dihapus.');
    }

    private function savePromo(?int $id = null)
    {
        $kode            = $this->normalizeKode((string) $this->request->getPost('kode'));
        $namaPromo       = trim((string) $this->request->getPost('nama_promo'));
        $deskripsi       = trim((string) $this->request->getPost('deskripsi'));
        $tipeDiskon      = trim((string) $this->request->getPost('tipe_diskon'));
        $nilaiDiskon     = (int) $this->request->getPost('nilai_diskon');
        $maksimalDiskon  = (int) $this->request->getPost('maksimal_diskon');
        $minimalBelanja  = (int) $this->request->getPost('minimal_belanja');
        $kuota           = (int) $this->request->getPost('kuota');
        $berlakuMulai    = $this->normalizeDateTime((string) $this->request->getPost('berlaku_mulai'));
        $berlakuSampai   = $this->normalizeDateTime((string) $this->request->getPost('berlaku_sampai'));
        $isActive        = $this->request->getPost('is_active') === '1' ? 1 : 0;

        $errors = [];

        if ($id !== null && ! $this->promoModel->find($id)) {
            return redirect()->to(base_url('admin/promo'))
                ->with('error', 'Promo tidak ditemukan.');
        }

        if ($kode === '') {
            $errors[] = 'Kode promo wajib diisi.';
        }

        if ($kode !== '' && ! preg_match('/^[A-Z0-9_-]+$/', $kode)) {
            $errors[] = 'Kode promo hanya boleh huruf, angka, strip, dan underscore.';
        }

        $kodeCheck = $this->promoModel->where('kode', $kode);

        if ($id !== null) {
            $kodeCheck->where('id !=', $id);
        }

        if ($kode !== '' && $kodeCheck->first()) {
            $errors[] = 'Kode promo sudah digunakan.';
        }

        if ($namaPromo === '') {
            $errors[] = 'Nama promo wajib diisi.';
        }

        if (! in_array($tipeDiskon, ['persen', 'nominal'], true)) {
            $errors[] = 'Tipe diskon tidak valid.';
        }

        if ($tipeDiskon === 'persen' && ($nilaiDiskon < 1 || $nilaiDiskon > 100)) {
            $errors[] = 'Diskon persen harus antara 1 sampai 100.';
        }

        if ($tipeDiskon === 'nominal' && $nilaiDiskon < 1) {
            $errors[] = 'Diskon nominal harus lebih dari 0.';
        }

        if ($maksimalDiskon < 0) {
            $errors[] = 'Maksimal diskon tidak boleh minus.';
        }

        if ($minimalBelanja < 0) {
            $errors[] = 'Minimal belanja tidak boleh minus.';
        }

        if ($kuota < 0) {
            $errors[] = 'Kuota tidak boleh minus.';
        }

        if ($berlakuMulai && $berlakuSampai && strtotime($berlakuMulai) > strtotime($berlakuSampai)) {
            $errors[] = 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.';
        }

        if (! empty($errors)) {
            return redirect()->to(base_url('admin/promo'))
                ->withInput()
                ->with('errors', $errors);
        }

        $data = [
            'kode'             => $kode,
            'nama_promo'       => $namaPromo,
            'deskripsi'        => $deskripsi !== '' ? $deskripsi : null,
            'tipe_diskon'      => $tipeDiskon,
            'nilai_diskon'     => $nilaiDiskon,
            'maksimal_diskon'  => $maksimalDiskon,
            'minimal_belanja'  => $minimalBelanja,
            'kuota'            => $kuota,
            'berlaku_mulai'    => $berlakuMulai,
            'berlaku_sampai'   => $berlakuSampai,
            'is_active'        => $isActive,
        ];

        if ($id === null) {
            $this->promoModel->insert($data);
            $message = 'Promo baru berhasil dibuat.';
        } else {
            $this->promoModel->update($id, $data);
            $message = 'Promo berhasil diperbarui.';
        }

        return redirect()->to(base_url('admin/promo'))
            ->with('success', $message);
    }

    private function normalizeKode(string $kode): string
    {
        $kode = strtoupper(trim($kode));
        $kode = preg_replace('/\s+/', '', $kode);

        return $kode ?? '';
    }

    private function normalizeDateTime(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $value = str_replace('T', ' ', $value);
        $timestamp = strtotime($value);

        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $timestamp);
    }
}