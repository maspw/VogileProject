<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nota Pembelian - {{ $pembelian->nota_pembelian }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #172B4D; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #168C8C; padding-bottom: 12px; margin-bottom: 20px; }
        .company-name { font-size: 20px; font-weight: bold; color: #172B4D; }
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
                <div class="company-sub">Divisi Pengadaan & Logistik Gudang</div>
                <div class="company-sub">Jl. Industri Raya No. 88, Kawasan Niaga Mandiri, Jakarta</div>
            </td>
            <td style="width: 40%; vertical-align: top;">
                <div class="doc-title">NOTA PEMBELIAN</div>
                <div style="text-align: right; font-size: 12px; font-weight: bold; margin-top: 5px;">{{ $pembelian->nota_pembelian }}</div>
                <div style="text-align: right; margin-top: 4px;">
                    <span class="badge {{ $pembelian->status_verifikasi === 'Terverifikasi' ? 'badge-verified' : 'badge-pending' }}">
                        {{ strtoupper($pembelian->status_verifikasi) }}
                    </span>
                </div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <strong>Pemasok / Supplier:</strong><br>
                <span style="font-size: 13px; font-weight: bold;">{{ $pembelian->supplier->nama_supplier ?? '-' }}</span><br>
                {{ $pembelian->supplier->alamat ?? 'Alamat tidak terdata' }}<br>
                Telp: {{ $pembelian->supplier->no_telp ?? '-' }}
            </td>
            <td style="width: 50%;">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 40%;"><strong>Tanggal Terima</strong></td>
                        <td>: {{ date('d F Y', strtotime($pembelian->tanggal)) }}</td>
                    </tr>
                    <tr>
                        <td><strong>No. Referensi PO</strong></td>
                        <td>: {{ $pembelian->no_po ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Metode Bayar</strong></td>
                        <td>: {{ $pembelian->metode_pembayaran }}</td>
                    </tr>
                    @if($pembelian->metode_pembayaran === 'Kredit')
                    <tr>
                        <td><strong>Jatuh Tempo</strong></td>
                        <td>: {{ $pembelian->jatuh_tempo ? date('d F Y', strtotime($pembelian->jatuh_tempo)) : '-' }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Admin Pembelian</strong></td>
                        <td>: {{ $pembelian->user->nama_user ?? $pembelian->user->name ?? '-' }}</td>
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
                <th>Deskripsi Barang Masuk</th>
                <th class="text-center" style="width: 60px;">Satuan</th>
                <th class="text-center" style="width: 50px;">Qty</th>
                <th class="text-right" style="width: 100px;">Harga Beli</th>
                <th class="text-right" style="width: 110px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pembelian->details as $idx => $detail)
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
            <tr class="grand-total">
                <td>TOTAL PEMBELIAN</td>
                <td class="text-right">Rp {{ number_format($pembelian->total, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <table style="width: 100%;">
            <tr>
                <td class="signature-box">
                    <div>Bagian Gudang (Penerima),</div>
                    <div class="signature-space"></div>
                    <div>( .................................... )</div>
                </td>
                <td class="signature-box">
                    <div>Penyetuju / Verifikator,</div>
                    <div class="signature-space"></div>
                    <div>( {{ $pembelian->verifier->nama_user ?? 'Admin Pembelian' }} )</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
