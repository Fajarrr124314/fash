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
        if ($pop && $pop->frame_size === 'A4') {
            $this->activePreviewPop = $pop->toArray();
            $this->previewQueue = [$this->activePreviewPop];
            $this->frameSize = $pop->frame_size;
            $this->showModal = true;
        }
    }

    public function handlePreviewBulk($ids)
    {
        if (count($ids) === 0) return;
        $items = Pop::whereIn('id', $ids)->where('frame_size', 'A4')->get()->toArray();
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
     
     <!-- Dynamic printing style override inside modal -->
     <style x-text="'@media print { @page { size: ' + (frameSize === 'A5' ? '148mm 105mm landscape' : (frameSize === 'A4' ? '148mm 210mm portrait' : '210mm 297mm portrait')) + '; margin: 0; } }'"></style>
     
     <style>
        .pop-card-preview, .pop-card-preview * {
            font-family: 'Arial Narrow', 'Archivo Narrow', Arial, sans-serif !important;
        }
        
        /* Bypass global thin font for preview outputs */
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
        
        /* Specific CSS rules for POP A4 (printed on A5 Portrait) */
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

        .pop-card-a4 .price-wrapper-a4 {
            display: flex;
            align-items: flex-start;
            color: #dc2626 !important;
            font-weight: 700 !important;
            line-height: 0.85;
        }

        .pop-card-a4 .price-rp-a4 {
            font-size: 20pt !important;
            font-weight: 400 !important;
            color: #000000ff !important;
            margin-top: 8px;
            margin-right: 2px;
            line-height: 1;
        }

        .pop-card-a4 .price-base-a4 {
            font-size: 140pt !important;
            font-weight: 700 !important;
            letter-spacing: -2px;
            line-height: 0.8;
        }

        .pop-card-a4 .price-suffix-a4 {
            font-size: 96pt !important;
            font-weight: 700 !important;
            line-height: 0.8;
            margin-top: 2px;
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
                  <h3 class="text-base font-extrabold text-slate-900">Pratinjau Desain POP A4</h3>
                  <p class="text-xs text-slate-500 font-medium">Ukuran frame cetak: <span class="font-bold text-indigo-600 uppercase">A4 (Print A5 Portrait)</span></p>
              </div>
              <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 transition">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
              </button>
          </div>

          <!-- Modal Content: Renders the active POP template -->
          <div class="flex justify-center items-center py-6 bg-slate-50 rounded-xl border border-dashed border-slate-200 overflow-auto max-h-[420px]">
              @if($activePreviewPop)
                  <div class="pop-card-preview bg-white shadow-lg border border-slate-300 relative transition-all duration-300 flex flex-col justify-between overflow-hidden pop-card-a4"
                       style="width: 148mm; height: 210mm;">
                       
                       <!-- Header Banner -->
                       <div class="header-banner-a4">
                           <span>{{ $activePreviewPop['header_text'] ?: 'HARGA SPESIAL' }}</span>
                       </div>

                       <!-- Content Body -->
                       <div class="flex-grow flex flex-col items-center text-center justify-between py-4 px-5 leading-none" style="display: flex; flex-direction: column; justify-content: space-between; flex-grow: 1;">
                           
                           <!-- Brand Name -->
                           <div class="w-full flex flex-col items-center" style="margin-top: 12px;">
                               <span class="brand-name-a4 block">
                                   {{ $activePreviewPop['brand_name'] }}
                               </span>
                               
                               @if($activePreviewPop['layout_type'] !== 'double_item')
                                   <span class="product-desc-a4 block">
                                       {{ $activePreviewPop['product_desc'] }}
                                   </span>
                               @endif
                           </div>

                           <!-- Dynamic Pricing Area -->
                           <div class="flex-grow flex flex-col items-center justify-center w-full" style="display: flex; flex-direction: column; justify-content: center; align-items: center; flex-grow: 1;">
                               
                               <!-- 1. Single Price Layout -->
                               @if($activePreviewPop['layout_type'] === 'single_price')
                                   @php
                                       $priceParts = $this->formatPriceStatic($activePreviewPop['primary_price']);
                                   @endphp
                                   <div class="price-wrapper-a4">
                                       <span class="price-rp-a4">Rp</span>
                                       <span class="price-base-a4">{{ $priceParts['base'] }}</span>
                                       <span class="price-suffix-a4">{{ $priceParts['suffix'] }}</span>
                                   </div>
                               @endif

                               <!-- 2. Was / Is Price (Coret) -->
                               @if($activePreviewPop['layout_type'] === 'was_is_price')
                                   @php
                                       $promoParts = $this->formatPriceStatic($activePreviewPop['primary_price']);
                                       $oldParts = $this->formatPriceStatic($activePreviewPop['secondary_price']);
                                   @endphp
                                   <div class="flex flex-col items-center justify-center gap-1.5 my-1">
                                       <!-- Old Price Row (Coret) -->
                                       <div class="flex items-start select-none relative">
                                           <span class="text-black font-normal" style="font-size: 16pt; margin-top: 6px; margin-right: 2px; line-height: 1;">Rp</span>
                                           <div class="coret-diagonal-preview flex items-start text-[#dc2626] font-bold">
                                               <span style="font-size: 110pt; line-height: 0.8; letter-spacing: -1.5px;">{{ $oldParts['base'] }}</span>
                                               <span style="font-size: 72pt; line-height: 0.8; margin-top: 1px;">{{ $oldParts['suffix'] }}</span>
                                           </div>
                                       </div>
                                       <!-- Promo Price Row -->
                                       <div class="flex items-start select-none">
                                           <span class="text-black font-normal" style="font-size: 20pt; margin-top: 8px; margin-right: 2px; line-height: 1;">Rp</span>
                                           <div class="flex items-start text-[#dc2626] font-bold">
                                               <span style="font-size: 130pt; line-height: 0.8; letter-spacing: -2px;">{{ $promoParts['base'] }}</span>
                                               <span style="font-size: 100pt; line-height: 0.8; margin-top: 1px;">{{ $promoParts['suffix'] }}</span>
                                           </div>
                                       </div>
                                   </div>
                               @endif

                               <!-- 3. Discount Percent Layout -->
                               @if($activePreviewPop['layout_type'] === 'discount_percent')
                                   <div class="flex flex-col items-center w-full">
                                       <div class="flex items-center text-[#dc2626] font-bold" style="line-height: 1;">
                                           @if($activePreviewPop['additional_data']['has_sd'] ?? false)
                                               <span class="text-black font-bold uppercase mr-1.5" style="font-size: 37.5pt;">S/D</span>
                                           @endif
                                           <span style="font-size: 210pt; line-height: 0.8; letter-spacing: -3px;">{{ $activePreviewPop['additional_data']['discount_percent'] ?? '50' }}</span>
                                           <span style="font-size: 37.5pt; margin-left: 2px;">%</span>
                                       </div>
                                       
                                       <!-- Bottom Comparative List -->
                                       @php
                                           $item1O = $this->formatPriceStatic($activePreviewPop['additional_data']['item1_old_price'] ?? '');
                                           $item1P = $this->formatPriceStatic($activePreviewPop['additional_data']['item1_price'] ?? '');
                                           $item2O = $this->formatPriceStatic($activePreviewPop['additional_data']['item2_old_price'] ?? '');
                                           $item2P = $this->formatPriceStatic($activePreviewPop['additional_data']['item2_price'] ?? '');
                                       @endphp
                                       <div class="w-full border-t border-slate-300 mt-2 pt-2 text-black">
                                           <div class="grid grid-cols-2 gap-2 text-center">
                                               <div class="flex flex-col items-center">
                                                   <span class="text-[14px] font-bold text-slate-700 block mb-0.5">{{ strtoupper($activePreviewPop['additional_data']['item1_name'] ?? 'LENGAN PENDEK') }}</span>
                                                   <div class="coret-diagonal-preview text-[14px] text-slate-500 font-semibold mb-0.5">
                                                       <span>Rp</span>
                                                       <span>{{ $item1O['base'] . $item1O['suffix'] }}</span>
                                                   </div>
                                                   <div style="color: #dc2626; font-weight: bold; display: flex; align-items: flex-start; font-size: 20px;">
                                                       <span class="text-[12px] mt-0.5 mr-0.5">Rp</span>
                                                       <span>{{ $item1P['base'] }}</span>
                                                       <span class="text-[13px] mt-0.5">{{ $item1P['suffix'] }}</span>
                                                   </div>
                                               </div>
                                               
                                               <div class="flex flex-col items-center border-l border-slate-200">
                                                   <span class="text-[14px] font-bold text-slate-700 block mb-0.5">{{ strtoupper($activePreviewPop['additional_data']['item2_name'] ?? 'LENGAN PANJANG') }}</span>
                                                   <div class="coret-diagonal-preview text-[14px] text-slate-500 font-semibold mb-0.5">
                                                       <span>Rp</span>
                                                       <span>{{ $item2O['base'] . $item2O['suffix'] }}</span>
                                                   </div>
                                                   <div style="color: #dc2626; font-weight: bold; display: flex; align-items: flex-start; font-size: 20px;">
                                                       <span class="text-[12px] mt-0.5 mr-0.5">Rp</span>
                                                       <span>{{ $item2P['base'] }}</span>
                                                       <span class="text-[13px] mt-0.5">{{ $item2P['suffix'] }}</span>
                                                   </div>
                                               </div>
                                           </div>
                                       </div>
                                   </div>
                               @endif

                               <!-- Double Item list -->
                               @if($activePreviewPop['layout_type'] === 'double_item')
                                   @php
                                       $i1O = $this->formatPriceStatic($activePreviewPop['additional_data']['item1_old_price'] ?? '');
                                       $i1P = $this->formatPriceStatic($activePreviewPop['additional_data']['item1_price'] ?? '');
                                       $i2O = $this->formatPriceStatic($activePreviewPop['additional_data']['item2_old_price'] ?? '');
                                       $i2P = $this->formatPriceStatic($activePreviewPop['additional_data']['item2_price'] ?? '');
                                   @endphp
                                   <div class="w-full flex flex-col gap-2 py-1">
                                       <div class="w-full flex justify-between items-center border-b border-dashed border-slate-300 pb-1.5">
                                           <div class="text-left flex flex-col">
                                               <span class="text-sm font-bold text-slate-800">{{ strtoupper($activePreviewPop['additional_data']['item1_name'] ?? '') }}</span>
                                               @if(!empty($activePreviewPop['additional_data']['item1_old_price']))
                                                   <div class="coret-diagonal-preview text-xs text-slate-500 font-medium align-self-start mt-0.5">
                                                       <span>Rp</span>
                                                       <span>{{ $i1O['base'] . $i1O['suffix'] }}</span>
                                                   </div>
                                               @endif
                                           </div>
                                           <div class="text-[#dc2626] font-bold flex items-start">
                                               <span class="text-[10px] mt-0.5 mr-0.5">Rp</span>
                                               <span class="text-2xl leading-none">{{ $i1P['base'] }}</span>
                                               <span class="text-sm leading-none font-bold mt-0.5">{{ $i1P['suffix'] }}</span>
                                           </div>
                                       </div>
                                       
                                       <div class="w-full flex justify-between items-center pt-1">
                                           <div class="text-left flex flex-col">
                                               <span class="text-sm font-bold text-slate-800">{{ strtoupper($activePreviewPop['additional_data']['item2_name'] ?? '') }}</span>
                                               @if(!empty($activePreviewPop['additional_data']['item2_old_price']))
                                                   <div class="coret-diagonal-preview text-xs text-slate-500 font-medium align-self-start mt-0.5">
                                                       <span>Rp</span>
                                                       <span>{{ $i2O['base'] . $i2O['suffix'] }}</span>
                                                   </div>
                                               @endif
                                           </div>
                                           <div class="text-[#dc2626] font-bold flex items-start">
                                               <span class="text-[10px] mt-0.5 mr-0.5">Rp</span>
                                               <span class="text-2xl leading-none">{{ $i2P['base'] }}</span>
                                               <span class="text-sm leading-none font-bold mt-0.5">{{ $i2P['suffix'] }}</span>
                                           </div>
                                       </div>
                                   </div>
                               @endif

                           </div>

                           <!-- Footer -->
                           <div class="w-full border-t border-slate-200 pt-1.5 flex justify-between items-center select-none text-[8px] font-bold text-slate-400">
                               <span>POP FASHION YKR</span>
                               <span class="uppercase">A5 PORTRAIT</span>
                           </div>
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
            <div class="flex-grow flex flex-col items-center text-center justify-between py-4 px-5 leading-none" style="display: flex; flex-direction: column; justify-content: space-between; flex-grow: 1;">
                
                <!-- Brand Name -->
                <div class="w-full flex flex-col items-center" style="margin-top: 12px;">
                    <span class="brand-name-a4 block">
                        {{ $pq['brand_name'] }}
                    </span>
                    
                    @if($pq['layout_type'] !== 'double_item')
                        <span class="product-desc-a4 block">
                            {{ $pq['product_desc'] }}
                        </span>
                    @endif
                </div>

                <!-- Dynamic Pricing Area -->
                <div class="flex-grow flex flex-col items-center justify-center w-full" style="display: flex; flex-direction: column; justify-content: center; align-items: center; flex-grow: 1;">
                    
                    <!-- 1. Single Price Layout -->
                    @if($pq['layout_type'] === 'single_price')
                        @php
                            $priceParts = $this->formatPriceStatic($pq['primary_price']);
                        @endphp
                        <div class="price-wrapper-a4">
                            <span class="price-rp-a4">Rp</span>
                            <span class="price-base-a4">{{ $priceParts['base'] }}</span>
                            <span class="price-suffix-a4">{{ $priceParts['suffix'] }}</span>
                        </div>
                    @endif

                    <!-- 2. Was / Is Price (Coret) -->
                    @if($pq['layout_type'] === 'was_is_price')
                        @php
                            $promoParts = $this->formatPriceStatic($pq['primary_price']);
                            $oldParts = $this->formatPriceStatic($pq['secondary_price']);
                        @endphp
                        <div class="flex flex-col items-center justify-center gap-1.5 my-1">
                            <!-- Old Price Row (Coret) -->
                            <div class="flex items-start select-none relative">
                                <span class="text-black font-normal" style="font-size: 16pt; margin-top: 6px; margin-right: 2px; line-height: 1;">Rp</span>
                                <div class="coret-diagonal-preview flex items-start text-[#dc2626] font-bold">
                                    <span style="font-size: 110pt; line-height: 0.8; letter-spacing: -1.5px;">{{ $oldParts['base'] }}</span>
                                    <span style="font-size: 72pt; line-height: 0.8; margin-top: 1px;">{{ $oldParts['suffix'] }}</span>
                                </div>
                            </div>
                            <!-- Promo Price Row -->
                            <div class="flex items-start select-none">
                                <span class="text-black font-normal" style="font-size: 20pt; margin-top: 8px; margin-right: 2px; line-height: 1;">Rp</span>
                                <div class="flex items-start text-[#dc2626] font-bold">
                                    <span style="font-size: 130pt; line-height: 0.8; letter-spacing: -2px;">{{ $promoParts['base'] }}</span>
                                    <span style="font-size: 100pt; line-height: 0.8; margin-top: 1px;">{{ $promoParts['suffix'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- 3. Discount Percent Layout -->
                    @if($pq['layout_type'] === 'discount_percent')
                        <div class="flex flex-col items-center w-full">
                            <div class="flex items-center text-[#dc2626] font-bold" style="line-height: 1;">
                                @if($pq['additional_data']['has_sd'] ?? false)
                                    <span class="text-black font-bold uppercase mr-1.5" style="font-size: 37.5pt;">S/D</span>
                                @endif
                                <span style="font-size: 210pt; line-height: 0.8; letter-spacing: -3px;">{{ $pq['additional_data']['discount_percent'] ?? '50' }}</span>
                                <span style="font-size: 37.5pt; margin-left: 2px;">%</span>
                            </div>
                            
                            <!-- Bottom Comparative List -->
                            @php
                                $item1O = $this->formatPriceStatic($pq['additional_data']['item1_old_price'] ?? '');
                                $item1P = $this->formatPriceStatic($pq['additional_data']['item1_price'] ?? '');
                                $item2O = $this->formatPriceStatic($pq['additional_data']['item2_old_price'] ?? '');
                                $item2P = $this->formatPriceStatic($pq['additional_data']['item2_price'] ?? '');
                            @endphp
                            <div class="w-full border-t border-slate-300 mt-2 pt-2 text-black">
                                <div class="grid grid-cols-2 gap-2 text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-[14px] font-bold text-slate-700 block mb-0.5">{{ strtoupper($pq['additional_data']['item1_name'] ?? 'LENGAN PENDEK') }}</span>
                                        <div class="coret-diagonal-preview text-[14px] text-slate-500 font-semibold mb-0.5">
                                            <span>Rp</span>
                                            <span>{{ $item1O['base'] . $item1O['suffix'] }}</span>
                                        </div>
                                        <div style="color: #dc2626; font-weight: bold; display: flex; align-items: flex-start; font-size: 20px;">
                                            <span class="text-[12px] mt-0.5 mr-0.5">Rp</span>
                                            <span>{{ $item1P['base'] }}</span>
                                            <span class="text-[13px] mt-0.5">{{ $item1P['suffix'] }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col items-center border-l border-slate-200">
                                        <span class="text-[14px] font-bold text-slate-700 block mb-0.5">{{ strtoupper($pq['additional_data']['item2_name'] ?? 'LENGAN PANJANG') }}</span>
                                        <div class="coret-diagonal-preview text-[14px] text-slate-500 font-semibold mb-0.5">
                                            <span>Rp</span>
                                            <span>{{ $item2O['base'] . $item2O['suffix'] }}</span>
                                        </div>
                                        <div style="color: #dc2626; font-weight: bold; display: flex; align-items: flex-start; font-size: 20px;">
                                            <span class="text-[12px] mt-0.5 mr-0.5">Rp</span>
                                            <span>{{ $item2P['base'] }}</span>
                                            <span class="text-[13px] mt-0.5">{{ $item2P['suffix'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Double Item list -->
                    @if($pq['layout_type'] === 'double_item')
                        @php
                            $i1O = $this->formatPriceStatic($pq['additional_data']['item1_old_price'] ?? '');
                            $i1P = $this->formatPriceStatic($pq['additional_data']['item1_price'] ?? '');
                            $i2O = $this->formatPriceStatic($pq['additional_data']['item2_old_price'] ?? '');
                            $i2P = $this->formatPriceStatic($pq['additional_data']['item2_price'] ?? '');
                        @endphp
                        <div class="w-full flex flex-col gap-2 py-1">
                            <div class="w-full flex justify-between items-center border-b border-dashed border-slate-300 pb-1.5">
                                <div class="text-left flex flex-col">
                                    <span class="text-sm font-bold text-slate-800">{{ strtoupper($pq['additional_data']['item1_name'] ?? '') }}</span>
                                    @if(!empty($pq['additional_data']['item1_old_price']))
                                        <div class="coret-diagonal-preview text-xs text-slate-500 font-medium align-self-start mt-0.5">
                                            <span>Rp</span>
                                            <span>{{ $i1O['base'] . $i1O['suffix'] }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-[#dc2626] font-bold flex items-start">
                                    <span class="text-[10px] mt-0.5 mr-0.5">Rp</span>
                                    <span class="text-2xl leading-none">{{ $i1P['base'] }}</span>
                                    <span class="text-sm leading-none font-bold mt-0.5">{{ $i1P['suffix'] }}</span>
                                </div>
                            </div>
                            
                            <div class="w-full flex justify-between items-center pt-1">
                                <div class="text-left flex flex-col">
                                    <span class="text-sm font-bold text-slate-800">{{ strtoupper($pq['additional_data']['item2_name'] ?? '') }}</span>
                                    @if(!empty($pq['additional_data']['item2_old_price']))
                                        <div class="coret-diagonal-preview text-xs text-slate-500 font-medium align-self-start mt-0.5">
                                            <span>Rp</span>
                                            <span>{{ $i2O['base'] . $i2O['suffix'] }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-[#dc2626] font-bold flex items-start">
                                    <span class="text-[10px] mt-0.5 mr-0.5">Rp</span>
                                    <span class="text-2xl leading-none">{{ $i2P['base'] }}</span>
                                    <span class="text-sm leading-none font-bold mt-0.5">{{ $i2P['suffix'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>

                <!-- Footer -->
                <div class="w-full border-t border-slate-200 pt-1.5 flex justify-between items-center select-none text-[8px] font-bold text-slate-400">
                    <span>POP FASHION YKR</span>
                    <span class="uppercase">A5 PORTRAIT</span>
                </div>
            </div>
        </div>
    @endforeach
</div>
</div>
