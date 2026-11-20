<?php

namespace Tests\Feature;

use App\Models\Barang;
use App\Models\KartuStok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::where('username', 'owner')->firstOrFail();
    }

    public function test_owner_can_open_every_application_page(): void
    {
        $this->seed();

        $routes = [
            'dashboard',
            'barang.index',
            'pelanggan.index',
            'supplier.index',
            'coa.index',
            'po.index',
            'pembelian.index',
            'retur-pembelian.index',
            'pembayaran-hutang.index',
            'penjualan.index',
            'retur-penjualan.index',
            'pelunasan-piutang.index',
            'beban.index',
        ];

        foreach ($routes as $route) {
            $this->actingAs($this->owner())
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_dashboard_shows_seeded_financial_summary(): void
    {
        $this->seed();

        $this->actingAs($this->owner())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Posisi Piutang')
            ->assertSee('3.644.000'); // 7.800.000 - 156.000 retur - 4.000.000 pelunasan
    }

    public function test_barang_stock_matches_last_kartu_stok_saldo(): void
    {
        $this->seed();

        foreach (Barang::all() as $barang) {
            $last = KartuStok::where('kode_barang', $barang->kode_barang)
                ->orderByDesc('tanggal')
                ->orderByDesc('id')
                ->first();

            $this->assertNotNull($last, "Kartu stok {$barang->kode_barang} tidak ditemukan.");
            $this->assertSame(
                (int) $barang->jumlah_barang,
                (int) $last->saldo,
                "Saldo kartu stok terakhir {$barang->kode_barang} tidak sama dengan stok barang."
            );
        }
    }

    public function test_all_seeded_journals_are_balanced(): void
    {
        $this->seed();

        $documents = DB::table('v_jurnal_umum')
            ->selectRaw('tipe_transaksi, id_sumber, SUM(debit) as total_debit, SUM(kredit) as total_kredit')
            ->groupBy('tipe_transaksi', 'id_sumber')
            ->get();

        $this->assertNotEmpty($documents);

        foreach ($documents as $doc) {
            $this->assertEquals(
                $doc->total_debit,
                $doc->total_kredit,
                "Jurnal {$doc->tipe_transaksi} {$doc->id_sumber} tidak seimbang."
            );
        }
    }
}
