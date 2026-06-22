<?php

use App\Models\Pop;
use Livewire\Component;

new class extends Component
{
    public $showModal = false;
    public $previewQueue = [];
    public $frameSize = 'A4';
    public $activePreviewPop = null;

    protected $listeners = [
        'preview-single' => 'handlePreviewSingle',
        'preview-bulk' => 'handlePreviewBulk'
    ];

    public function handlePreviewSingle($id)
    {
        $pop = Pop::find($id);
        if ($pop && $pop->frame_size === 'A4' && $pop->layout_type === 'double_item') {
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
            ->where('frame_size', 'A4')
            ->where('layout_type', 'double_item')
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
     
     <style x-text="'@media print { @page { size: A4 landscape; margin: 0; } }'"></style>
     
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
            display: inline-block;
        }
        .coret-diagonal-preview::after {
            content: "";
            position: absolute;
            left: -3%;
            right: -3%;
            top: 50%;
            height: 2px;
            background-color: #000000 !important;
            transform: rotate(-6deg);
        }
        
        .pop-card-a4 {
            width: 148mm;
            height: 210mm;
            box-sizing: border-box;
            padding: 0 !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background-color: white !important;
            color: black !important;
            overflow: hidden;
        }
        
        .pop-card-a4 .header-banner-a4 {
            background-color: #dc2626 !important;
            color: white !important;
            text-align: center;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 16px 16px 0 16px;
            border-radius: 0px;
            height: 75px;
            box-sizing: border-box;
            padding: 0 10px;
        }
        
        .pop-card-a4 .header-banner-a4 span {
            font-size: 46pt !important;
            font-weight: 700 !important;
            line-height: 1;
            letter-spacing: -0.5px;
        }
        
        .pop-card-a4 .brand-name-a4 {
            font-size: 46pt !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            color: black !important;
            line-height: 1;
            margin-top: 12px;
            letter-spacing: -0.5px;
            text-align: center;
        }

        .pop-card-a4 .price-area-a4 {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
            margin-top: 4px;
            margin-bottom: 4px;
        }
        
        .pop-card-a4 .double-container-a4 {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 0 20px;
            box-sizing: border-box;
        }
        
        .pop-card-a4 .double-row-a4 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        
        .pop-card-a4 .double-row-border-a4 {
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 10px;
        }
        
        .pop-card-a4 .double-left-a4 {
            text-align: left;
            display: flex;
            flex-direction: column;
        }
        
        .pop-card-a4 .double-name-a4 {
            font-size: 14pt !important;
            font-weight: 700 !important;
            color: black !important;
            text-transform: uppercase;
        }
        
        .pop-card-a4 .double-was-a4 {
            font-size: 11pt !important;
            color: #64748b !important;
            font-weight: 400;
            margin-top: 1px;
        }
        
        .pop-card-a4 .double-right-a4 {
            display: flex;
            align-items: flex-start;
            color: #dc2626 !important;
            font-weight: 700 !important;
        }
        
        .pop-card-a4 .double-rp-a4 {
            font-size: 11pt !important;
            font-weight: 400 !important;
            margin-top: 1px;
            margin-right: 1px;
            color: #000000 !important;
        }
        
        .pop-card-a4 .double-price-base-a4 {
            font-size: 26pt !important;
            font-weight: 700 !important;
            line-height: 0.8;
        }
        
        .pop-card-a4 .double-price-suffix-a4 {
            font-size: 14pt !important;
            font-weight: 700 !important;
            line-height: 0.8;
            margin-top: 1px;
        }
        
        @media print {
            body, html {
                margin: 0 !important;
                padding: 0 !important;
                width: 297mm !important;
                height: 210mm !important;
                background: white !important;
                overflow: hidden !important;
            }
            .no-print {
                display: none !important;
            }
            .print-page-a4 {
                width: 297mm !important;
                height: 210mm !important;
                display: flex !important;
                flex-direction: row !important;
                page-break-after: always !important;
                page-break-inside: avoid !important;
                box-sizing: border-box !important;
                background-color: white !important;
            }
            .print-page-a4 .pop-card-a4 {
                width: 148.5mm !important;
                height: 210mm !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
                box-sizing: border-box !important;
            }
            .print-page-a4 > :first-child {
                border-right: 1px dashed #cbd5e1 !important;
            }
            .pop-card-preview-empty {
                width: 148.5mm !important;
                height: 210mm !important;
                box-sizing: border-box !important;
                background-color: white !important;
            }
            .pop-card-preview {
                box-shadow: none !important;
                border: none !important;
                padding: 0px !important;
            }
        }
     
        /* ---- Mobile Responsive Preview ---- */
        .preview-modal-dialog {
            max-height: 92vh;
            overflow-y: auto;
        }
        .preview-scroll-area {
            overflow-x: auto;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        .pop-card-preview-wrapper {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 8px;
        }
        /* Scale the preview card to fit small screens */
        @media (max-width: 640px) {
            .pop-card-preview {
                transform-origin: top center;
                transform: scale(0.32);
                margin-bottom: calc((210mm * -0.68)) !important;
            }
            .preview-scroll-area {
                min-height: 100px;
            }
        }
        @media (min-width: 641px) and (max-width: 900px) {
            .pop-card-preview {
                transform-origin: top center;
                transform: scale(0.45);
                margin-bottom: calc((210mm * -0.55)) !important;
            }
            .preview-scroll-area {
                min-height: 160px;
            }
        }
        /* ---- End Mobile Responsive Preview ---- */
     </style>

     <!-- Preview Modal Dialog Card -->
     <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden max-w-4xl w-full flex flex-col p-4 sm:p-6 space-y-4 sm:space-y-6 preview-modal-dialog"
          @click.away="open = false">
          
          <!-- Modal Header -->
          <div class="flex justify-between items-center border-b border-slate-100 pb-3">
              <div>
                  <h3 class="text-base font-extrabold text-slate-900">Pratinjau Desain POP A4 - Dua Item</h3>
                  <p class="text-xs text-slate-500 font-medium">Ukuran frame cetak: <span class="font-bold text-indigo-600 uppercase">A4 (Print A5 Portrait)</span></p>
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
                  <div class="pop-card-preview is-a4 bg-white shadow-lg border border-slate-300 relative transition-all duration-300 flex flex-col justify-between overflow-hidden pop-card-a4"
                       style="width: 148mm; height: 210mm;">
                       
                       <!-- Header Banner -->
                       <div class="header-banner-a4">
                           <span>{{ $activePreviewPop['header_text'] ?: 'HARGA SPESIAL' }}</span>
                       </div>

                       <!-- Content Body -->
                       <div class="flex-grow flex flex-col justify-between py-4 px-5 leading-none">
                           <!-- Brand Block -->
                           <div>
                               <div class="brand-name-a4">{{ $activePreviewPop['brand_name'] }}</div>
                           </div>

                           <!-- Price Area -->
                           <div class="price-area-a4">
                               @php
                                   $i1O = $this->formatPriceStatic($activePreviewPop['additional_data']['item1_old_price'] ?? '');
                                   $i1P = $this->formatPriceStatic($activePreviewPop['additional_data']['item1_price'] ?? '');
                                   $i2O = $this->formatPriceStatic($activePreviewPop['additional_data']['item2_old_price'] ?? '');
                                   $i2P = $this->formatPriceStatic($activePreviewPop['additional_data']['item2_price'] ?? '');
                               @endphp
                               <div class="double-container-a4">
                                   <div class="double-row-a4 double-row-border-a4">
                                       <div class="double-left-a4">
                                           <span class="double-name-a4">{{ $activePreviewPop['additional_data']['item1_name'] ?? '' }}</span>
                                           @if(!empty($activePreviewPop['additional_data']['item1_old_price']))
                                               <span class="double-was-a4 select-none"><span class="coret-diagonal-preview">Rp {{ $i1O['base'] . $i1O['suffix'] }}</span></span>
                                           @endif
                                       </div>
                                       <div class="double-right-a4">
                                           <span class="double-rp-a4">Rp</span>
                                           <span class="double-price-base-a4">{{ $i1P['base'] }}</span>
                                           <span class="double-price-suffix-a4">{{ $i1P['suffix'] }}</span>
                                       </div>
                                   </div>
                                   <div class="double-row-a4">
                                       <div class="double-left-a4">
                                           <span class="double-name-a4">{{ $activePreviewPop['additional_data']['item2_name'] ?? '' }}</span>
                                           @if(!empty($activePreviewPop['additional_data']['item2_old_price']))
                                               <span class="double-was-a4 select-none"><span class="coret-diagonal-preview">Rp {{ $i2O['base'] . $i2O['suffix'] }}</span></span>
                                           @endif
                                       </div>
                                       <div class="double-right-a4">
                                           <span class="double-rp-a4">Rp</span>
                                           <span class="double-price-base-a4">{{ $i2P['base'] }}</span>
                                           <span class="double-price-suffix-a4">{{ $i2P['suffix'] }}</span>
                                       </div>
                                   </div>
                               </div>
                           </div>
                        <!-- Footer Image -->
                        <div style="text-align:center; padding-bottom: 10px; padding-top: 4px; line-height:0;">
                            <img src="{{ asset('images/Picture2.bmp') }}" alt="Footer Logo" style="max-height: 18px; width: auto; display: inline-block; object-fit: contain;">
                        </div>
                           <div class="h-4"></div>
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
                      @click="window.printA4DoubleItem()"
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
window.printA4DoubleItem = function() {
    var area = document.getElementById('pop-print-area-a4di');
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
        + '.print-page-a4 { width: 297mm; height: 210mm; display: flex; flex-direction: row; page-break-after: always; page-break-inside: avoid; box-sizing: border-box; background-color: white; overflow: hidden; }'
        + '.pop-card-a4 { width: 148.5mm; height: 210mm; box-sizing: border-box; display: flex; flex-direction: column; justify-content: space-between; background-color: white; color: black; overflow: hidden; position: relative; }'
        + '.print-page-a4 > :first-child { border-right: 1.5px dashed black !important; }'
        + '.pop-card-a4-empty { width: 148.5mm; height: 210mm; box-sizing: border-box; background-color: white; }'
        + '.header-banner-a4 { background-color: #dc2626 !important; color: white !important; text-align: center; text-transform: uppercase; display: flex; align-items: center; justify-content: center; margin: 16px 16px 0 16px; height: 75px; box-sizing: border-box; padding: 0 10px; }'
        + '.header-banner-a4 span { font-size: 46pt !important; font-weight: 700 !important; line-height: 1; letter-spacing: -0.5px; }'
        + '.brand-name-a4 { font-size: 46pt !important; font-weight: 700 !important; text-transform: uppercase; color: black !important; line-height: 1; margin-top: 12px; letter-spacing: -0.5px; text-align: center; }'
        + '.double-container-a4 { width: 100%; display: flex; flex-direction: column; gap: 12px; padding: 0 20px; box-sizing: border-box; }'
        + '.double-row-a4 { display: flex; justify-content: space-between; align-items: center; width: 100%; }'
        + '.double-row-border-a4 { border-bottom: 1px dashed #cbd5e1; padding-bottom: 10px; }'
        + '.double-left-a4 { text-align: left; display: flex; flex-direction: column; }'
        + '.double-name-a4 { font-size: 14pt !important; font-weight: 700 !important; color: black !important; text-transform: uppercase; }'
        + '.double-was-a4 { font-size: 11pt !important; color: #64748b !important; font-weight: 400; margin-top: 1px; }'
        + '.double-right-a4 { display: flex; align-items: flex-start; color: #dc2626 !important; font-weight: 700 !important; }'
        + '.double-rp-a4 { font-size: 11pt !important; font-weight: 400 !important; margin-top: 1px; margin-right: 1px; color: #000000 !important; }'
        + '.double-price-base-a4 { font-size: 26pt !important; font-weight: 700 !important; line-height: 0.8; }'
        + '.double-price-suffix-a4 { font-size: 14pt !important; font-weight: 700 !important; line-height: 0.8; margin-top: 1px; }'
        + '.coret-diagonal-preview { position: relative; display: inline-block; }'
        + '.coret-diagonal-preview::after { content: ""; position: absolute; left: -3%; right: -3%; top: 50%; height: 2px; background-color: #000000 !important; transform: rotate(-6deg); }'
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
<div id="pop-print-area-a4di" style="position:absolute;left:-99999px;top:0;width:297mm;overflow:hidden;">
    @foreach(array_chunk($previewQueue, 2) as $chunk)
        <div class="print-page-a4">
            @foreach($chunk as $pq)
                <div class="pop-card-a4">
                    <!-- Header Banner -->
                    <div class="header-banner-a4">
                        <span>{{ $pq['header_text'] ?: 'HARGA SPESIAL' }}</span>
                    </div>

                    <!-- Content Body -->
                    <div style="flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; padding: 16px 20px 16px 20px; line-height: none;">
                        <!-- Brand Block -->
                        <div>
                            <div class="brand-name-a4" style="margin-top: 12px;">{{ $pq['brand_name'] }}</div>
                        </div>

                        <!-- Price Area -->
                        <div class="price-area-a4">
                            @php
                                $i1O = $this->formatPriceStatic($pq['additional_data']['item1_old_price'] ?? '');
                                $i1P = $this->formatPriceStatic($pq['additional_data']['item1_price'] ?? '');
                                $i2O = $this->formatPriceStatic($pq['additional_data']['item2_old_price'] ?? '');
                                $i2P = $this->formatPriceStatic($pq['additional_data']['item2_price'] ?? '');
                            @endphp
                            <div class="double-container-a4">
                                <div class="double-row-a4 double-row-border-a4">
                                    <div class="double-left-a4">
                                        <span class="double-name-a4">{{ $pq['additional_data']['item1_name'] ?? '' }}</span>
                                        @if(!empty($pq['additional_data']['item1_old_price']))
                                            <span class="double-was-a4 select-none"><span class="coret-diagonal-preview">Rp {{ $i1O['base'] . $i1O['suffix'] }}</span></span>
                                        @endif
                                    </div>
                                    <div class="double-right-a4">
                                        <span class="double-rp-a4">Rp</span>
                                        <span class="double-price-base-a4">{{ $i1P['base'] }}</span>
                                        <span class="double-price-suffix-a4">{{ $i1P['suffix'] }}</span>
                                    </div>
                                </div>
                                <div class="double-row-a4">
                                    <div class="double-left-a4">
                                        <span class="double-name-a4">{{ $pq['additional_data']['item2_name'] ?? '' }}</span>
                                        @if(!empty($pq['additional_data']['item2_old_price']))
                                            <span class="double-was-a4 select-none"><span class="coret-diagonal-preview">Rp {{ $i2O['base'] . $i2O['suffix'] }}</span></span>
                                        @endif
                                    </div>
                                    <div class="double-right-a4">
                                        <span class="double-rp-a4">Rp</span>
                                        <span class="double-price-base-a4">{{ $i2P['base'] }}</span>
                                        <span class="double-price-suffix-a4">{{ $i2P['suffix'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Footer Image -->
                        <div style="text-align:center; padding-bottom: 10px; padding-top: 4px; line-height:0;">
                            <img src="{{ asset('images/Picture2.bmp') }}" alt="Footer Logo" style="max-height: 18px; width: auto; display: inline-block; object-fit: contain;">
                        </div>
                        <div style="height: 16px;"></div>
                    </div>
                </div>
            @endforeach
            @if(count($chunk) < 2)
                <div class="pop-card-a4-empty"></div>
            @endif
        </div>
    @endforeach
</div>
</div>
