<?php

use App\Models\Pop;
use Livewire\Component;

new class extends Component
{
    public $showModal = false;
    public $previewQueue = [];
    public $frameSize = 'A5';
    public $activePreviewPop = null;

    protected $listeners = [
        'preview-single' => 'handlePreviewSingle',
        'preview-bulk' => 'handlePreviewBulk'
    ];

    public function handlePreviewSingle($id)
    {
        $pop = Pop::find($id);
        if ($pop && $pop->frame_size === 'A5' && $pop->layout_type === 'single_price') {
            $this->activePreviewPop = $pop->toArray();
            // Isi queue sesuai qty_print agar layout 4-up bekerja
            $qty = max(1, (int)($pop->qty_print ?? 1));
            $this->previewQueue = array_fill(0, $qty, $this->activePreviewPop);
            $this->frameSize = $pop->frame_size;
            $this->showModal = true;
        }
    }

    public function handlePreviewBulk($ids)
    {
        if (count($ids) === 0) return;
        $items = Pop::whereIn('id', $ids)
            ->where('frame_size', 'A5')
            ->where('layout_type', 'single_price')
            ->get()
            ->toArray();
            
        $this->previewQueue = [];
        foreach ($items as $item) {
            $qty = $item['qty_print'] ?? 1;
            for ($i = 0; $i < $qty; $i++) {
                $this->previewQueue[] = $item;
            }
        }
        if (count($this->previewQueue) > 0) {
            $this->frameSize = $items[0]['frame_size'];
            $this->activePreviewPop = $items[0];
            $this->showModal = true;
        }
    }

    public function formatPriceStatic($val)
    {
        if (!$val) return ['base' => '', 'suffix' => ''];
        $clean = preg_replace('/[^0-9]/', '', $val);
        if (strlen($clean) === 0) return ['base' => '', 'suffix' => ''];
        $num = (int)$clean;
        if ($num < 1000) return ['base' => (string)$num, 'suffix' => ''];
        
        $baseStr = substr($clean, 0, -3);
        $suffixStr = substr($clean, -3);
        $formattedBase = number_format((int)$baseStr, 0, ',', '.');
        return [
            'base' => $formattedBase . '.',
            'suffix' => $suffixStr
        ];
    }
};
?>

