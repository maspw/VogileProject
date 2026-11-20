<?php

namespace App\Http\Controllers;

use App\Models\PelunasanPiutang;
use App\Models\PembayaranHutang;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\ReturPembelian;
use App\Models\ReturPenjualan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    /**
     * Cetak Faktur Penjualan (Invoice)
     */
    public function fakturPenjualan(string $id)
    {
        $penjualan = Penjualan::with(['pelanggan', 'user', 'details.barang'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.faktur_penjualan', compact('penjualan'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Faktur-{$penjualan->id_penjualan}.pdf");
    }

    /**
     * Cetak Nota Pembelian
     */
    public function notaPembelian(string $nota)
    {
        $pembelian = Pembelian::with(['supplier', 'user', 'details.barang'])->findOrFail($nota);

        $pdf = Pdf::loadView('pdf.nota_pembelian', compact('pembelian'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("Nota-{$pembelian->nota_pembelian}.pdf");
    }

    /**
     * Cetak Bukti Retur Penjualan
     */
    public function returPenjualan(string $no_retur)
    {
        $retur = ReturPenjualan::with(['penjualan.pelanggan', 'user', 'details.barang'])->findOrFail($no_retur);

        $pdf = Pdf::loadView('pdf.bukti_retur', [
            'retur' => $retur,
            'tipe' => 'Penjualan',
            'partner' => $retur->penjualan->pelanggan->nama ?? '-',
            'noRef' => $retur->id_penjualan,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("Retur-Penjualan-{$retur->no_retur_penjualan}.pdf");
    }

    /**
     * Cetak Bukti Retur Pembelian
     */
    public function returPembelian(string $no_retur)
    {
        $retur = ReturPembelian::with(['pembelian.supplier', 'user', 'details.barang'])->findOrFail($no_retur);

        $pdf = Pdf::loadView('pdf.bukti_retur', [
            'retur' => $retur,
            'tipe' => 'Pembelian',
            'partner' => $retur->pembelian->supplier->nama_supplier ?? '-',
            'noRef' => $retur->nota_pembelian,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("Retur-Pembelian-{$retur->no_retur}.pdf");
    }

    /**
     * Cetak Kuitansi / Bukti Penerimaan Piutang
     */
    public function buktiPiutang(string $no_pelunasan)
    {
        $pelunasan = PelunasanPiutang::with(['penjualan.pelanggan', 'user'])->findOrFail($no_pelunasan);

        $pdf = Pdf::loadView('pdf.bukti_pembayaran', [
            'pembayaran' => $pelunasan,
            'judul' => 'KUITANSI PELUNASAN PIUTANG',
            'noBukti' => $pelunasan->no_pelunasan,
            'partnerLabel' => 'Diterima Dari',
            'partnerName' => $pelunasan->penjualan->pelanggan->nama ?? '-',
            'noTransaksiRef' => $pelunasan->id_penjualan,
        ])->setPaper('a5', 'landscape');

        return $pdf->stream("Kuitansi-Piutang-{$pelunasan->no_pelunasan}.pdf");
    }

    /**
     * Cetak Bukti Pengeluaran Pembayaran Hutang
     */
    public function buktiHutang(string $no_pembayaran)
    {
        $pembayaran = PembayaranHutang::with(['pembelian.supplier', 'user'])->findOrFail($no_pembayaran);

        $pdf = Pdf::loadView('pdf.bukti_pembayaran', [
            'pembayaran' => $pembayaran,
            'judul' => 'BUKTI PEMBAYARAN HUTANG USAHA',
            'noBukti' => $pembayaran->no_pembayaran,
            'partnerLabel' => 'Dibayarkan Kepada',
            'partnerName' => $pembayaran->pembelian->supplier->nama_supplier ?? '-',
            'noTransaksiRef' => $pembayaran->nota_pembelian,
        ])->setPaper('a5', 'landscape');

        return $pdf->stream("Bukti-Hutang-{$pembayaran->no_pembayaran}.pdf");
    }
}
