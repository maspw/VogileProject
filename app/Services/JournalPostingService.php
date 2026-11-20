<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;

class JournalPostingService
{
    /**
     * Posting sekumpulan entri jurnal ke tabel target `ju_*`
     * Format tiap baris entri:
     * [
     *     'kode_akun' => 1101,
     *     'debit' => 500000,
     *     'kredit' => 0,
     *     'keterangan' => 'Keterangan jurnal'
     * ]
     */
    public function post(
        string $tableName,
        string $idColumn,
        string $idValue,
        string $tanggal,
        array $entries
    ): void {
        if (empty($entries)) {
            throw new Exception("Entri jurnal tidak boleh kosong untuk transaksi {$idValue}.");
        }

        // Group entries berdasarkan kode_akun untuk mencegah bentrok composite primary key
        $grouped = [];
        $totalDebit = 0;
        $totalKredit = 0;

        foreach ($entries as $e) {
            $kode = (int) $e['kode_akun'];
            $deb = (int) round((float) ($e['debit'] ?? 0));
            $kre = (int) round((float) ($e['kredit'] ?? 0));
            $ket = $e['keterangan'] ?? '';

            if (!isset($grouped[$kode])) {
                $grouped[$kode] = [
                    'debit' => 0,
                    'kredit' => 0,
                    'keterangan' => $ket,
                ];
            }

            $grouped[$kode]['debit'] += $deb;
            $grouped[$kode]['kredit'] += $kre;

            $totalDebit += $deb;
            $totalKredit += $kre;
        }

        // Validasi Keseimbangan Akuntansi (Σdebit == Σkredit)
        if ($totalDebit !== $totalKredit) {
            throw new Exception("Jurnal Umum tidak seimbang untuk {$idValue}! Total Debit: Rp " . number_format($totalDebit, 0, ',', '.') . " != Total Kredit: Rp " . number_format($totalKredit, 0, ',', '.'));
        }

        // Idempotensi: Hapus jurnal lama sebelum re-insert jika transaksi di-post ulang
        DB::table($tableName)->where($idColumn, $idValue)->delete();

        // Simpan baris-baris jurnal
        $insertData = [];
        foreach ($grouped as $kodeAkun => $item) {
            $insertData[] = [
                $idColumn => $idValue,
                'kode_akun' => $kodeAkun,
                'debit' => $item['debit'],
                'kredit' => $item['kredit'],
                'keterangan' => $item['keterangan'],
                'tanggal' => $tanggal,
            ];
        }

        DB::table($tableName)->insert($insertData);
    }
}
