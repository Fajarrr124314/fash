<?php
 
use App\Models\Pop;
use Livewire\Component;
 
new class extends Component
{
    public $pops = [];
    public $selectedIds = [];
    public $allChecked = false;
    public $search = '';
    
    // Form State
    public $showForm = false;
    public $formTitle = 'Tambah POP A3 Discount Percent';
    public $popId = null;
    
    // Form fields
    public $brandName = '';
    public $productDesc = '';
    public $qtyPrint = 1;
    public $unit = 'PCS';
    public $headerText = 'DISKON';
    
    // Discount layout specific fields
    public $discountPercent = '50';
    public $discountPercent2 = '';
    public $isDoubleDiscount = false;
    public $primaryPrice = '';
    public $secondaryPrice = '';
    public $showDescription = true;
    public $showStartingFrom = false;
 
    public function mount()
    {
        $this->loadPops();
    }
 
    public function loadPops()
    {
        $query = Pop::where('frame_size', 'A3')->where('layout_type', 'discount_percent');
        if ($this->search) {
            $query->where(function($q) {
                $q->where('brand_name', 'like', '%'.$this->search.'%')
                  ->orWhere('product_desc', 'like', '%'.$this->search.'%');
            });
        }
        $this->pops = $query->orderBy('created_at', 'desc')->get()->toArray();
    }
 
    public function updatedSearch()
    {
        $this->loadPops();
    }
 
    public function toggleAll()
    {
        if ($this->allChecked) {
            $this->selectedIds = array_map(fn($item) => (string)$item['id'], $this->pops);
        } else {
            $this->selectedIds = [];
        }
    }
 
    public function openAddForm()
    {
        $this->resetForm();
        $this->formTitle = 'Tambah POP A3 Discount Percent';
        $this->showForm = true;
    }
 
    public function editPop($id)
    {
        $this->resetForm();
        $pop = Pop::find($id);
        if ($pop) {
            $this->popId = $pop->id;
            $this->brandName = $pop->brand_name;
            $this->productDesc = $pop->product_desc;
            $this->qtyPrint = $pop->qty_print;
            $this->unit = $pop->unit;
            $this->headerText = $pop->header_text;
            $this->primaryPrice = $pop->primary_price;
            $this->secondaryPrice = $pop->secondary_price;
            $this->showStartingFrom = (bool)$pop->show_starting_from;
            
            $add = $pop->additional_data ?? [];
            $this->discountPercent = $add['discount_percent'] ?? '50';
            $this->discountPercent2 = $add['discount_percent_2'] ?? '';
            $this->isDoubleDiscount = (bool)($add['is_double_discount'] ?? false);
            $this->showDescription = isset($add['show_description']) ? (bool)$add['show_description'] : true;
            
            $this->formTitle = 'Edit POP A3 Discount Percent';
            $this->showForm = true;
        }
    }
 
    public function resetForm()
    {
        $this->popId = null;
        $this->brandName = '';
        $this->productDesc = '';
        $this->qtyPrint = 1;
        $this->unit = 'PCS';
        $this->headerText = 'DISKON';
        $this->discountPercent = '50';
        $this->discountPercent2 = '';
        $this->isDoubleDiscount = false;
        $this->primaryPrice = '';
        $this->secondaryPrice = '';
        $this->showDescription = true;
        $this->showStartingFrom = false;
    }
 
    public function save()
    {
        $this->validate([
            'brandName' => 'required|string',
            'discountPercent' => 'required|string',
            'primaryPrice' => 'required|string',
            'qtyPrint' => 'required|integer|min:1',
            'unit' => 'required|string',
        ]);
 
        $name = $this->brandName . ' - ' . ($this->productDesc ?: 'POP');
        $sku = $this->popId ? Pop::find($this->popId)->sku : rand(10000000, 99999999);
 
        $data = [
            'sku' => $sku,
            'name' => $name,
            'frame_size' => 'A3',
            'layout_type' => 'discount_percent',
            'header_text' => $this->headerText,
            'brand_name' => $this->brandName,
            'product_desc' => $this->productDesc,
            'primary_price' => $this->primaryPrice,
            'secondary_price' => $this->secondaryPrice,
            'qty_print' => $this->qtyPrint,
            'unit' => $this->unit,
            'additional_data' => [
                'discount_percent' => $this->discountPercent,
                'discount_percent_2' => $this->discountPercent2,
                'is_double_discount' => $this->isDoubleDiscount,
                'show_description' => $this->showDescription,
            ],
            'show_starting_from' => $this->showStartingFrom,
        ];
 
        if ($this->popId) {
            Pop::find($this->popId)->update($data);
            $msg = 'POP A3 Discount Percent berhasil diperbarui!';
        } else {
            Pop::create($data);
            $msg = 'POP A3 Discount Percent berhasil ditambahkan!';
        }
 
        $this->showForm = false;
        $this->loadPops();
        $this->selectedIds = [];
        $this->allChecked = false;
        $this->dispatch('notify', ['type' => 'success', 'message' => $msg]);
    }
 
    public function deletePop($id)
    {
        Pop::destroy($id);
        $this->loadPops();
        $this->selectedIds = array_values(array_filter($this->selectedIds, fn($val) => $val != $id));
        $this->dispatch('notify', ['type' => 'warning', 'message' => 'POP berhasil dihapus.']);
    }
 
    public function bulkDelete()
    {
        if (count($this->selectedIds) === 0) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'Tidak ada item yang dipilih!']);
            return;
        }
        Pop::whereIn('id', $this->selectedIds)->delete();
        $this->selectedIds = [];
        $this->allChecked = false;
        $this->loadPops();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'POP terpilih berhasil dihapus.']);
    }
 
    public function incrementQty($id)
    {
        $pop = Pop::find($id);
        if ($pop) {
            $pop->increment('qty_print');
            $this->loadPops();
        }
    }
 
    public function decrementQty($id)
    {
        $pop = Pop::find($id);
        if ($pop && $pop->qty_print > 1) {
            $pop->decrement('qty_print');
            $this->loadPops();
        }
    }
 
    public function previewSingle($id)
    {
        $this->dispatch('preview-single', $id);
    }
 
    public function bulkPrint()
    {
        if (count($this->selectedIds) === 0) {
            $this->dispatch('notify', ['type' => 'warning', 'message' => 'Pilih item terlebih dahulu untuk mencetak!']);
            return;
        }
        $this->dispatch('preview-bulk', $this->selectedIds);
    }
};
?>
 
