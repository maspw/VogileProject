<?php

namespace Tests\Feature;

use App\Models\PelunasanPiutang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    private function user(string $username): User
    {
        return User::where('username', $username)->firstOrFail();
    }

    public function test_guest_cannot_open_transaction_pages(): void
    {
        $this->get(route('penjualan.index'))->assertRedirect('/login');
    }

    public function test_user_without_role_cannot_open_restricted_module(): void
    {
        $this->actingAs($this->user('sales'))
            ->get(route('barang.index'))
            ->assertForbidden();
    }

    public function test_creator_cannot_verify_own_transaction(): void
    {
        $this->actingAs($this->user('adminpenjualan'))
            ->post(route('penjualan.verify', 'PJ-ANOMALI-001'))
            ->assertForbidden();
    }

    public function test_payment_above_remaining_receivable_is_rejected(): void
    {
        $this->actingAs($this->user('adminpiutang'))
            ->from(route('pelunasan-piutang.index'))
            ->post(route('pelunasan-piutang.store'), [
                'id_penjualan' => 'PJ-202609-002',
                'tanggal' => '2026-11-20',
                'total' => 999999999,
                'metode_bayar' => 'Bank BCA',
            ])
            ->assertRedirect(route('pelunasan-piutang.index'))
            ->assertSessionHas('error');

        $this->assertSame(1, PelunasanPiutang::count());
    }

    public function test_valid_payment_is_stored_with_its_method(): void
    {
        $this->actingAs($this->user('adminpiutang'))
            ->post(route('pelunasan-piutang.store'), [
                'id_penjualan' => 'PJ-202609-002',
                'tanggal' => '2026-11-20',
                'total' => 1000000,
                'metode_bayar' => 'Kas',
            ])
            ->assertRedirect(route('pelunasan-piutang.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('pelunasan_piutang', [
            'id_penjualan' => 'PJ-202609-002',
            'metode_bayar' => 'Kas',
            'status_verifikasi' => 'Menunggu',
        ]);
    }

    public function test_return_quantity_cannot_exceed_sold_quantity(): void
    {
        $this->actingAs($this->user('adminpenjualan'))
            ->from(route('retur-penjualan.index'))
            ->post(route('retur-penjualan.store'), [
                'id_penjualan' => 'PJ-202609-002',
                'tanggal' => '2026-11-20',
                'alasan' => 'Uji validasi jumlah retur',
                'items' => [
                    ['kode_barang' => 'BRG002', 'jumlah' => 500, 'harga_satuan' => 78000],
                ],
            ])
            ->assertRedirect(route('retur-penjualan.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('retur_penjualan', ['alasan' => 'Uji validasi jumlah retur']);
    }
}
