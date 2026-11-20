<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bukti Retur {{ $tipe }} - {{ $retur->no_retur_penjualan ?? $retur->no_retur }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #172B4D; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #E53E3E; padding-bottom: 12px; margin-bottom: 20px; }
        .company-name { font-size: 20px; font-weight: bold; color: #172B4D; }
        .company-sub { font-size: 11px; color: #5E6C84; }
        .doc-title { font-size: 18px; font-weight: bold; color: #E53E3E; text-align: right; text-transform: uppercase; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { vertical-align: top; font-size: 11px; line-height: 1.5; }
        .item-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .item-table th { background: #F4F5F7; color: #172B4D; font-size: 10px; text-transform: uppercase; padding: 8px; border: 1px solid #DFE1E6; text-align: left; }
        .item-table td { padding: 8px; border: 1px solid #DFE1E6; font-size: 11px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-box { float: right; width: 280px; }
        .total-table { width: 100%; border-collapse: collapse; }
        .total-table td { padding: 5px 8px; font-size: 11px; }
        .total-table .grand-total { font-size: 14px; font-weight: bold; color: #E53E3E; border-top: 2px solid #172B4D; }
        .footer { clear: both; margin-top: 40px; padding-top: 20px; border-top: 1px solid #DFE1E6; }
        .signature-box { float: left; width: 45%; text-align: center; font-size: 11px; }
        .signature-space { height: 60px; }
    </style>
</head>
<body>
    <table style="width: 100%;" class="header">
        <tr>
            <td style="width: 60%;">
                <div class="company-name">PT VOGILE DISTRIBUSI</div>
                <div class="company-sub">Berita Acara & Bukti Pengembalian Barang (Retur)</div>
                <div class="company-sub">Jl. Industri Raya No. 88, Jakarta</div>
            </td>
            <td style="width: 40%; vertical-align: top;">
                <div class="doc-title">BUKTI RETUR {{ strtoupper($tipe) }}</div>
                <div style="text-align: right; font-size: 12px; font-weight: bold; margin-top: 5px;">{{ $retur->no_retur_penjualan ?? $retur->no_retur }}</div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <strong>Pihak Terkait:</strong><br>
                <span style="font-size: 13px; font-weight: bold;">{{ $partner }}</span><br>
                <strong>No. Referensi Asal:</strong> {{ $noRef }}
            </td>
            <td style="width: 50%;">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 40%;"><strong>Tanggal Retur</strong></td>
                        <td>: {{ date('d F Y', strtotime($retur->tanggal)) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Alasan Retur</strong></td>
                        <td>: {{ $retur->alasan }}</td>
                    </tr>
                    <tr>
                        <td><strong>Petugas Input</strong></td>
                        <td>: {{ $retur->user->nama_user ?? $retur->user->name ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="item-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th style="width: 90px;">Kode SKU</th>
                <th>Nama Barang Diretur</th>
                <th class="text-center" style="width: 50px;">Qty</th>
                <th class="text-right" style="width: 100px;">Nilai Satuan</th>
                <th class="text-right" style="width: 110px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($retur->details as $idx => $detail)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td><strong>{{ $detail->kode_barang }}</strong></td>
                <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                <td class="text-center">{{ number_format($detail->jumlah) }}</td>
                <td class="text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                <td class="text-right"><strong>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        <table class="total-table">
            <tr class="grand-total">
                <td>TOTAL NILAI RETUR</td>
                <td class="text-right">Rp {{ number_format($retur->total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <table style="width: 100%;">
            <tr>
                <td class="signature-box">
                    <div>Pihak Pengirim/Penerima,</div>
                    <div class="signature-space"></div>
                    <div>( {{ $partner }} )</div>
                </td>
                <td class="signature-box">
                    <div>Verifikator Gudang / Akuntansi,</div>
                    <div class="signature-space"></div>
                    <div>( {{ $retur->verifier->nama_user ?? 'Admin' }} )</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
