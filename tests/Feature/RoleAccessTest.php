<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $username): User
    {
        return User::where('username', $username)->firstOrFail();
    }

    public function test_each_role_can_open_its_own_dashboard(): void
    {
        $this->seed();

        $roles = [
            'owner' => 'Dashboard Owner',
            'sales' => 'Dashboard Sales',
            'adminpenjualan' => 'Dashboard Admin Penjualan',
            'adminpiutang' => 'Dashboard Admin Piutang',
            'adminpembelian' => 'Dashboard Admin Pembelian',
            'adminhutang' => 'Dashboard Admin Hutang',
            'kasir' => 'Dashboard Kasir',
        ];

        foreach ($roles as $username => $heading) {
            $this->actingAs($this->user($username))
                ->get(route('dashboard'))
                ->assertOk()
                ->assertSee($heading);
        }
    }

    public function test_role_matrix_enforces_module_access(): void
    {
        $this->seed();

        $matrix = [
            'kasir' => ['allowed' => ['penjualan.index', 'retur-penjualan.index', 'pelunasan-piutang.index', 'pembayaran-hutang.index', 'beban.index'], 'denied' => ['barang.index', 'supplier.index', 'coa.index', 'po.index']],
            'sales' => ['allowed' => ['penjualan.index', 'retur-penjualan.index', 'pelanggan.index'], 'denied' => ['barang.index', 'supplier.index', 'beban.index', 'pembelian.index']],
            'adminpembelian' => ['allowed' => ['barang.index', 'supplier.index', 'po.index', 'pembelian.index', 'retur-pembelian.index'], 'denied' => ['penjualan.index', 'beban.index', 'coa.index']],
            'adminpenjualan' => ['allowed' => ['barang.index', 'pelanggan.index', 'penjualan.index', 'retur-penjualan.index'], 'denied' => ['supplier.index', 'po.index', 'beban.index']],
            'adminpiutang' => ['allowed' => ['pelanggan.index', 'pelunasan-piutang.index'], 'denied' => ['barang.index', 'penjualan.index', 'beban.index']],
            'adminhutang' => ['allowed' => ['supplier.index', 'pembayaran-hutang.index'], 'denied' => ['penjualan.index', 'po.index', 'beban.index']],
            'owner' => ['allowed' => ['barang.index', 'pelanggan.index', 'supplier.index', 'coa.index', 'po.index', 'pembelian.index', 'penjualan.index', 'beban.index'], 'denied' => []],
        ];

        foreach ($matrix as $username => $checks) {
            $user = $this->user($username);

            foreach ($checks['allowed'] as $route) {
                $this->actingAs($user)->get(route($route))->assertOk();
            }

            foreach ($checks['denied'] as $route) {
                $this->actingAs($user)->get(route($route))->assertForbidden();
            }
        }
    }

    public function test_sidebar_only_shows_menus_allowed_for_the_role(): void
    {
        $this->seed();

        $this->actingAs($this->user('kasir'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Beban Operasional')
            ->assertDontSee('Purchase Order')
            ->assertDontSee('Supplier')
            ->assertDontSee('Akun / COA');

        $this->actingAs($this->user('adminpembelian'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Purchase Order')
            ->assertSee('Retur Pembelian')
            ->assertDontSee('Pelunasan Piutang')
            ->assertDontSee('Beban Operasional');
    }
}
