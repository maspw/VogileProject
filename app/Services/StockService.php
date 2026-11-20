<?php

namespace App\Services;

use App\Models\Barang;
use App\Models\KartuStok;
use Exception;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Mutasi Masuk: Menambah stok barang dan mencatat ke kartu_stok
     */
    public function recordMasuk(
        string $kodeBarang,
        int $qty,
        float $hargaSatuan,
        string $jenisTransaksi,
        string $noReferensi,
        ?string $keterangan = null
    ): KartuStok {
        if ($qty <= 0) {
            throw new Exception("Jumlah mutasi masuk harus lebih besar dari 0 untuk barang {$kodeBarang}.");
        }

        return DB::transaction(function () use ($kodeBarang, $qty, $hargaSatuan, $jenisTransaksi, $noReferensi, $keterangan) {
            $barang = Barang::where('kode_barang', $kodeBarang)->lockForUpdate()->firstOrFail();

            // Hitung moving average HPP jika mutasi dari Pembelian
            if ($jenisTransaksi === 'Pembelian' && ($barang->jumlah_barang + $qty) > 0) {
                $totalNilaiLama = (float) ($barang->jumlah_barang * $barang->hpp);
                $totalNilaiBaru = (float) ($qty * $hargaSatuan);
                $totalQtyBaru = $barang->jumlah_barang + $qty;
                $hppBaru = ($totalNilaiLama + $totalNilaiBaru) / $totalQtyBaru;
                $barang->hpp = round($hppBaru, 2);
            }

            $barang->jumlah_barang += $qty;
            $barang->save();

            return KartuStok::create([
                'kode_barang' => $kodeBarang,
                'tanggal' => now(),
                'jenis_transaksi' => $jenisTransaksi,
                'no_referensi' => $noReferensi,
                'masuk' => $qty,
                'keluar' => 0,
                'saldo' => $barang->jumlah_barang,
                'harga_satuan' => $hargaSatuan,
                'keterangan' => $keterangan,
            ]);
        });
    }

    /**
     * Mutasi Keluar: Mengurangi stok barang dan mencatat ke kartu_stok
     */
    public function recordKeluar(
        string $kodeBarang,
        int $qty,
        float $hargaSatuan,
        string $jenisTransaksi,
        string $noReferensi,
        ?string $keterangan = null
    ): KartuStok {
        if ($qty <= 0) {
            throw new Exception("Jumlah mutasi keluar harus lebih besar dari 0 untuk barang {$kodeBarang}.");
        }

        return DB::transaction(function () use ($kodeBarang, $qty, $hargaSatuan, $jenisTransaksi, $noReferensi, $keterangan) {
            $barang = Barang::where('kode_barang', $kodeBarang)->lockForUpdate()->firstOrFail();

            if ($barang->jumlah_barang < $qty) {
                throw new Exception("Stok tidak mencukupi untuk barang {$barang->nama_barang} ({$kodeBarang}). Sisa stok: {$barang->jumlah_barang}, diminta: {$qty}.");
            }

            $barang->jumlah_barang -= $qty;
            $barang->save();

            return KartuStok::create([
                'kode_barang' => $kodeBarang,
                'tanggal' => now(),
                'jenis_transaksi' => $jenisTransaksi,
                'no_referensi' => $noReferensi,
                'masuk' => 0,
                'keluar' => $qty,
                'saldo' => $barang->jumlah_barang,
                'harga_satuan' => $hargaSatuan,
                'keterangan' => $keterangan,
            ]);
        });
    }
}
