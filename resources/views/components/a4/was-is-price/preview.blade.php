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
        if ($pop && $pop->frame_size === 'A4' && $pop->layout_type === 'was_is_price') {
            $this->activePreviewPop = $pop->toArray();
            $this->previewQueue = [$this->activePreviewPop];
            $this->frameSize = $pop->frame_size;
            $this->showModal = true;
        }
    }

    public function handlePreviewBulk($ids)
    {
        if (count($ids) === 0) return;
        $items = Pop::whereIn('id', $ids)
            ->where('frame_size', 'A4')
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
     
     <style x-text="'@media print { @page { size: 148mm 210mm portrait; margin: 0; } }'"></style>
     
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
            height: 3px;
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
        
        .pop-card-a4 .product-desc-a4 {
            font-size: 21pt !important;
            font-weight: 400 !important;
            text-transform: uppercase;
            color: #334155 !important;
            line-height: 1.2;
            margin-top: 2px;
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
        
        /* old price styling */
        .pop-card-a4 .old-price-rp-a4 {
            font-size: 20pt !important;
            font-weight: 400 !important;
            color: #000000 !important;
            margin-top: 6px;
            margin-right: 2px;
            line-height: 1;
        }
        .pop-card-a4 .old-price-base-a4 {
            font-size: 110pt !important;
            font-weight: 700 !important;
            line-height: 0.8;
            letter-spacing: -1.5px;
        }
        .pop-card-a4 .old-price-suffix-a4 {
            font-size: 72pt !important;
            font-weight: 700 !important;
            line-height: 0.8;
            margin-top: 1px;
        }
        
        /* promo price styling */
        .pop-card-a4 .promo-price-rp-a4 {
            font-size: 20pt !important;
            font-weight: 400 !important;
            color: #000000 !important;
            margin-top: 8px;
            margin-right: 2px;
            line-height: 1;
        }
        .pop-card-a4 .promo-price-base-a4 {
            font-size: 130pt !important;
            font-weight: 700 !important;
            line-height: 0.8;
            letter-spacing: -2px;
        }
        .pop-card-a4 .promo-price-suffix-a4 {
            font-size: 100pt !important;
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
                  <h3 class="text-base font-extrabold text-slate-900">Pratinjau Desain POP A4 - Harga Coret</h3>
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
                               <div class="product-desc-a4">{{ $activePreviewPop['product_desc'] }}</div>
                           </div>

                           <!-- Price Area -->
                           <div class="price-area-a4">
                               @php
                                   $promoParts = $this->formatPriceStatic($activePreviewPop['primary_price']);
                                   $oldParts = $this->formatPriceStatic($activePreviewPop['secondary_price']);
                               @endphp
                               <div class="flex flex-col items-center justify-center gap-1.5 my-1">
                                   <!-- Old Price Row (Coret) -->
                                   <div class="flex items-start select-none relative">
                                       <span class="old-price-rp-a4">Rp</span>
                                       <div class="coret-diagonal-preview flex items-start text-[#dc2626] font-bold">
                                           <span class="old-price-base-a4">{{ $oldParts['base'] }}</span>
                                           <span class="old-price-suffix-a4">{{ $oldParts['suffix'] }}</span>
                                       </div>
                                   </div>
                                   <!-- Promo Price Row -->
                                   <div class="flex items-start select-none">
                                       <span class="promo-price-rp-a4">Rp</span>
                                       <div class="flex items-start text-[#dc2626] font-bold">
                                           <span class="promo-price-base-a4">{{ $promoParts['base'] }}</span>
                                           <span class="promo-price-suffix-a4">{{ $promoParts['suffix'] }}</span>
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
                      @click="window.print();"
                      class="bg-[#6366f1] hover:bg-[#4f46e5] text-white font-extrabold py-2.5 px-6 rounded-xl text-xs transition duration-150 flex items-center gap-1.5 shadow-md shadow-indigo-100">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                  </svg>
                  Mulai Mencetak (Print)
              </button>
          </div>
     </div>
</div>

<!-- ==================== PRINT CONTEXT WRAPPER (HIDDEN ON SCREEN) ==================== -->
<div id="pop-print-area" class="print-only hidden">
    @foreach($previewQueue as $pq)
        <div class="pop-card-preview bg-white relative flex flex-col justify-between overflow-hidden print-card-item-modal pop-card-a4"
             style="width: 148mm; height: 210mm; margin: 0 auto; page-break-after: always; page-break-inside: avoid; border: none; box-shadow: none; box-sizing: border-box; padding: 0px; font-family: 'Arial Narrow', 'Archivo Narrow', Arial, sans-serif;">
            
            <!-- Header Banner -->
            <div class="header-banner-a4">
                <span>{{ $pq['header_text'] ?: 'HARGA SPESIAL' }}</span>
            </div>

            <!-- Content Body -->
            <div class="flex-grow flex flex-col justify-between py-4 px-5 leading-none">
                <!-- Brand Block -->
                <div>
                    <div class="brand-name-a4">{{ $pq['brand_name'] }}</div>
                    <div class="product-desc-a4">{{ $pq['product_desc'] }}</div>
                </div>

                <!-- Price Area -->
                <div class="price-area-a4">
                    @php
                        $promoParts = $this->formatPriceStatic($pq['primary_price']);
                        $oldParts = $this->formatPriceStatic($pq['secondary_price']);
                    @endphp
                    <div class="flex flex-col items-center justify-center gap-1.5 my-1">
                        <!-- Old Price Row (Coret) -->
                        <div class="flex items-start select-none relative">
                            <span class="old-price-rp-a4">Rp</span>
                            <div class="coret-diagonal-preview flex items-start text-[#dc2626] font-bold">
                                <span class="old-price-base-a4">{{ $oldParts['base'] }}</span>
                                <span class="old-price-suffix-a4">{{ $oldParts['suffix'] }}</span>
                            </div>
                        </div>
                        <!-- Promo Price Row -->
                        <div class="flex items-start select-none">
                            <span class="promo-price-rp-a4">Rp</span>
                            <div class="flex items-start text-[#dc2626] font-bold">
                                <span class="promo-price-base-a4">{{ $promoParts['base'] }}</span>
                                <span class="promo-price-suffix-a4">{{ $promoParts['suffix'] }}</span>
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
    @endforeach
</div>
</div>
