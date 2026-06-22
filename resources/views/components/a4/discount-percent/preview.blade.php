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
        if ($pop && $pop->frame_size === 'A4' && $pop->layout_type === 'discount_percent') {
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
            ->where('layout_type', 'discount_percent')
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
        
        .pop-card-a4 .product-desc-a4 {
            font-size: 21pt !important;
            font-weight: 400 !important;
            text-transform: uppercase;
            color: #334155 !important;
            line-height: 1.2;
            margin-top: 2px;
            text-align: center;
        }
        
        /* Discount Layout Specific Styles */
        .pop-card-a4 .discount-container-a4 {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-grow: 1;
            margin-top: 8px;
            margin-bottom: 8px;
            width: 100%;
        }
        
        /* Single Discount Mode */
        .pop-card-a4 .single-discount-wrapper-a4 {
            display: flex;
            align-items: flex-start;
            color: #dc2626 !important;
            font-weight: 700 !important;
            line-height: 0.8;
        }
        .pop-card-a4 .single-discount-base-a4 {
            font-size: 240pt !important;
            line-height: 0.8;
            letter-spacing: -6px;
        }
        .pop-card-a4 .single-discount-percent-a4 {
            font-size: 55pt !important;
            margin-top: 15px;
            margin-left: 2px;
        }
        
        /* Double Discount Mode */
        .pop-card-a4 .double-discount-wrapper-a4 {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            color: #dc2626 !important;
            font-weight: 700 !important;
            line-height: 0.8;
        }
        .pop-card-a4 .double-discount-first-a4 {
            display: flex;
            align-items: flex-start;
            line-height: 0.8;
        }
        .pop-card-a4 .double-discount-first-base-a4 {
            font-size: 195pt !important;
            line-height: 0.8;
            letter-spacing: -4px;
        }
        .pop-card-a4 .double-discount-first-percent-a4 {
            font-size: 43pt !important;
            margin-top: 11px;
            margin-left: 1px;
        }
        .pop-card-a4 .double-discount-plus-a4 {
            display: flex;
            align-items: center;
            align-self: flex-end;
            height: 195pt;
            margin-left: -10px;
            margin-right: -10px;
        }
        .pop-card-a4 .double-discount-plus-text-a4 {
            font-size: 43pt !important;
            font-weight: 700 !important;
            color: black !important;
            line-height: 1;
        }
        .pop-card-a4 .double-discount-second-a4 {
            display: flex;
            align-items: flex-start;
            line-height: 0.8;
        }
        .pop-card-a4 .double-discount-second-base-a4 {
            font-size: 145pt !important;
            line-height: 0.8;
            letter-spacing: -3px;
        }
        .pop-card-a4 .double-discount-second-percent-a4 {
            font-size: 32pt !important;
            margin-top: 8px;
            margin-left: 1px;
        }
        
        /* Bottom Price Area styling */
        .pop-card-a4 .bottom-prices-container-a4 {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin-top: 6px;
            margin-bottom: 6px;
        }
        
        .pop-card-a4 .starting-from-a4 {
            font-size: 19pt !important;
            font-weight: 400 !important;
            color: black !important;
            text-align: center;
            margin-top: -10px;
            margin-bottom: 16px;
            line-height: 1;
        }
        
        .pop-card-a4 .prices-row-a4 {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 16px;
            width: 100%;
        }
        
        /* Old Price */
        .pop-card-a4 .price-old-wrapper-a4 {
            display: flex;
            align-items: flex-start;
            line-height: 0.85;
        }
        .pop-card-a4 .price-old-rp-a4 {
            font-size: 16pt !important;
            font-weight: 400 !important;
            color: black !important;
            margin-top: 4px;
            margin-right: 2px;
            line-height: 1;
        }
        .pop-card-a4 .price-old-base-a4 {
            font-size: 60pt !important;
            font-weight: 700 !important;
            letter-spacing: -1px;
            line-height: 0.8;
            color: #dc2626 !important;
        }
        .pop-card-a4 .price-old-suffix-a4 {
            font-size: 34pt !important;
            font-weight: 700 !important;
            line-height: 0.8;
            margin-top: 1px;
            color: #dc2626 !important;
        }
        
        .pop-card-a4 .coret-diagonal-discount-a4 {
            position: relative;
            display: inline-flex;
            align-items: flex-start;
        }
        .pop-card-a4 .coret-diagonal-discount-a4::after {
            content: "";
            position: absolute;
            left: -3%;
            right: -3%;
            top: 50%;
            height: 2.5px;
            background-color: #000000 !important;
            transform: rotate(-6deg);
        }
        
        /* Promo Price */
        .pop-card-a4 .price-promo-wrapper-a4 {
            display: flex;
            align-items: flex-start;
            line-height: 0.85;
        }
        .pop-card-a4 .price-promo-rp-a4 {
            font-size: 19pt !important;
            font-weight: 400 !important;
            color: black !important;
            margin-top: 5px;
            margin-right: 2px;
            line-height: 1;
        }
        .pop-card-a4 .price-promo-base-a4 {
            font-size: 70pt !important;
            font-weight: 700 !important;
            letter-spacing: -1.5px;
            line-height: 0.8;
            color: #dc2626 !important;
        }
        .pop-card-a4 .price-promo-suffix-a4 {
            font-size: 41pt !important;
            font-weight: 700 !important;
            line-height: 0.8;
            margin-top: 1px;
            color: #dc2626 !important;
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
     </style>
     
     <!-- Preview Modal Dialog Card -->
     <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden max-w-4xl w-full flex flex-col p-4 sm:p-6 space-y-4 sm:space-y-6 preview-modal-dialog"
          @click.away="open = false">
          
          <!-- Modal Header -->
          <div class="flex justify-between items-center border-b border-slate-100 pb-3">
              <div>
                  <h3 class="text-base font-extrabold text-slate-900">Pratinjau Desain POP A4 - Diskon %</h3>
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
                           <span>{{ $activePreviewPop['header_text'] ?: 'DISKON' }}</span>
                       </div>

                       <!-- Content Body -->
                       @php
                           $showDesc = !empty($activePreviewPop['additional_data']['show_description']);
                           $showStart = !empty($activePreviewPop['show_starting_from']);
                           $isDouble = !empty($activePreviewPop['additional_data']['is_double_discount']);
                           
                           $disc1 = $activePreviewPop['additional_data']['discount_percent'] ?? '50';
                           $disc2 = $activePreviewPop['additional_data']['discount_percent_2'] ?? '';
                           
                           $promoPrice = $this->formatPriceStatic($activePreviewPop['primary_price'] ?? '');
                           $oldPrice = $this->formatPriceStatic($activePreviewPop['secondary_price'] ?? '');
                       @endphp
                       <div class="flex-grow flex flex-col justify-between py-4 px-5 leading-none">
                            <!-- Brand Block -->
                            <div>
                                <div class="brand-name-a4">{{ $activePreviewPop['brand_name'] }}</div>
                                @if($showDesc && !empty($activePreviewPop['product_desc']))
                                    <div class="product-desc-a4">{{ $activePreviewPop['product_desc'] }}</div>
                                @endif
                            </div>

                            <!-- Discount Area -->
                            <div class="discount-container-a4">
                                @if(!$isDouble)
                                    <!-- Single Discount Mode -->
                                    <div class="single-discount-wrapper-a4">
                                        <span class="single-discount-base-a4">{{ $disc1 }}</span>
                                        <span class="single-discount-percent-a4">%</span>
                                    </div>
                                @else
                                    <!-- Double Discount Mode -->
                                    <div class="double-discount-wrapper-a4">
                                        <div class="double-discount-first-a4">
                                            <span class="double-discount-first-base-a4">{{ $disc1 }}</span>
                                            <span class="double-discount-first-percent-a4">%</span>
                                        </div>
                                        <div class="double-discount-plus-a4">
                                            <span class="double-discount-plus-text-a4">+</span>
                                        </div>
                                        <div class="double-discount-second-a4">
                                            <span class="double-discount-second-base-a4">{{ $disc2 }}</span>
                                            <span class="double-discount-second-percent-a4">%</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Bottom Price Area -->
                            <div class="bottom-prices-container-a4">
                                @if($showStart && !empty($activePreviewPop['secondary_price']))
                                    <div class="starting-from-a4">Mulai Dari</div>
                                @endif
                                
                                <div class="prices-row-a4">
                                    <!-- Old Price (Left) -->
                                    @if(!empty($activePreviewPop['secondary_price']))
                                        <div class="price-old-wrapper-a4">
                                            <span class="price-old-rp-a4">Rp</span><div class="coret-diagonal-discount-a4"><span class="price-old-base-a4">{{ $oldPrice['base'] }}</span><span class="price-old-suffix-a4">{{ $oldPrice['suffix'] }}</span></div>
                                        </div>
                                    @endif

                                    <!-- Promo Price (Right) -->
                                    <div class="price-promo-wrapper-a4">
                                        <span class="price-promo-rp-a4">Rp</span><span class="price-promo-base-a4">{{ $promoPrice['base'] }}</span><span class="price-promo-suffix-a4">{{ $promoPrice['suffix'] }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Image -->
                            <div style="text-align:center; padding-bottom: 5px; padding-top: 100px; line-height:0;">
                                <img src="{{ asset('images/Picture2.bmp') }}" alt="Footer Logo" style="max-height: 18px; width: auto; display: inline-block; object-fit: contain;">
                            </div>
                            <div style="height: 20px;"></div>
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
                      @click="window.printA4DiscountPercent()"
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
window.printA4DiscountPercent = function() {
    var area = document.getElementById('pop-print-area-a4dp');
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
        + '.product-desc-a4 { font-size: 21pt !important; font-weight: 400 !important; text-transform: uppercase; color: #334155 !important; line-height: 1.2; margin-top: 2px; text-align: center; }'
        + '.discount-container-a4 { display: flex; justify-content: center; align-items: center; flex-grow: 1; margin-top: 8px; margin-bottom: 8px; width: 100%; }'
        + '.single-discount-wrapper-a4 { display: flex; align-items: flex-start; color: #dc2626 !important; font-weight: 700 !important; line-height: 0.8; }'
        + '.single-discount-base-a4 { font-size: 240pt !important; line-height: 0.8; letter-spacing: -6px; }'
        + '.single-discount-percent-a4 { font-size: 55pt !important; margin-top: 15px; margin-left: 2px; }'
        + '.double-discount-wrapper-a4 { display: flex; align-items: flex-end; justify-content: center; color: #dc2626 !important; font-weight: 700 !important; line-height: 0.8; }'
        + '.double-discount-first-a4 { display: flex; align-items: flex-start; line-height: 0.8; }'
        + '.double-discount-first-base-a4 { font-size: 195pt !important; line-height: 0.8; letter-spacing: -4px; }'
        + '.double-discount-first-percent-a4 { font-size: 43pt !important; margin-top: 11px; margin-left: 1px; }'
        + '.double-discount-plus-a4 { display: flex; align-items: center; align-self: flex-end; height: 195pt; margin-left: -10px; margin-right: -10px; }'
        + '.double-discount-plus-text-a4 { font-size: 43pt !important; font-weight: 700 !important; color: black !important; line-height: 1; }'
        + '.double-discount-second-a4 { display: flex; align-items: flex-start; line-height: 0.8; }'
        + '.double-discount-second-base-a4 { font-size: 145pt !important; line-height: 0.8; letter-spacing: -3px; }'
        + '.double-discount-second-percent-a4 { font-size: 32pt !important; margin-top: 8px; margin-left: 1px; }'
        + '.bottom-prices-container-a4 { display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; margin-top: 6px; margin-bottom: 6px; }'
        + '.starting-from-a4 { font-size: 19pt !important; font-weight: 400 !important; color: black !important; text-align: center; margin-top: -10px; margin-bottom: 16px; line-height: 1; }'
        + '.prices-row-a4 { display: flex; align-items: flex-end; justify-content: center; gap: 16px; width: 100%; }'
        + '.price-old-wrapper-a4 { display: flex; align-items: flex-start; line-height: 0.85; }'
        + '.price-old-rp-a4 { font-size: 16pt !important; font-weight: 400 !important; color: black !important; margin-top: 4px; margin-right: 2px; line-height: 1; }'
        + '.price-old-base-a4 { font-size: 60pt !important; font-weight: 700 !important; letter-spacing: -1px; line-height: 0.8; color: #dc2626 !important; }'
        + '.price-old-suffix-a4 { font-size: 34pt !important; font-weight: 700 !important; line-height: 0.8; margin-top: 1px; color: #dc2626 !important; }'
        + '.coret-diagonal-discount-a4 { position: relative; display: inline-flex; align-items: flex-start; }'
        + '.coret-diagonal-discount-a4::after { content: ""; position: absolute; left: -3%; right: -3%; top: 50%; height: 2.5px; background-color: #000000 !important; transform: rotate(-6deg); }'
        + '.price-promo-wrapper-a4 { display: flex; align-items: flex-start; line-height: 0.85; }'
        + '.price-promo-rp-a4 { font-size: 19pt !important; font-weight: 400 !important; color: black !important; margin-top: 5px; margin-right: 2px; line-height: 1; }'
        + '.price-promo-base-a4 { font-size: 70pt !important; font-weight: 700 !important; letter-spacing: -1.5px; line-height: 0.8; color: #dc2626 !important; }'
        + '.price-promo-suffix-a4 { font-size: 41pt !important; font-weight: 700 !important; line-height: 0.8; margin-top: 1px; color: #dc2626 !important; }'
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
<div id="pop-print-area-a4dp" style="position:absolute;left:-99999px;top:0;width:297mm;overflow:hidden;">
    @foreach(array_chunk($previewQueue, 2) as $chunk)
        <div class="print-page-a4">
            @foreach($chunk as $pq)
                <div class="pop-card-a4">
                    <!-- Header Banner -->
                    <div class="header-banner-a4">
                        <span>{{ $pq['header_text'] ?: 'DISKON' }}</span>
                    </div>

                    <!-- Content Body -->
                    @php
                        $pqShowDesc = !empty($pq['additional_data']['show_description']);
                        $pqShowStart = !empty($pq['show_starting_from']);
                        $pqIsDouble = !empty($pq['additional_data']['is_double_discount']);
                        
                        $pqDisc1 = $pq['additional_data']['discount_percent'] ?? '50';
                        $pqDisc2 = $pq['additional_data']['discount_percent_2'] ?? '';
                        
                        $pqPromoPrice = $this->formatPriceStatic($pq['primary_price'] ?? '');
                        $pqOldPrice = $this->formatPriceStatic($pq['secondary_price'] ?? '');
                    @endphp
                    <div style="flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; padding: 16px 20px 16px 20px; line-height: none;">
                        <!-- Brand Block -->
                        <div>
                            <div class="brand-name-a4" style="margin-top: 12px;">{{ $pq['brand_name'] }}</div>
                            @if($pqShowDesc && !empty($pq['product_desc']))
                                <div class="product-desc-a4">{{ $pq['product_desc'] }}</div>
                            @endif
                        </div>

                        <!-- Discount Area -->
                        <div class="discount-container-a4">
                            @if(!$pqIsDouble)
                                <!-- Single Discount Mode -->
                                <div class="single-discount-wrapper-a4">
                                    <span class="single-discount-base-a4">{{ $pqDisc1 }}</span>
                                    <span class="single-discount-percent-a4">%</span>
                                </div>
                            @else
                                <!-- Double Discount Mode -->
                                <div class="double-discount-wrapper-a4">
                                    <div class="double-discount-first-a4">
                                        <span class="double-discount-first-base-a4">{{ $pqDisc1 }}</span>
                                        <span class="double-discount-first-percent-a4">%</span>
                                    </div>
                                    <div class="double-discount-plus-a4">
                                        <span class="double-discount-plus-text-a4">+</span>
                                    </div>
                                    <div class="double-discount-second-a4">
                                        <span class="double-discount-second-base-a4">{{ $pqDisc2 }}</span>
                                        <span class="double-discount-second-percent-a4">%</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Bottom Price Area -->
                        <div class="bottom-prices-container-a4">
                            @if($pqShowStart && !empty($pq['secondary_price']))
                                <div class="starting-from-a4">Mulai Dari</div>
                            @endif
                            
                            <div class="prices-row-a4">
                                <!-- Old Price (Left) -->
                                @if(!empty($pq['secondary_price']))
                                    <div class="price-old-wrapper-a4">
                                        <span class="price-old-rp-a4">Rp</span><div class="coret-diagonal-discount-a4"><span class="price-old-base-a4">{{ $pqOldPrice['base'] }}</span><span class="price-old-suffix-a4">{{ $pqOldPrice['suffix'] }}</span></div>
                                    </div>
                                @endif

                                <!-- Promo Price (Right) -->
                                <div class="price-promo-wrapper-a4">
                                    <span class="price-promo-rp-a4">Rp</span><span class="price-promo-base-a4">{{ $pqPromoPrice['base'] }}</span><span class="price-promo-suffix-a4">{{ $pqPromoPrice['suffix'] }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Image -->
                        <div style="text-align:center; padding-bottom: 5px; padding-top: 100px; line-height:0;">
                            <img src="{{ asset('images/Picture2.bmp') }}" alt="Footer Logo" style="max-height: 18px; width: auto; display: inline-block; object-fit: contain;">
                        </div>
                        <div style="height: 20px;"></div>
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