<div>
<div x-data="{ open: @entangle('showModal'), frameSize: @entangle('frameSize') }"
     x-show="open"
     x-on:keydown.escape.window="open = false"
     class="fixed inset-0 z-40 overflow-y-auto no-print flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
     style="display: none;"
     x-transition>
     
     {{-- page size is now set in the dedicated print style block below the modal --}}
     
     <style>
        .pop-card-preview, .pop-card-preview * {
            font-family: 'Arial Narrow', 'Archivo Narrow', Arial, sans-serif !important;
        }
        
        .pop-card-preview .font-bold, 
        .pop-card-preview [class*="font-bold"],
        .pop-card-preview [class*="font-extrabold"],
        .pop-card-preview [class*="font-black"] {
            font-weight: 700 !important;
        }
        .pop-card-preview .font-semibold {
            font-weight: 600 !important;
        }
        .pop-card-preview .font-medium {
            font-weight: 500 !important;
        }
        .pop-card-preview .font-normal {
            font-weight: 400 !important;
        }
        
        .pop-card-a5 {
            width: 148mm;
            height: 105mm;
            box-sizing: border-box;
            padding: 0 !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background-color: white !important;
            color: black !important;
            overflow: hidden;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        .pop-card-a5 .header-banner-a5 {
            background-color: #dc2626 !important;
            color: white !important;
            text-align: center;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 12px 12px 0 12px;
            border-radius: 0px;
            height: 75px;
            box-sizing: border-box;
            padding: 0 10px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        
        .pop-card-a5 .header-banner-a5 span {
            font-size: 40pt !important;
            font-weight: 700 !important;
            line-height: 1;
            letter-spacing: -0.5px;
        }
        
        .pop-card-a5 .brand-name-a5 {
            font-size: 40pt !important;
            font-weight: 600 !important;
            text-transform: uppercase;
            color: black !important;
            line-height: 1;
            margin-top: -10px;
            letter-spacing: -0.5px;
            text-align: center;
        }
        
        .pop-card-a5 .product-desc-a5 {
            font-size: 18pt !important;
            font-weight: 400 !important;
            text-transform: uppercase;
            color: #000000ff !important;
            line-height: 1.2;
            margin-top: -5px;
            text-align: center;
        }
        
        .pop-card-a5 .price-area-a5 {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
            margin-top: 10px;
            margin-bottom: -10px;
            margin-top:-20px;
        }
        
        .pop-card-a5 .price-wrapper-a5 {
            display: flex;
            align-items: flex-start;
            color: #dc2626 !important;
            font-weight: 700 !important;
            line-height: 0.85;
            margin-top:-20px;
        }
        
        .pop-card-a5 .price-rp-a5 {
            font-size: 20pt !important;
            font-weight: 400 !important;
            color: #000000ff !important;
            margin-top: 0px;
            margin-right: 2px;
            line-height: 1;
        }
        
        .pop-card-a5 .price-base-a5 {
            font-size: 100pt !important;
            font-weight: 700 !important;
            letter-spacing: -2px;
            line-height: 0.8;
        }
        
        .pop-card-a5 .price-suffix-a5 {
            font-size: 72pt !important;
            font-weight: 700 !important;
            line-height: 0.8;
            margin-top: 2px;
        }
        
        /*mulai dari */
        .pop-card-a5 .starting-from-label-a5 {
            font-size: 15pt !important;
            font-weight: 300 !important;
            text-transform: uppercase;
            color: #000000ff !important;
            text-align: center;
            letter-spacing: 0.5px;
            line-height: 1.4;
            margin-bottom: 25px;
        }
        
        /* no print media here — handled by the global print style below */
     </style>

     <!-- Preview Modal Dialog Card -->
     <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden max-w-4xl w-full flex flex-col p-4 sm:p-6 space-y-4 sm:space-y-6 preview-modal-dialog"
          @click.away="open = false">
          
          <!-- Modal Header -->
          <div class="flex justify-between items-center border-b border-slate-100 pb-3">
              <div>
                  <h3 class="text-base font-extrabold text-slate-900">Pratinjau Desain POP A5 - Harga Tunggal</h3>
                  <p class="text-xs text-slate-500 font-medium">Ukuran frame cetak: <span class="font-bold text-indigo-600 uppercase">A5 (Print A6 Landscape)</span></p>
              </div>
              <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 transition">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
              </button>
          </div>

          <!-- Modal Content -->
          <div class="flex justify-center items-start py-4 sm:py-6 bg-slate-50 rounded-xl border border-dashed border-slate-200 preview-scroll-area" style="min-height:140px;">
              @if($activePreviewPop)
                  <div class="pop-card-preview bg-white shadow-lg border border-slate-300 relative transition-all duration-300 flex flex-col justify-between overflow-hidden pop-card-a5"
                       style="width: 148mm; height: 105mm;">
                       
                       <!-- Header Banner -->
                        <div class="header-banner-a5" style="background-color:#dc2626;color:white;text-align:center;text-transform:uppercase;display:flex;align-items:center;justify-content:center;margin:12px 12px 0 12px;height:75px;box-sizing:border-box;padding:0 10px;">
                            <span style="font-size:40pt;font-weight:700;line-height:1;letter-spacing:-0.5px;color:white;">{{ $activePreviewPop['header_text'] ?: 'HARGA SPESIAL' }}</span>
                        </div>

                       <!-- Content Body -->
                       <div class="flex-grow flex flex-col justify-between py-3 px-5 leading-none">
                           <!-- Brand Block -->
                           <div>
                               <div class="brand-name-a5">{{ $activePreviewPop['brand_name'] }}</div>
                               <div class="product-desc-a5">{{ $activePreviewPop['product_desc'] }}</div>
                           </div>

                           <!-- Price Area -->
                           <div class="price-area-a5">
                               @php
                                   $priceParts = $this->formatPriceStatic($activePreviewPop['primary_price']);
                               @endphp
                               <div class="flex flex-col items-center">
                                   @if(!empty($activePreviewPop['show_starting_from']))
                                       <div class="starting-from-label-a5">mulai dari</div>
                                   @endif
                                   <div class="price-wrapper-a5">
                                       <span class="price-rp-a5">Rp</span>
                                       <span class="price-base-a5">{{ $priceParts['base'] }}</span>
                                       <span class="price-suffix-a5">{{ $priceParts['suffix'] }}</span>
                                   </div>
                               </div>
                           </div>
                        <!-- Footer Image -->
                        <div style="text-align:center; padding-bottom: 5px; padding-top: 10px; line-height:0;">
                            <img src="{{ asset('images/Picture2.bmp') }}" alt="Footer Logo" style="max-height: 18px; width: auto; display: inline-block; object-fit: contain;">
                        </div>
                           <div class="h-2"></div>
                       </div>
                  </div>
              @endif
          </div>

          <!-- Modal Footer Controls -->
          <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
              <button type="button" @click="open = false" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 px-6 rounded-xl text-xs transition duration-150">
                  Tutup
              </button>
              <button type="button" 
                      @click="window.printA5SinglePrice()"
                      class="bg-[#6366f1] hover:bg-[#4f46e5] text-white font-extrabold py-2.5 px-6 rounded-xl text-xs transition duration-150 flex items-center gap-1.5 shadow-md shadow-indigo-100">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                  </svg>
                  Mulai Mencetak (Print)
              </button>
          </div>
     </div>
</div>

@script
<script>
window.printA5SinglePrice = function() {
    var area = document.getElementById('pop-print-area-a5sp');
    if (!area) { alert('Data print tidak ditemukan.'); return; }
    var html = area.innerHTML;
    if (!html.trim()) { alert('Tidak ada data untuk dicetak.'); return; }

    var win = window.open('', '_blank', 'width=1200,height=900');
    if (!win) { alert('Popup diblokir browser! Izinkan popup untuk domain ini.'); return; }
    win.document.open();
    win.document.write(
        '<!DOCTYPE html><html><head><meta charset="UTF-8">'
        + '<style>'
        + '@page { size: 297mm 210mm landscape; margin: 0; }'
        + '* { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; box-sizing: border-box; margin: 0; padding: 0; }'
        + 'body { background: white; font-family: \'Arial Narrow\', Arial, sans-serif; }'
        + '</style>'
        + '</head><body>' + html + '</body></html>'
    );
    win.document.close();
    win.focus();
    setTimeout(function() {
        win.print();
        setTimeout(function() { win.close(); }, 500);
    }, 400);
};
</script>
@endscript

<!-- ==================== PRINT DATA AREA (hidden off-screen) ==================== -->
<div id="pop-print-area-a5sp" style="position:absolute;left:-99999px;top:0;width:297mm;overflow:hidden;">
    @php
        $chunks = array_chunk($previewQueue, 4);
    @endphp

    @foreach($chunks as $chunkIndex => $chunk)
        <div style="width:297mm;height:210mm;display:grid;grid-template-columns:148mm 149mm;grid-template-rows:105mm 105mm;box-sizing:border-box;{{ !$loop->last ? 'page-break-after:always;break-after:page;' : '' }}">
            @foreach($chunk as $pq)
                @php $priceParts = $this->formatPriceStatic($pq['primary_price']); @endphp
                <div style="width:148mm;height:105mm;box-sizing:border-box;font-family:'Arial Narrow',Arial,sans-serif;overflow:hidden;display:flex;flex-direction:column;justify-content:space-between;background-color:white;color:black;border:1px solid #000;">

                    <!-- Header Banner -->
                    <div style="background-color:#dc2626;color:white;text-align:center;text-transform:uppercase;display:flex;align-items:center;justify-content:center;margin:12px 12px 0 12px;height:75px;box-sizing:border-box;padding:0 10px;">
                        <span style="font-size:40pt;font-weight:700;line-height:1;letter-spacing:-0.5px;font-family:'Arial Narrow',Arial,sans-serif;">{{ $pq['header_text'] ?: 'HARGA SPESIAL' }}</span>
                    </div>

                    <!-- Content Body -->
                    <div style="flex-grow:1;display:flex;flex-direction:column;justify-content:space-between;padding:12px 20px 8px 20px;">
                        <div>
                            <div style="font-size:40pt;font-weight:600;text-transform:uppercase;color:black;line-height:1;margin-top:-10px;letter-spacing:-0.5px;text-align:center;font-family:'Arial Narrow',Arial,sans-serif;">{{ $pq['brand_name'] }}</div>
                            <div style="font-size:18pt;font-weight:400;text-transform:uppercase;color:#000;line-height:1.2;margin-top:-5px;text-align:center;font-family:'Arial Narrow',Arial,sans-serif;">{{ $pq['product_desc'] }}</div>
                        </div>

                        <!-- Price Area -->
                        <div style="display:flex;align-items:center;justify-content:center;flex-grow:1;margin-top:-20px;margin-bottom:-10px;">
                            <div style="display:flex;flex-direction:column;align-items:center;">
                                @if(!empty($pq['show_starting_from']))
                                    <div style="font-size:15pt;font-weight:300;text-transform:uppercase;color:#000;text-align:center;letter-spacing:0.5px;line-height:1.4;margin-bottom:25px;font-family:'Arial Narrow',Arial,sans-serif;">mulai dari</div>
                                @endif
                                <div style="display:flex;align-items:flex-start;color:#dc2626;font-weight:700;line-height:0.85;margin-top:-20px;">
                                    <span style="font-size:20pt;font-weight:400;color:#000;margin-top:0;margin-right:2px;line-height:1;font-family:'Arial Narrow',Arial,sans-serif;">Rp</span>
                                    <span style="font-size:100pt;font-weight:700;letter-spacing:-2px;line-height:0.8;font-family:'Arial Narrow',Arial,sans-serif;">{{ $priceParts['base'] }}</span>
                                    <span style="font-size:72pt;font-weight:700;line-height:0.8;margin-top:2px;font-family:'Arial Narrow',Arial,sans-serif;">{{ $priceParts['suffix'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Image -->
                    <div style="text-align:center;padding-bottom:8px;padding-top:5px;line-height:0;">
                        <img src="{{ asset('images/Picture2.bmp') }}" alt="" style="max-height:18px;width:auto;display:inline-block;object-fit:contain;">
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
</div>