<div class="space-y-6">
    <!-- Header Controls -->
    <div class="no-print flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <button type="button" 
                    wire:click="bulkPrint"
                    class="bg-[#6366f1] hover:bg-[#4f46e5] text-white font-bold py-2 px-5 rounded-lg text-xs transition duration-150 flex items-center gap-1.5 shadow-sm active:scale-[0.98]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print Selected
            </button>
            
            <button type="button" 
                    @click="$dispatch('request-confirm', { title: 'Hapus Data Terpilih', message: 'Semua POP yang dipilih akan dihapus permanen. Apakah Anda yakin?', icon: 'delete', confirmLabel: 'Ya, Hapus Semua', confirmClass: 'bg-red-600 hover:bg-red-700', action: () => $wire.bulkDelete() })"
                    class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-5 rounded-lg text-xs transition duration-150 flex items-center gap-1.5 shadow-sm active:scale-[0.98]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete Selected
            </button>
        </div>
    </div>
 
    <!-- TABLE LIST BLOCK (Solid White) -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-md overflow-hidden flex flex-col w-full">
        <!-- Table Header -->
        <div class="px-6 py-5 border-b border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h3 class="text-base font-extrabold text-slate-800">POP A3 - Diskon %</h3>
                <p class="text-xs text-slate-400 font-semibold uppercase mt-0.5">Daftar SKU / Diskon (A3)</p>
            </div>
            
            <button type="button" 
                    wire:click="openAddForm"
                    class="bg-[#6366f1] hover:bg-[#4f46e5] text-white font-bold py-2 px-5 rounded-lg text-xs transition duration-150 flex items-center gap-1 shadow-sm active:scale-[0.98]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                Tambah POP A3
            </button>
        </div>
 
        <!-- Table Filters & Search -->
        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                <span>Show</span>
                <select class="bg-white border border-slate-200 rounded-lg px-2.5 py-1 text-slate-700 focus:outline-none">
                    <option>10</option>
                </select>
                <span>entries</span>
            </div>
            
            <div class="w-full md:w-64 relative">
                <span class="absolute left-3.5 top-2.5 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <input type="text" 
                       wire:model.live="search" 
                       placeholder="Cari brand / deskripsi..." 
                       class="w-full bg-white border border-slate-200 rounded-lg pl-9 pr-4 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 font-semibold animate-none">
            </div>
        </div>
 
        <!-- Responsive Table -->
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="py-4 px-5 w-12 text-center">
                            <input type="checkbox" wire:model.live="allChecked" wire:click="toggleAll" class="rounded border-slate-300 text-indigo-600 focus:ring-0">
                        </th>
                        <th class="py-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center w-[120px]">Actions</th>
                        <th class="py-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center w-[130px]">Qty Print</th>
                        <th class="py-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Merek & Deskripsi</th>
                        <th class="py-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">Diskon %</th>
                        <th class="py-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">Harga Promo</th>
                        <th class="py-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center">Harga Coret</th>
                        <th class="py-4 px-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center w-20">Unit</th>
                        <th class="py-4 px-5 text-[10px] font-bold text-slate-400 uppercase tracking-wider text-right">Created At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @if(count($pops) === 0)
                        <tr>
                            <td colspan="9" class="py-8 px-6 text-center text-slate-400 font-medium">
                                Tidak ada data POP ditemukan.
                            </td>
                        </tr>
                    @else
                        @foreach($pops as $pop)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-5 text-center">
                                    <input type="checkbox" wire:model.live="selectedIds" value="{{ $pop['id'] }}" class="rounded border-slate-300 text-indigo-600 focus:ring-0">
                                </td>
                                
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Preview -->
                                        <button type="button" 
                                                wire:click="previewSingle({{ $pop['id'] }})"
                                                class="text-indigo-600 hover:text-indigo-800 transition p-1 hover:bg-slate-100 rounded"
                                                title="Pratinjau POP">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        
                                        <!-- Edit -->
                                        <button type="button" 
                                                wire:click="editPop({{ $pop['id'] }})"
                                                class="text-amber-600 hover:text-amber-800 transition p-1 hover:bg-slate-100 rounded"
                                                title="Edit POP">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        
                                        <!-- Delete -->
                                        <button type="button" 
                                                @click="$dispatch('request-confirm', { title: 'Hapus POP', message: 'Data POP ini akan dihapus permanen. Apakah Anda yakin?', icon: 'delete', confirmLabel: 'Ya, Hapus', confirmClass: 'bg-red-600 hover:bg-red-700', action: () => $wire.deletePop({{ $pop['id'] }}) })"
                                                class="text-red-500 hover:text-red-700 transition p-1 hover:bg-slate-100 rounded"
                                                title="Hapus POP">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                
                                <td class="py-3 px-4 text-center">
                                    <div class="inline-flex items-center border border-slate-200 rounded-lg overflow-hidden bg-white">
                                        <button type="button" 
                                                wire:click="decrementQty({{ $pop['id'] }})"
                                                class="px-2.5 py-1 bg-slate-50 hover:bg-slate-100 text-slate-500 font-bold border-r border-slate-200 transition">-</button>
                                        <span class="px-3.5 py-1 text-slate-800 font-semibold min-w-8 text-center">{{ $pop['qty_print'] }}</span>
                                        <button type="button" 
                                                wire:click="incrementQty({{ $pop['id'] }})"
                                                class="px-2.5 py-1 bg-slate-50 hover:bg-slate-100 text-slate-500 font-bold border-l border-slate-200 transition">+</button>
                                    </div>
                                </td>
                                
                                <td class="py-3 px-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-900 uppercase text-[13px]">{{ $pop['brand_name'] }}</span>
                                        <span class="text-[10px] text-slate-400 font-medium tracking-wide uppercase">{{ $pop['product_desc'] ?: '-' }}</span>
                                    </div>
                                </td>
                                
                                <td class="py-3 px-4 text-center font-bold text-indigo-600 text-[13px]">
                                    {{ $pop['additional_data']['discount_percent'] ?? '0' }}%
                                    @if(!empty($pop['additional_data']['is_double_discount']) && !empty($pop['additional_data']['discount_percent_2']))
                                        + {{ $pop['additional_data']['discount_percent_2'] }}%
                                    @endif
                                </td>
 
                                <td class="py-3 px-4 text-center text-red-600 font-bold">
                                    {{ $pop['primary_price'] ? 'Rp '.number_format((int)preg_replace('/[^0-9]/', '', $pop['primary_price']), 0, ',', '.') : '-' }}
                                </td>
 
                                <td class="py-3 px-4 text-center text-slate-500 font-semibold line-through">
                                    {{ $pop['secondary_price'] ? 'Rp '.number_format((int)preg_replace('/[^0-9]/', '', $pop['secondary_price']), 0, ',', '.') : '-' }}
                                </td>
                                
                                <td class="py-3 px-4 text-center text-slate-500 font-semibold">
                                    {{ $pop['unit'] }}
                                </td>
                                
                                <td class="py-3 px-5 text-right text-slate-400 font-medium">
                                    {{ date('d M Y H:i', strtotime($pop['created_at'])) }}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
 
    <!-- FLOATING POP FORM MODAL (Solid White) -->
    <div x-data="{ open: @entangle('showForm') }"
         x-show="open"
         class="fixed inset-0 z-40 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/50"
         style="display: none;"
         x-transition>
         
         <div class="bg-white border border-slate-200 rounded-2xl shadow-xl max-w-4xl w-full z-50 overflow-hidden"
              @click.away="open = false">
              
              <!-- Modal Header -->
              <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex justify-between items-center">
                  <h3 class="text-base font-extrabold text-slate-800">{{ $formTitle }}</h3>
                  <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 transition">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                  </button>
              </div>
 
              <!-- Form Form -->
              <form wire:submit.prevent="save" class="p-6 space-y-6">
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                      
                      <!-- Col 1: Brand Info -->
                      <div class="space-y-4">
                          <div>
                              <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Merek / Brand</label>
                              <input type="text" wire:model="brandName" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm uppercase focus:border-indigo-500 focus:outline-none transition font-semibold">
                              @error('brandName')
                                  <span class="text-red-500 text-xs mt-1 block font-semibold">{{ $message }}</span>
                              @enderror
                          </div>
                          
                          <div>
                              <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Deskripsi Produk</label>
                              <input type="text" wire:model="productDesc" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm uppercase focus:border-indigo-500 focus:outline-none transition font-semibold">
                          </div>
 
                          <div class="grid grid-cols-2 gap-3">
                              <div>
                                  <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Unit</label>
                                  <input type="text" wire:model="unit" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none transition font-semibold">
                                  @error('unit')
                                      <span class="text-red-500 text-xs mt-1 block font-semibold">{{ $message }}</span>
                                  @enderror
                              </div>
                              <div>
                                  <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Qty Cetak</label>
                                  <input type="number" min="1" wire:model="qtyPrint" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none transition font-semibold">
                                  @error('qtyPrint')
                                      <span class="text-red-500 text-xs mt-1 block font-semibold">{{ $message }}</span>
                                  @enderror
                              </div>
                          </div>
                      </div>
 
                      <!-- Col 2: Layout & Toggles -->
                      <div class="space-y-4">
                          <div>
                              <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Banner Header</label>
                              <input type="text" wire:model="headerText" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none transition font-semibold">
                          </div>
 
                           <div class="border-t border-slate-100 pt-3 space-y-3">
                               <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Opsi Tampilan</label>
 
                               <label class="inline-flex items-center gap-3 cursor-pointer select-none w-full">
                                   <div class="relative">
                                       <input type="checkbox" wire:model.live="showDescription" id="dpShowDescription" class="sr-only peer">
                                       <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                   </div>
                                   <div>
                                       <span class="text-xs font-bold text-slate-700">Tampilkan Deskripsi</span>
                                    </div>
                               </label>
 
                               <label class="inline-flex items-center gap-3 cursor-pointer select-none w-full">
                                   <div class="relative">
                                       <input type="checkbox" wire:model.live="showStartingFrom" id="dpShowStartingFrom" class="sr-only peer">
                                       <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                   </div>
                                   <div>
                                       <span class="text-xs font-bold text-slate-700">Tampilkan "Mulai Dari"</span>
                                   </div>
                               </label>
 
                               <label class="inline-flex items-center gap-3 cursor-pointer select-none w-full">
                                   <div class="relative">
                                       <input type="checkbox" wire:model.live="isDoubleDiscount" id="dpIsDoubleDiscount" class="sr-only peer">
                                       <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-indigo-600"></div>
                                   </div>
                                   <div>
                                       <span class="text-xs font-bold text-slate-700">Aktifkan Diskon + (Double)</span>
                                   </div>
                               </label>
                           </div>
                      </div>
 
                      <!-- Col 3: Discount & Price Inputs -->
                      <div class="space-y-4">
                          <div class="grid grid-cols-2 gap-3">
                              <div>
                                  <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Diskon %</label>
                                  <input type="text" wire:model="discountPercent" placeholder="50" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-indigo-500 font-bold">
                                  @error('discountPercent')
                                      <span class="text-red-500 text-xs mt-1 block font-semibold">{{ $message }}</span>
                                  @enderror
                              </div>
 
                              <div x-show="$wire.isDoubleDiscount">
                                  <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Diskon Kedua %</label>
                                  <input type="text" wire:model="discountPercent2" placeholder="20" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-indigo-500 font-bold">
                                  @error('discountPercent2')
                                      <span class="text-red-500 text-xs mt-1 block font-semibold">{{ $message }}</span>
                                  @enderror
                              </div>
                          </div>
 
                          <div>
                              <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Harga Promo (Rp)</label>
                              <input type="text" wire:model="primaryPrice" placeholder="295000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-indigo-500 font-bold text-[#dc2626]">
                              @error('primaryPrice')
                                  <span class="text-red-500 text-xs mt-1 block font-semibold">{{ $message }}</span>
                              @enderror
                          </div>
 
                          <div>
                              <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Harga Asli / Coret (Rp)</label>
                              <input type="text" wire:model="secondaryPrice" placeholder="589900" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-indigo-500 font-bold text-slate-600">
                              @error('secondaryPrice')
                                  <span class="text-red-500 text-xs mt-1 block font-semibold">{{ $message }}</span>
                              @enderror
                          </div>
                      </div>
 
                  </div>
 
                  <!-- Footer Buttons -->
                  <div class="flex justify-end gap-3 border-t border-slate-100 pt-4 mt-6">
                      <button type="button" @click="open = false" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 px-5 rounded-xl text-xs transition duration-150">
                          Batal
                      </button>
                      <button type="submit" class="bg-[#6366f1] hover:bg-[#4f46e5] text-white font-bold py-2.5 px-6 rounded-xl text-xs transition duration-150 shadow-sm">
                          Simpan Ke Database
                      </button>
                  </div>
              </form>
         </div>
    </div>
 
    <!-- NESTED FEATURE PREVIEW MODAL -->
    <livewire:a3.discount-percent.preview />
</div>
