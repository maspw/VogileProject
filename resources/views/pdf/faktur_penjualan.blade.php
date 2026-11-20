<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Faktur Penjualan - {{ $penjualan->id_penjualan }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #172B4D; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #168C8C; padding-bottom: 12px; margin-bottom: 20px; }
        .company-name { font-size: 20px; font-weight: bold; color: #172B4D; letter-spacing: 0.5px; }
        .company-sub { font-size: 11px; color: #5E6C84; }
        .doc-title { font-size: 18px; font-weight: bold; color: #168C8C; text-align: right; text-transform: uppercase; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { vertical-align: top; font-size: 11px; line-height: 1.5; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .badge-verified { background: #E3FCEF; color: #006644; }
        .badge-pending { background: #FFF0B3; color: #172B4D; }
        .item-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .item-table th { background: #F4F5F7; color: #172B4D; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px; border: 1px solid #DFE1E6; text-align: left; }
        .item-table td { padding: 8px; border: 1px solid #DFE1E6; font-size: 11px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-box { float: right; width: 280px; }
        .total-table { width: 100%; border-collapse: collapse; }
        .total-table td { padding: 5px 8px; font-size: 11px; }
        .total-table .grand-total { font-size: 14px; font-weight: bold; color: #168C8C; border-top: 2px solid #172B4D; }
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
                <div class="company-sub">Sistem Distribusi Bahan Pokok & Sembako Terintegrasi</div>
                <div class="company-sub">Jl. Industri Raya No. 88, Kawasan Niaga Mandiri, Jakarta</div>
                <div class="company-sub">Telp: (021) 8899-7711 | Email: invoicing@vogile.com</div>
            </td>
            <td style="width: 40%; vertical-align: top;">
                <div class="doc-title">FAKTUR PENJUALAN</div>
                <div style="text-align: right; font-size: 12px; font-weight: bold; margin-top: 5px;">{{ $penjualan->id_penjualan }}</div>
                <div style="text-align: right; margin-top: 4px;">
                    <span class="badge {{ $penjualan->status_verifikasi === 'Terverifikasi' ? 'badge-verified' : 'badge-pending' }}">
                        {{ strtoupper($penjualan->status_verifikasi) }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <strong>Kepada Yth. (Pelanggan):</strong><br>
                <span style="font-size: 13px; font-weight: bold;">{{ $penjualan->pelanggan->nama ?? '-' }}</span><br>
                {{ $penjualan->pelanggan->alamat ?? 'Alamat tidak terdata' }}<br>
                Telp: {{ $penjualan->pelanggan->no_telp ?? '-' }}
            </td>
            <td style="width: 50%;">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 40%;"><strong>Tanggal Faktur</strong></td>
                        <td>: {{ date('d F Y', strtotime($penjualan->tanggal)) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Metode Bayar</strong></td>
                        <td>: {{ $penjualan->metode_pembayaran }}</td>
                    </tr>
                    @if($penjualan->metode_pembayaran === 'Kredit')
                    <tr>
                        <td><strong>Jatuh Tempo</strong></td>
                        <td>: {{ $penjualan->jatuh_tempo ? date('d F Y', strtotime($penjualan->jatuh_tempo)) : '-' }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Sales / Kasir</strong></td>
                        <td>: {{ $penjualan->user->nama_user ?? $penjualan->user->name ?? '-' }}</td>
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
                <th>Deskripsi Barang Dagang</th>
                <th class="text-center" style="width: 60px;">Satuan</th>
                <th class="text-center" style="width: 50px;">Qty</th>
                <th class="text-right" style="width: 100px;">Harga Satuan</th>
                <th class="text-right" style="width: 110px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($penjualan->details as $idx => $detail)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td><strong>{{ $detail->kode_barang }}</strong></td>
                <td>{{ $detail->barang->nama_barang ?? '-' }}</td>
                <td class="text-center">{{ $detail->barang->satuan ?? 'Pcs' }}</td>
                <td class="text-center">{{ number_format($detail->jumlah) }}</td>
                <td class="text-right">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
                <td class="text-right"><strong>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        <table class="total-table">
            <tr>
                <td>Subtotal</td>
                <td class="text-right">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Potongan / Retur</td>
                <td class="text-right">Rp 0</td>
            </tr>
            <tr class="grand-total">
                <td>TOTAL TAGIHAN</td>
                <td class="text-right">Rp {{ number_format($penjualan->total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <table style="width: 100%;">
            <tr>
                <td class="signature-box">
                    <div>Penerima / Pelanggan,</div>
                    <div class="signature-space"></div>
                    <div>( {{ $penjualan->pelanggan->nama ?? '..........................' }} )</div>
                </td>
                <td class="signature-box">
                    <div>Hormat Kami, PT VOGILE</div>
                    <div class="signature-space"></div>
                    <div>( {{ $penjualan->user->nama_user ?? 'Admin Penjualan' }} )</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
