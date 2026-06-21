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
        if ($pop && $pop->frame_size === 'A5' && $pop->layout_type === 'was_is_price') {
            $this->activePreviewPop = $pop->toArray();
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
            ->where('layout_type', 'was_is_price')
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
     
     <style x-text="'@media print { @page { size: 148mm 105mm landscape; margin: 0; } }'"></style>
     
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

        .coret-diagonal-preview {
            position: relative;
            display: inline-flex;
        }
        .coret-diagonal-preview::after {
            content: "";
            position: absolute;
            left: -5%;
            right: -5%;
            top: 48%;
            height: 4px;
            background-color: #000000 !important;
            transform: rotate(-12deg);
            z-index: 1;
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
            margin-top: 0px;
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
            margin-top: 2px;
            margin-bottom: 2px;
        }
        
        /* old price styling */
        .pop-card-a5 .old-price-rp-a5 {
            font-size: 18pt !important;
            font-weight: 400 !important;
            color: #000000 !important;
            margin-top: 6px;
            margin-right: 2px;
            line-height: 1;
        }
        .pop-card-a5 .old-price-base-a5 {
            font-size: 72pt !important;
            font-weight: 700 !important;
            line-height: 0.8;
            letter-spacing: -1px;
        }
        .pop-card-a5 .old-price-suffix-a5 {
            font-size: 36pt !important;
            font-weight: 700 !important;
            line-height: 0.8;
            margin-top: 1px;
        }
        
        /* promo price styling */
        .pop-card-a5 .promo-price-rp-a5 {
            font-size: 18pt !important;
            font-weight: 400 !important;
            color: #000000 !important;
            margin-top: 8px;
            margin-right: 2px;
            line-height: 1;
        }
        .pop-card-a5 .promo-price-base-a5 {
            font-size: 96pt !important;
            font-weight: 700 !important;
            line-height: 0.8;
            letter-spacing: -2px;
        }
        .pop-card-a5 .promo-price-suffix-a5 {
            font-size: 56pt !important;
            font-weight: 700 !important;
            line-height: 0.8;
            margin-top: 1px;
        }
        
        @media print {
            body, html {
                margin: 0 !important;
                padding: 0 !important;
                width: 100vw !important;
                height: 100vh !important;
                background: white !important;
                overflow: hidden !important;
            }
            .no-print {
                display: none !important;
            }
            .print-area-wrapper-modal {
                position: fixed !important;
                top: 0 !important;
                left: 0 !important;
                width: 100vw !important;
                height: 100vh !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                background: white !important;
                z-index: 99999 !important;
            }
            .pop-card-preview {
                box-shadow: none !important;
                border: none !important;
                margin: 0 auto !important;
                padding: 0px !important;
            }
            .print-card-item-modal {
                page-break-after: always;
                page-break-inside: avoid;
            }
        }
     </style>

     <!-- Preview Modal Dialog Card -->
     <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden max-w-4xl w-full flex flex-col p-6 space-y-6"
          @click.away="open = false">
          
          <!-- Modal Header -->
          <div class="flex justify-between items-center border-b border-slate-100 pb-3">
              <div>
                  <h3 class="text-base font-extrabold text-slate-900">Pratinjau Desain POP A5 - Harga Coret</h3>
                  <p class="text-xs text-slate-500 font-medium">Ukuran frame cetak: <span class="font-bold text-indigo-600 uppercase">A5 (Print A6 Landscape)</span></p>
              </div>
              <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 transition">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
              </button>
          </div>

          <!-- Modal Content -->
          <div class="flex justify-center items-center py-6 bg-slate-50 rounded-xl border border-dashed border-slate-200 overflow-auto max-h-[420px]">
              @if($activePreviewPop)
                  <div class="pop-card-preview bg-white shadow-lg border border-slate-300 relative transition-all duration-300 flex flex-col justify-between overflow-hidden pop-card-a5"
                       style="width: 148mm; height: 105mm;">
                       
                       <!-- Header Banner -->
                        <div class="header-banner-a5" style="background-color:#dc2626;color:white;text-align:center;text-transform:uppercase;display:flex;align-items:center;justify-content:center;margin:12px 12px 0 12px;height:75px;min-height:75px;flex-shrink:0;box-sizing:border-box;padding:0 10px;">
                            <span style="font-size:40pt;font-weight:700;line-height:1;letter-spacing:-0.5px;color:white;font-family:'Arial Narrow',Arial,sans-serif;">{{ $activePreviewPop['header_text'] ?: 'HARGA SPESIAL' }}</span>
                        </div>

                        <!-- Content Body -->
                        <div class="flex-grow flex flex-col justify-between leading-none" style="padding:8px 18px 0px 18px;">
                            <!-- Brand Block -->
                            <div style="text-align:center;">
                                <div style="font-size:40pt;font-weight:700;text-transform:uppercase;color:black;line-height:1;letter-spacing:-0.5px;font-family:'Arial Narrow',Arial,sans-serif;margin-top:-5px;">{{ $activePreviewPop['brand_name'] }}</div>
                            </div>

                            <!-- Price Area -->
                            <div style="display:flex;align-items:center;justify-content:center;flex-grow:1;">
                                @php
                                    $promoParts = $this->formatPriceStatic($activePreviewPop['primary_price']);
                                    $oldParts = $this->formatPriceStatic($activePreviewPop['secondary_price']);
                                @endphp
                                <div style="display:flex;flex-direction:column;align-items:center;">
                                    <!-- Was Price (Harga Coret) -->
                                    <div style="display:flex;align-items:flex-start;position:relative;">
                                        <span style="font-size:18pt;font-weight:400;color:#000;margin-top:8px;margin-right:2px;line-height:1;font-family:'Arial Narrow',Arial,sans-serif;">Rp</span>
                                        <div class="coret-diagonal-preview" style="display:inline-flex;align-items:flex-start;color:#dc2626;font-weight:700;">
                                            <span style="font-size:72pt;font-weight:700;line-height:0.8;letter-spacing:-1px;font-family:'Arial Narrow',Arial,sans-serif;">{{ $oldParts['base'] }}</span>
                                            <span style="font-size:36pt;font-weight:700;line-height:0.8;margin-top:2px;font-family:'Arial Narrow',Arial,sans-serif;">{{ $oldParts['suffix'] }}</span>
                                        </div>
                                    </div>
                                    <!-- Promo Price (Harga Baru) -->
                                    <div style="display:flex;align-items:flex-start;margin-top:-10px;">
                                        <span style="font-size:20pt;font-weight:400;color:#000;margin-top:8px;margin-right:2px;line-height:1;font-family:'Arial Narrow',Arial,sans-serif;">Rp</span>
                                        <div style="display:flex;align-items:flex-start;color:#dc2626;font-weight:700;">
                                            <span style="font-size:96pt;font-weight:700;line-height:0.8;letter-spacing:-2px;font-family:'Arial Narrow',Arial,sans-serif;">{{ $promoParts['base'] }}</span>
                                            <span style="font-size:56pt;font-weight:700;line-height:0.8;margin-top:2px;font-family:'Arial Narrow',Arial,sans-serif;">{{ $promoParts['suffix'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                         </div>
                         <!-- Footer Image -->
                         <div style="text-align:center;padding-bottom:8px;padding-top:4px;line-height:0;flex-shrink:0;">
                             <img src="{{ asset('images/Picture2.bmp') }}" alt="Footer Logo" style="max-height:18px;width:auto;display:inline-block;object-fit:contain;">
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
                      @click="window.printA5WasIsPrice()"
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
window.printA5WasIsPrice = function() {
    var area = document.getElementById('pop-print-area-a5wip');
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
        + '.coret-diagonal-preview { position: relative; display: inline-flex; }'
        + '.coret-diagonal-preview::after { content: ""; position: absolute; left: -5%; right: -5%; top: 48%; height: 4px; background-color: #000000 !important; transform: rotate(-12deg); z-index: 1; }'
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
<div id="pop-print-area-a5wip" style="position:absolute;left:-99999px;top:0;width:297mm;overflow:hidden;">
    @php
        $chunks = array_chunk($previewQueue, 4);
    @endphp

    @foreach($chunks as $chunkIndex => $chunk)
        <div style="width:297mm;height:210mm;display:grid;grid-template-columns:148mm 149mm;grid-template-rows:105mm 105mm;box-sizing:border-box;{{ !$loop->last ? 'page-break-after:always;break-after:page;' : '' }}">
            @foreach($chunk as $pq)
                @php
                    $promoParts = $this->formatPriceStatic($pq['primary_price']);
                    $oldParts = $this->formatPriceStatic($pq['secondary_price']);
                @endphp
                <div style="width:148mm;height:105mm;box-sizing:border-box;font-family:'Arial Narrow',Arial,sans-serif;overflow:hidden;display:flex;flex-direction:column;justify-content:space-between;background-color:white;color:black;border:1px solid #000;padding: 0px;">
                    
                    <!-- Header Banner -->
                    <div style="background-color:#dc2626;color:white;text-align:center;text-transform:uppercase;display:flex;align-items:center;justify-content:center;margin:12px 12px 0 12px;height:75px;min-height:75px;flex-shrink:0;box-sizing:border-box;padding:0 10px;">
                        <span style="font-size:40pt;font-weight:700;line-height:1;letter-spacing:-0.5px;color:white;font-family:'Arial Narrow',Arial,sans-serif;">{{ $pq['header_text'] ?: 'HARGA SPESIAL' }}</span>
                    </div>

                    <!-- Content Body -->
                    <div style="flex-grow:1;display:flex;flex-direction:column;justify-content:space-between;padding:8px 18px 0px 18px;line-height:none;">
                        <!-- Brand Block -->
                        <div style="text-align:center;">
                            <div style="font-size:40pt;font-weight:700;text-transform:uppercase;color:black;line-height:1;letter-spacing:-0.5px;font-family:'Arial Narrow',Arial,sans-serif;margin-top:-5px;">{{ $pq['brand_name'] }}</div>
                        </div>

                        <!-- Price Area -->
                        <div style="display:flex;align-items:center;justify-content:center;flex-grow:1;">
                            <div style="display:flex;flex-direction:column;align-items:center;">
                                <!-- Was Price (Harga Coret) -->
                                <div style="display:flex;align-items:flex-start;position:relative;">
                                    <span style="font-size:18pt;font-weight:400;color:#000;margin-top:8px;margin-right:2px;line-height:1;font-family:'Arial Narrow',Arial,sans-serif;">Rp</span>
                                    <div class="coret-diagonal-preview" style="display:inline-flex;align-items:flex-start;color:#dc2626;font-weight:700;">
                                        <span style="font-size:72pt;font-weight:700;line-height:0.8;letter-spacing:-1px;font-family:'Arial Narrow',Arial,sans-serif;">{{ $oldParts['base'] }}</span>
                                        <span style="font-size:36pt;font-weight:700;line-height:0.8;margin-top:2px;font-family:'Arial Narrow',Arial,sans-serif;">{{ $oldParts['suffix'] }}</span>
                                    </div>
                                </div>
                                <!-- Promo Price (Harga Baru) -->
                                <div style="display:flex;align-items:flex-start;margin-top:-10px;">
                                    <span style="font-size:20pt;font-weight:400;color:#000;margin-top:8px;margin-right:2px;line-height:1;font-family:'Arial Narrow',Arial,sans-serif;">Rp</span>
                                    <div style="display:flex;align-items:flex-start;color:#dc2626;font-weight:700;">
                                        <span style="font-size:96pt;font-weight:700;line-height:0.8;letter-spacing:-2px;font-family:'Arial Narrow',Arial,sans-serif;">{{ $promoParts['base'] }}</span>
                                        <span style="font-size:56pt;font-weight:700;line-height:0.8;margin-top:2px;font-family:'Arial Narrow',Arial,sans-serif;">{{ $promoParts['suffix'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Image -->
                    <div style="text-align:center;padding-bottom:8px;padding-top:4px;line-height:0;flex-shrink:0;">
                        <img src="{{ asset('images/Picture2.bmp') }}" alt="" style="max-height:18px;width:auto;display:inline-block;object-fit:contain;">
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
</div>
