@php($barangs = $barangs ?? collect())

<div class="space-y-2">
    <div class="hidden md:grid md:grid-cols-12 gap-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500 px-1">
        <div class="col-span-5">Barang</div>
        <div class="col-span-2">Jumlah</div>
        <div class="col-span-3">Harga Satuan</div>
        <div class="col-span-2">&nbsp;</div>
    </div>

    <div id="item-rows" class="space-y-2"></div>

    <button type="button" onclick="addItemRow()"
            class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-lg border border-dashed border-slate-300 text-slate-600 hover:border-[#168C8C] hover:text-[#168C8C] transition">
        + Tambah Baris
    </button>
</div>

<template id="item-row-template">
    <div class="item-row grid grid-cols-1 md:grid-cols-12 gap-2 items-start rounded-xl border border-slate-200 bg-slate-50/60 p-2">
        <div class="md:col-span-5">
            <select name="items[__INDEX__][kode_barang]" required onchange="fillItemPrice(this)"
                    class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:border-[#168C8C] focus:ring-[#168C8C]">
                <option value="">-- Pilih Barang --</option>
                @foreach($barangs as $b)
                    <option value="{{ $b->kode_barang }}" data-harga="{{ (float) $b->harga_barang }}">
                        {{ $b->kode_barang }} — {{ $b->nama_barang }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <input type="number" name="items[__INDEX__][jumlah]" min="1" value="1" required
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#168C8C] focus:ring-[#168C8C]">
        </div>
        <div class="md:col-span-3">
            <input type="number" name="items[__INDEX__][harga_satuan]" min="0" step="0.01" value="0" required
                   class="item-price w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#168C8C] focus:ring-[#168C8C]">
        </div>
        <div class="md:col-span-2">
            <button type="button" onclick="this.closest('.item-row').remove()"
                    class="w-full text-xs font-semibold px-3 py-2 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition">
                Hapus
            </button>
        </div>
    </div>
</template>

@once
    @push('scripts')
        <script>
            let itemIndex = 0;

            function addItemRow() {
                const template = document.getElementById('item-row-template');
                if (!template) return;

                const html = template.innerHTML.replaceAll('__INDEX__', itemIndex++);
                const wrapper = document.createElement('div');
                wrapper.innerHTML = html.trim();
                document.getElementById('item-rows').appendChild(wrapper.firstElementChild);
            }

            function fillItemPrice(select) {
                const option = select.options[select.selectedIndex];
                const price = option ? option.dataset.harga : null;
                const row = select.closest('.item-row');

                if (row && price !== undefined && price !== null) {
                    row.querySelector('.item-price').value = price;
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                if (document.getElementById('item-rows')) {
                    addItemRow();
                }
            });
        </script>
    @endpush
@endonce
