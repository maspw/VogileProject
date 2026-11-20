<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $judul }} - {{ $noBukti }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #172B4D; margin: 0; padding: 15px; }
        .kuitansi-border { border: 2px dashed #168C8C; padding: 15px; border-radius: 6px; }
        .header { border-bottom: 2px solid #168C8C; padding-bottom: 8px; margin-bottom: 15px; }
        .company-name { font-size: 16px; font-weight: bold; color: #172B4D; }
        .doc-title { font-size: 16px; font-weight: bold; color: #168C8C; text-align: right; }
        .table-data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-data td { padding: 6px 4px; font-size: 12px; vertical-align: top; }
        .nominal-box { background: #E3FCEF; border: 1px solid #36B37E; padding: 10px 15px; font-size: 18px; font-weight: bold; color: #006644; display: inline-block; border-radius: 4px; margin-top: 10px; }
        .signature-box { float: right; width: 220px; text-align: center; font-size: 11px; margin-top: 15px; }
        .signature-space { height: 50px; }
    </style>
</head>
<body>
    <div class="kuitansi-border">
        <table style="width: 100%;" class="header">
            <tr>
                <td style="width: 50%;">
                    <div class="company-name">PT VOGILE DISTRIBUSI</div>
                    <div style="font-size: 10px; color: #5E6C84;">Jl. Industri Raya No. 88, Jakarta | Telp: (021) 8899-7711</div>
                </td>
                <td style="width: 50%;">
                    <div class="doc-title">{{ $judul }}</div>
                    <div style="text-align: right; font-size: 11px; font-weight: bold;">NO: {{ $noBukti }}</div>
                </td>
            </tr>
        </table>

        <table class="table-data">
            <tr>
                <td style="width: 25%;"><strong>Tanggal Transaksi</strong></td>
                <td>: {{ date('d F Y', strtotime($pembayaran->tanggal)) }}</td>
            </tr>
            <tr>
                <td><strong>{{ $partnerLabel }}</strong></td>
                <td>: <strong style="font-size: 13px;">{{ $partnerName }}</strong></td>
            </tr>
            <tr>
                <td><strong>No. Transaksi Referensi</strong></td>
                <td>: {{ $noTransaksiRef }}</td>
            </tr>
            <tr>
                <td><strong>Keterangan / Catatan</strong></td>
                <td>: {{ $pembayaran->catatan ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>Status Verifikasi</strong></td>
                <td>: <strong style="color: #006644;">{{ strtoupper($pembayaran->status_verifikasi) }}</strong></td>
            </tr>
            <tr>
                <td><strong>Jumlah Pembayaran</strong></td>
                <td>
                    <div class="nominal-box">
                        Rp {{ number_format($pembayaran->total, 0, ',', '.') }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="signature-box">
            <div>Jakarta, {{ date('d F Y', strtotime($pembayaran->tanggal)) }}</div>
            <div>Kasir / Bagian Keuangan,</div>
            <div class="signature-space"></div>
            <div>( {{ $pembayaran->user->nama_user ?? 'Kasir Keuangan' }} )</div>
        </div>

        <div style="clear: both;"></div>
    </div>
</body>
</html>
