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
        if ($pop) {
            $this->activePreviewPop = $pop->toArray();
            $this->previewQueue = [$this->activePreviewPop];
            $this->frameSize = $pop->frame_size;
            $this->showModal = true;
        }
    }

    public function handlePreviewBulk($ids)
    {
        if (count($ids) === 0) return;
        $items = Pop::whereIn('id', $ids)->get()->toArray();
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
        .pop-card-preview {
            font-family: 'Arial Narrow', 'Archivo Narrow', Arial, sans-serif;
            color: black;
            background-color: white;
            box-sizing: border-box;
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
            background-color: #1e293b;
            transform: rotate(-10deg);
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
                padding: 12px !important;
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
                  <h3 class="text-base font-extrabold text-slate-900">Pratinjau Desain POP</h3>
                  <p class="text-xs text-slate-500 font-medium">Ukuran frame cetak: <span class="font-bold text-indigo-600 uppercase" x-text="frameSize === 'A5' ? 'A5 (Print A6 Landscape)' : (frameSize === 'A4' ? 'A4 (Print A5 Portrait)' : 'A3 (Print A4 Portrait)')"></span></p>
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
                  <div class="pop-card-preview bg-white shadow-lg border border-slate-300 relative transition-all duration-300 flex flex-col justify-between overflow-hidden"
                       :style="
                          frameSize === 'A5' ? 'width: 148mm; height: 105mm;' : 
                          (frameSize === 'A4' ? 'width: 148mm; height: 210mm;' : 'width: 210mm; height: 297mm;')
                       ">
                       
                       <!-- Header Banner -->
                       <div class="bg-[#dc2626] text-white font-bold text-center uppercase flex items-center justify-center shrink-0 w-full"
                            :style="
                               frameSize === 'A5' ? 'padding: 10px 15px;' : 
                               (frameSize === 'A4' ? 'padding: 16px 20px;' : 'padding: 24px 30px;')
                            ">
                           <span class="w-full tracking-wide text-center leading-none"
                                 :style="
                                    frameSize === 'A5' ? 'font-size: 24px;' : 
                                    (frameSize === 'A4' ? 'font-size: 32px;' : 'font-size: 48px;')
                                 ">
                               {{ $activePreviewPop['header_text'] ?: 'HARGA SPESIAL' }}
                           </span>
                       </div>

                       <!-- Content Body -->
                       <div class="flex-grow flex flex-col items-center text-center justify-between py-4 px-5 leading-none" style="display: flex; flex-direction: column; justify-content: space-between; flex-grow: 1;">
                           
                           <!-- Brand Name -->
                           <div class="w-full flex flex-col items-center" :style="frameSize === 'A5' ? 'margin-top: 4px;' : 'margin-top: 12px;'">
                               <span class="font-bold uppercase tracking-wider text-black block"
                                     :style="
                                        frameSize === 'A5' ? 'font-size: 28px; margin-bottom: 2px;' : 
                                        (frameSize === 'A4' ? 'font-size: 36px; margin-bottom: 4px;' : 'font-size: 52px; margin-bottom: 8px;')
                                     ">
                                   {{ $activePreviewPop['brand_name'] }}
                                </span>
                               
                               @if($activePreviewPop['layout_type'] !== 'double_item')
                                   <span class="font-semibold uppercase tracking-widest text-[#475569]"
                                         :style="
                                            frameSize === 'A5' ? 'font-size: 11px;' : 
                                            (frameSize === 'A4' ? 'font-size: 13px;' : 'font-size: 16px;')
                                         ">
                                       {{ $activePreviewPop['product_desc'] }}
                                   </span>
                               @endif
                           </div>

                           <!-- Dynamic Pricing Area -->
                           <div class="flex-grow flex flex-col items-center justify-center w-full" style="display: flex; flex-direction: column; justify-content: center; align-items: center; flex-grow: 1;">
                               
                               <!-- 1. Single Price Layout -->
                               @if($activePreviewPop['layout_type'] === 'single_price')
                                   @php
                                       $priceParts = app()->call([$this, 'formatPriceStatic'], ['val' => $activePreviewPop['primary_price']]);
                                   @endphp
                                   <div class="flex items-start text-[#dc2626] font-bold">
                                       <span class="mr-0.5" :style="frameSize === 'A5' ? 'font-size: 16px; margin-top: 4px;' : (frameSize === 'A4' ? 'font-size: 20px; margin-top: 4px;' : 'font-size: 26px; margin-top: 4px;')">Rp</span>
                                       <span :style="frameSize === 'A5' ? 'font-size: 64px;' : (frameSize === 'A4' ? 'font-size: 88px;' : 'font-size: 128px;')" style="line-height: 0.8; letter-spacing: -2px;">{{ $priceParts['base'] }}</span>
                                       <span :style="frameSize === 'A5' ? 'font-size: 28px;' : (frameSize === 'A4' ? 'font-size: 38px;' : 'font-size: 54px;')" style="line-height: 0.8;">{{ $priceParts['suffix'] }}</span>
                                   </div>
                               @endif

                               <!-- 2. Was / Is Price (Coret) -->
                               @if($activePreviewPop['layout_type'] === 'was_is_price')
                                   @php
                                       $promoParts = app()->call([$this, 'formatPriceStatic'], ['val' => $activePreviewPop['primary_price']]);
                                       $oldParts = app()->call([$this, 'formatPriceStatic'], ['val' => $activePreviewPop['secondary_price']]);
                                   @endphp
                                   <div class="flex flex-col items-center">
                                       <div class="coret-diagonal-preview text-slate-500 font-semibold mb-0.5"
                                            :style="
                                               frameSize === 'A5' ? 'font-size: 20px; margin-bottom: 4px;' : 
                                               (frameSize === 'A4' ? 'font-size: 26px; margin-bottom: 4px;' : 'font-size: 36px; margin-bottom: 4px;')
                                            ">
                                           <span style="font-size: 11px;">Rp</span>
                                           <span>{{ $oldParts['base'] . $oldParts['suffix'] }}</span>
                                       </div>
                                       <div class="flex items-start text-[#dc2626] font-bold">
                                           <span class="mr-0.5" :style="frameSize === 'A5' ? 'font-size: 16px; margin-top: 4px;' : (frameSize === 'A4' ? 'font-size: 20px; margin-top: 4px;' : 'font-size: 26px; margin-top: 4px;')">Rp</span>
                                           <span :style="frameSize === 'A5' ? 'font-size: 64px;' : (frameSize === 'A4' ? 'font-size: 88px;' : 'font-size: 128px;')" style="line-height: 0.8; letter-spacing: -2px;">{{ $promoParts['base'] }}</span>
                                           <span :style="frameSize === 'A5' ? 'font-size: 28px;' : (frameSize === 'A4' ? 'font-size: 38px;' : 'font-size: 54px;')" style="line-height: 0.8;">{{ $promoParts['suffix'] }}</span>
                                       </div>
                                   </div>
                               @endif

                               <!-- 3. Discount Percent Layout -->
                               @if($activePreviewPop['layout_type'] === 'discount_percent')
                                   <div class="flex flex-col items-center w-full">
                                       <div class="flex items-center text-[#dc2626] font-bold" style="line-height: 1;">
                                           @if($activePreviewPop['additional_data']['has_sd'] ?? false)
                                               <span class="text-black font-bold uppercase mr-1.5"
                                                     :style="
                                                        frameSize === 'A5' ? 'font-size: 18px;' : 
                                                        (frameSize === 'A4' ? 'font-size: 24px;' : 'font-size: 32px;')
                                                     ">S/D</span>
                                           @endif
                                           <span :style="frameSize === 'A5' ? 'font-size: 80px;' : (frameSize === 'A4' ? 'font-size: 110px;' : 'font-size: 140px;')" style="line-height: 0.8; letter-spacing: -2px;">{{ $activePreviewPop['additional_data']['discount_percent'] ?? '60' }}</span>
                                           <span :style="frameSize === 'A5' ? 'font-size: 40px; margin-left: 2px;' : (frameSize === 'A4' ? 'font-size: 50px; margin-left: 2px;' : 'font-size: 65px; margin-left: 2px;')">%</span>
                                       </div>
                                       
                                       <!-- Bottom Comparative List -->
                                       @if($activePreviewPop['frame_size'] !== 'A5')
                                           @php
                                               $item1O = app()->call([$this, 'formatPriceStatic'], ['val' => $activePreviewPop['additional_data']['item1_old_price'] ?? '']);
                                               $item1P = app()->call([$this, 'formatPriceStatic'], ['val' => $activePreviewPop['additional_data']['item1_price'] ?? '']);
                                               $item2O = app()->call([$this, 'formatPriceStatic'], ['val' => $activePreviewPop['additional_data']['item2_old_price'] ?? '']);
                                               $item2P = app()->call([$this, 'formatPriceStatic'], ['val' => $activePreviewPop['additional_data']['item2_price'] ?? '']);
                                           @endphp
                                           <div class="w-full border-t border-slate-300 mt-2 pt-2 text-black">
                                               <div class="grid grid-cols-2 gap-2 text-center">
                                                   <div class="flex flex-col items-center">
                                                       <span class="text-[10px] font-bold text-slate-700 block mb-0.5">{{ strtoupper($activePreviewPop['additional_data']['item1_name'] ?? 'LENGAN PENDEK') }}</span>
                                                       <div class="coret-diagonal-preview text-xs text-slate-500 font-semibold mb-0.5">
                                                           <span>Rp</span>
                                                           <span>{{ $item1O['base'] . $item1O['suffix'] }}</span>
                                                       </div>
                                                       <div style="color: #dc2626; font-weight: bold; display: flex; align-items: flex-start; font-size: 14px;">
                                                           <span class="text-[8px] mt-0.5 mr-0.5">Rp</span>
                                                           <span>{{ $item1P['base'] }}</span>
                                                           <span class="text-[9px] mt-0.5">{{ $item1P['suffix'] }}</span>
                                                       </div>
                                                   </div>
                                                   
                                                   <div class="flex flex-col items-center border-l border-slate-200">
                                                       <span class="text-[10px] font-bold text-slate-700 block mb-0.5">{{ strtoupper($activePreviewPop['additional_data']['item2_name'] ?? 'LENGAN PANJANG') }}</span>
                                                       <div class="coret-diagonal-preview text-xs text-slate-500 font-semibold mb-0.5">
                                                           <span>Rp</span>
                                                           <span>{{ $item2O['base'] . $item2O['suffix'] }}</span>
                                                       </div>
                                                       <div style="color: #dc2626; font-weight: bold; display: flex; align-items: flex-start; font-size: 14px;">
                                                           <span class="text-[8px] mt-0.5 mr-0.5">Rp</span>
                                                           <span>{{ $item2P['base'] }}</span>
                                                           <span class="text-[9px] mt-0.5">{{ $item2P['suffix'] }}</span>
                                                       </div>
                                                   </div>
                                               </div>
                                           </div>
                                       @endif
                                   </div>
                               @endif

                               <!-- Double Item list -->
                               @if($activePreviewPop['layout_type'] === 'double_item')
                                   @php
                                       $i1O = app()->call([$this, 'formatPriceStatic'], ['val' => $activePreviewPop['additional_data']['item1_old_price'] ?? '']);
                                       $i1P = app()->call([$this, 'formatPriceStatic'], ['val' => $activePreviewPop['additional_data']['item1_price'] ?? '']);
                                       $i2O = app()->call([$this, 'formatPriceStatic'], ['val' => $activePreviewPop['additional_data']['item2_old_price'] ?? '']);
                                       $i2P = app()->call([$this, 'formatPriceStatic'], ['val' => $activePreviewPop['additional_data']['item2_price'] ?? '']);
                                   @endphp
                                   <div class="w-full flex flex-col gap-2 py-1">
                                       <div class="w-full flex justify-between items-center border-b border-dashed border-slate-300 pb-1.5">
                                           <div class="text-left flex flex-col">
                                               <span class="text-xs font-bold text-slate-800">{{ strtoupper($activePreviewPop['additional_data']['item1_name'] ?? '') }}</span>
                                               @if(!empty($activePreviewPop['additional_data']['item1_old_price']))
                                                   <div class="coret-diagonal-preview text-[10px] text-slate-500 font-medium align-self-start mt-0.5">
                                                       <span>Rp</span>
                                                       <span>{{ $i1O['base'] . $i1O['suffix'] }}</span>
                                                   </div>
                                               @endif
                                           </div>
                                           <div class="text-[#dc2626] font-bold flex items-start">
                                               <span class="text-[9px] mt-0.5 mr-0.5">Rp</span>
                                               <span class="text-xl leading-none">{{ $i1P['base'] }}</span>
                                               <span class="text-[10px] leading-none font-bold mt-0.5">{{ $i1P['suffix'] }}</span>
                                           </div>
                                       </div>
                                       
                                       <div class="w-full flex justify-between items-center pt-1">
                                           <div class="text-left flex flex-col">
                                               <span class="text-xs font-bold text-slate-800">{{ strtoupper($activePreviewPop['additional_data']['item2_name'] ?? '') }}</span>
                                               @if(!empty($activePreviewPop['additional_data']['item2_old_price']))
                                                   <div class="coret-diagonal-preview text-[10px] text-slate-500 font-medium align-self-start mt-0.5">
                                                       <span>Rp</span>
                                                       <span>{{ $i2O['base'] . $i2O['suffix'] }}</span>
                                                   </div>
                                               @endif
                                           </div>
                                           <div class="text-[#dc2626] font-bold flex items-start">
                                               <span class="text-[9px] mt-0.5 mr-0.5">Rp</span>
                                               <span class="text-xl leading-none">{{ $i2P['base'] }}</span>
                                               <span class="text-[10px] leading-none font-bold mt-0.5">{{ $i2P['suffix'] }}</span>
                                           </div>
                                       </div>
                                   </div>
                               @endif

                           </div>

                           <!-- Footer -->
                           <div class="w-full border-t border-slate-200 pt-1.5 flex justify-between items-center select-none text-[8px] font-bold text-slate-400">
                               <span>POP FASHION YKR</span>
                               <span class="uppercase" x-text="frameSize === 'A5' ? 'A6 LANDSCAPE' : (frameSize === 'A4' ? 'A5 PORTRAIT' : 'A4 PORTRAIT')"></span>
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
        <div class="pop-card-preview bg-white relative flex flex-col justify-between overflow-hidden print-card-item-modal"
             style="width: {{ $pq['frame_size'] === 'A5' ? '148mm' : ($pq['frame_size'] === 'A4' ? '148mm' : '210mm') }}; 
                    height: {{ $pq['frame_size'] === 'A5' ? '105mm' : ($pq['frame_size'] === 'A4' ? '210mm' : '297mm') }};
                    margin: 0 auto; page-break-after: always; page-break-inside: avoid; border: none; box-shadow: none; box-sizing: border-box; padding: 12px; font-family: 'Arial Narrow', 'Archivo Narrow', Arial, sans-serif;">
            
            <!-- Red Header Banner -->
            <div class="bg-[#dc2626] text-white font-bold text-center uppercase flex items-center justify-center shrink-0 w-full"
                 style="padding: {{ $pq['frame_size'] === 'A5' ? '10px 15px' : ($pq['frame_size'] === 'A4' ? '16px 20px' : '24px 30px') }};">
                <span class="w-full tracking-wide text-center leading-none"
                      style="font-size: {{ $pq['frame_size'] === 'A5' ? '24px' : ($pq['frame_size'] === 'A4' ? '32px' : '48px') }};">
                    {{ $pq['header_text'] ?: 'HARGA SPESIAL' }}
                </span>
            </div>

            <!-- Content Body -->
            <div class="flex-grow flex flex-col items-center text-center justify-between py-4 px-5 leading-none" style="display: flex; flex-direction: column; justify-content: space-between; flex-grow: 1;">
                
                <!-- Brand block -->
                <div class="w-full flex flex-col items-center" style="margin-top: {{ $pq['frame_size'] === 'A5' ? '4px' : '12px' }};">
                    <span class="font-bold uppercase tracking-wider text-black block"
                          style="font-size: {{ $pq['frame_size'] === 'A5' ? '28px' : ($pq['frame_size'] === 'A4' ? '36px' : '52px') }}; margin-bottom: 2px;">
                        {{ $pq['brand_name'] }}
                    </span>
                    
                    @if($pq['layout_type'] !== 'double_item')
                        <span class="font-semibold uppercase tracking-widest text-[#475569]"
                              style="font-size: {{ $pq['frame_size'] === 'A5' ? '11px' : ($pq['frame_size'] === 'A4' ? '13px' : '16px') }};">
                            {{ $pq['product_desc'] }}
                        </span>
                    @endif
                </div>

                <!-- Price Block -->
                <div class="flex-grow flex flex-col items-center justify-center w-full" style="display: flex; flex-direction: column; justify-content: center; align-items: center; flex-grow: 1;">
                    
                    <!-- 1. Single Price Layout -->
                    @if($pq['layout_type'] === 'single_price')
                        @php
                            $priceParts = app()->call([$this, 'formatPriceStatic'], ['val' => $pq['primary_price']]);
                        @endphp
                        <div style="color: #dc2626; font-weight: bold; display: flex; align-items: flex-start;">
                            <span style="font-size: {{ $pq['frame_size'] === 'A5' ? '16px' : ($pq['frame_size'] === 'A4' ? '20px' : '26px') }}; margin-top: 4px; margin-right: 2px;">Rp</span>
                            <span style="font-size: {{ $pq['frame_size'] === 'A5' ? '64px' : ($pq['frame_size'] === 'A4' ? '88px' : '128px') }}; line-height: 0.8; letter-spacing: -2px;">{{ $priceParts['base'] }}</span>
                            <span style="font-size: {{ $pq['frame_size'] === 'A5' ? '28px' : ($pq['frame_size'] === 'A4' ? '38px' : '54px') }}; line-height: 0.8;">{{ $priceParts['suffix'] }}</span>
                        </div>
                    @endif

                    <!-- 2. Was / Is Price Layout -->
                    @if($pq['layout_type'] === 'was_is_price')
                        @php
                            $promoParts = app()->call([$this, 'formatPriceStatic'], ['val' => $pq['primary_price']]);
                            $oldParts = app()->call([$this, 'formatPriceStatic'], ['val' => $pq['secondary_price']]);
                        @endphp
                        <div style="display: flex; flex-direction: column; align-items: center;">
                            <div class="coret-diagonal-preview" style="color: #64748b; font-weight: 600; font-size: {{ $pq['frame_size'] === 'A5' ? '20px' : ($pq['frame_size'] === 'A4' ? '26px' : '36px') }}; margin-bottom: 4px;">
                                <span style="font-size: 11px;">Rp</span>
                                <span>{{ $oldParts['base'] . $oldParts['suffix'] }}</span>
                            </div>
                            <div style="color: #dc2626; font-weight: bold; display: flex; align-items: flex-start;">
                                <span style="font-size: {{ $pq['frame_size'] === 'A5' ? '16px' : ($pq['frame_size'] === 'A4' ? '20px' : '26px') }}; margin-top: 4px; margin-right: 2px;">Rp</span>
                                <span style="font-size: {{ $pq['frame_size'] === 'A5' ? '64px' : ($pq['frame_size'] === 'A4' ? '88px' : '128px') }}; line-height: 0.8; letter-spacing: -2px;">{{ $promoParts['base'] }}</span>
                                <span style="font-size: {{ $pq['frame_size'] === 'A5' ? '28px' : ($pq['frame_size'] === 'A4' ? '38px' : '54px') }}; line-height: 0.8;">{{ $promoParts['suffix'] }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- 3. Discount Percent Layout -->
                    @if($pq['layout_type'] === 'discount_percent')
                        <div style="display: flex; flex-direction: column; align-items: center; width: 100%;">
                            <div style="color: #dc2626; font-weight: bold; display: flex; align-items: center; line-height: 1;">
                                @if($pq['additional_data']['has_sd'] ?? false)
                                    <span style="color: black; font-size: {{ $pq['frame_size'] === 'A5' ? '18px' : ($pq['frame_size'] === 'A4' ? '24px' : '32px') }}; margin-right: 6px; font-weight: bold;">S/D</span>
                                @endif
                                <span style="font-size: {{ $pq['frame_size'] === 'A5' ? '80px' : ($pq['frame_size'] === 'A4' ? '110px' : '140px') }}; line-height: 0.8; letter-spacing: -2px;">{{ $pq['additional_data']['discount_percent'] ?? '60' }}</span>
                                <span style="font-size: {{ $pq['frame_size'] === 'A5' ? '40px' : ($pq['frame_size'] === 'A4' ? '50px' : '65px') }}; margin-left: 2px;">%</span>
                            </div>
                            
                            @if($pq['frame_size'] !== 'A5')
                                @php
                                    $item1O = app()->call([$this, 'formatPriceStatic'], ['val' => $pq['additional_data']['item1_old_price'] ?? '']);
                                    $item1P = app()->call([$this, 'formatPriceStatic'], ['val' => $pq['additional_data']['item1_price'] ?? '']);
                                    $item2O = app()->call([$this, 'formatPriceStatic'], ['val' => $pq['additional_data']['item2_old_price'] ?? '']);
                                    $item2P = app()->call([$this, 'formatPriceStatic'], ['val' => $pq['additional_data']['item2_price'] ?? '']);
                                @endphp
                                <div style="width: 100%; border-top: 1px solid #cbd5e1; margin-top: 8px; padding-top: 8px; color: black;">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; text-align: center;">
                                        <div style="display: flex; flex-direction: column; align-items: center;">
                                            <span style="font-size: 10px; font-weight: bold; color: #475569; margin-bottom: 2px;">{{ strtoupper($pq['additional_data']['item1_name'] ?? 'LENGAN PENDEK') }}</span>
                                            <div class="coret-diagonal-preview" style="font-size: 11px; color: #64748b; font-weight: 600;">
                                                <span>Rp</span>
                                                <span>{{ $item1O['base'] . $item1O['suffix'] }}</span>
                                            </div>
                                            <div style="color: #dc2626; font-weight: bold; display: flex; align-items: flex-start; font-size: 14px;">
                                                <span style="font-size: 8px; margin-top: 2px;">Rp</span>
                                                <span>{{ $item1P['base'] }}</span>
                                                <span style="font-size: 9px; margin-top: 2px;">{{ $item1P['suffix'] }}</span>
                                            </div>
                                        </div>
                                        <div style="display: flex; flex-direction: column; align-items: center; border-left: 1px solid #e2e8f0;">
                                            <span style="font-size: 10px; font-weight: bold; color: #475569; margin-bottom: 2px;">{{ strtoupper($pq['additional_data']['item2_name'] ?? 'LENGAN PANJANG') }}</span>
                                            <div class="coret-diagonal-preview" style="font-size: 11px; color: #64748b; font-weight: 600;">
                                                <span>Rp</span>
                                                <span>{{ $item2O['base'] . $item2O['suffix'] }}</span>
                                            </div>
                                            <div style="color: #dc2626; font-weight: bold; display: flex; align-items: flex-start; font-size: 14px;">
                                                <span style="font-size: 8px; margin-top: 2px;">Rp</span>
                                                <span>{{ $item2P['base'] }}</span>
                                                <span style="font-size: 9px; margin-top: 2px;">{{ $item2P['suffix'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- 4. Double Item Layout -->
                    @if($pq['layout_type'] === 'double_item')
                        @php
                            $i1O = app()->call([$this, 'formatPriceStatic'], ['val' => $pq['additional_data']['item1_old_price'] ?? '']);
                            $i1P = app()->call([$this, 'formatPriceStatic'], ['val' => $pq['additional_data']['item1_price'] ?? '']);
                            $i2O = app()->call([$this, 'formatPriceStatic'], ['val' => $pq['additional_data']['item2_old_price'] ?? '']);
                            $i2P = app()->call([$this, 'formatPriceStatic'], ['val' => $pq['additional_data']['item2_price'] ?? '']);
                        @endphp
                        <div style="width: 100%; display: flex; flex-direction: column; gap: 8px; padding: 4px 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px dashed #cbd5e1; padding-bottom: 6px;">
                                <div style="text-align: left; display: flex; flex-direction: column;">
                                    <span style="font-size: 12px; font-weight: bold; color: #1e293b;">{{ strtoupper($pq['additional_data']['item1_name'] ?? '') }}</span>
                                    @if(!empty($pq['additional_data']['item1_old_price']))
                                        <div class="coret-diagonal-preview" style="font-size: 10px; color: #64748b; font-weight: 500; align-self: flex-start; margin-top: 1px;">
                                            <span>Rp</span>
                                            <span>{{ $i1O['base'] . $i1O['suffix'] }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div style="color: #dc2626; font-weight: bold; display: flex; align-items: flex-start;">
                                    <span style="font-size: 9px; margin-top: 2px;">Rp</span>
                                    <span style="font-size: 24px; line-height: 0.8;">{{ $i1P['base'] }}</span>
                                    <span style="font-size: 12px; line-height: 0.8; font-weight: bold;">{{ $i1P['suffix'] }}</span>
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 4px;">
                                <div style="text-align: left; display: flex; flex-direction: column;">
                                    <span style="font-size: 12px; font-weight: bold; color: #1e293b;">{{ strtoupper($pq['additional_data']['item2_name'] ?? '') }}</span>
                                    @if(!empty($pq['additional_data']['item2_old_price']))
                                        <div class="coret-diagonal-preview" style="font-size: 10px; color: #64748b; font-weight: 500; align-self: flex-start; margin-top: 1px;">
                                            <span>Rp</span>
                                            <span>{{ $i2O['base'] . $i2O['suffix'] }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div style="color: #dc2626; font-weight: bold; display: flex; align-items: flex-start;">
                                    <span style="font-size: 9px; margin-top: 2px;">Rp</span>
                                    <span style="font-size: 24px; line-height: 0.8;">{{ $i2P['base'] }}</span>
                                    <span style="font-size: 12px; line-height: 0.8; font-weight: bold;">{{ $i2P['suffix'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>

                <!-- Footer -->
                <div style="width: 100%; border-top: 1px solid #e2e8f0; padding-top: 6px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 8px; font-weight: 600; color: #64748b;">POP FASHION YKR</span>
                    <span style="font-size: 8px; font-weight: 600; color: #64748b;">
                        {{ $pq['frame_size'] === 'A5' ? 'A6 LANDSCAPE' : ($pq['frame_size'] === 'A4' ? 'A5 PORTRAIT' : 'A4 PORTRAIT') }}
                    </span>
                </div>

            </div>
        </div>
    @endforeach
</div>
</div>
