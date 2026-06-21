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
        if ($pop && $pop->frame_size === 'A5') {
            $this->activePreviewPop = $pop->toArray();
            $this->previewQueue = [$this->activePreviewPop];
            $this->frameSize = $pop->frame_size;
            $this->showModal = true;
        }
    }

    public function handlePreviewBulk($ids)
    {
        if (count($ids) === 0) return;
        $items = Pop::whereIn('id', $ids)->where('frame_size', 'A5')->get()->toArray();
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
            background-color: #dc2626; /* Red line for strike-through */
            transform: rotate(-8deg);
        }
        
        /* Specific CSS rules for POP A5 (printed on A6 Landscape) */
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
        }
        
        .pop-card-a5 .header-banner-a5 {
            background-color: #dc2626 !important;
            color: white !important;
            text-align: center;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 52px; /* Fit 40pt text */
            box-sizing: border-box;
            padding: 0 10px;
        }
        
        .pop-card-a5 .header-banner-a5 span {
            font-size: 40pt !important;
            font-weight: 700 !important;
            line-height: 1;
            letter-spacing: -0.5px;
        }
        
        .pop-card-a5 .brand-name-a5 {
            font-size: 40pt !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            color: black !important;
            line-height: 1;
            margin-top: 8px;
            letter-spacing: -0.5px;
            text-align: center;
        }
        
        .pop-card-a5 .product-desc-a5 {
            font-size: 18pt !important;
            font-weight: 400 !important;
            text-transform: uppercase;
            color: #334155 !important;
            line-height: 1.2;
            margin-top: 1px;
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
        
        .pop-card-a5 .price-wrapper-a5 {
            display: flex;
            align-items: flex-start;
            color: #dc2626 !important;
            font-weight: 700 !important;
            line-height: 0.85;
        }
        
        .pop-card-a5 .price-rp-a5 {
            font-size: 18pt !important;
            font-weight: 400 !important;
            margin-top: 6px;
            margin-right: 2px;
            line-height: 1;
        }
        
        .pop-card-a5 .price-base-a5 {
            font-size: 70pt !important;
            font-weight: 700 !important;
            letter-spacing: -2px;
            line-height: 0.8;
        }
        
        .pop-card-a5 .price-suffix-a5 {
            font-size: 30pt !important;
            font-weight: 700 !important;
            line-height: 0.8;
            margin-top: 2px;
        }

        /* Double Item A5 */
        .pop-card-a5 .double-container-a5 {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding: 0 16px;
            box-sizing: border-box;
        }
        
        .pop-card-a5 .double-row-a5 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        
        .pop-card-a5 .double-row-border-a5 {
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 4px;
        }
        
        .pop-card-a5 .double-left-a5 {
            text-align: left;
            display: flex;
            flex-direction: column;
        }
        
        .pop-card-a5 .double-name-a5 {
            font-size: 13pt !important;
            font-weight: 700 !important;
            color: black !important;
            text-transform: uppercase;
        }
        
        .pop-card-a5 .double-was-a5 {
            font-size: 9pt !important;
            color: #64748b !important;
            font-weight: 400 !important;
            text-decoration: line-through;
            margin-top: 1px;
        }
        
        .pop-card-a5 .double-right-a5 {
            display: flex;
            align-items: flex-start;
            color: #dc2626 !important;
            font-weight: 700 !important;
        }
        
        .pop-card-a5 .double-rp-a5 {
            font-size: 10pt !important;
            font-weight: 400 !important;
            margin-top: 1px;
            margin-right: 1px;
        }
        
        .pop-card-a5 .double-price-base-a5 {
            font-size: 24pt !important;
            font-weight: 700 !important;
            line-height: 0.8;
        }
        
        .pop-card-a5 .double-price-suffix-a5 {
            font-size: 13pt !important;
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
                padding: 0px !important; /* Edge-to-edge for printing */
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
                  <h3 class="text-base font-extrabold text-slate-900">Pratinjau Desain POP A5</h3>
                  <p class="text-xs text-slate-500 font-medium">Ukuran frame cetak: <span class="font-bold text-indigo-600 uppercase">A5 (Print A6 Landscape)</span></p>
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
                  <div class="pop-card-preview bg-white shadow-lg border border-slate-300 relative transition-all duration-300 flex flex-col justify-between overflow-hidden pop-card-a5"
                       style="width: 148mm; height: 105mm;">
                       
                       <!-- Header Banner -->
                       <div class="header-banner-a5">
                           <span>{{ $activePreviewPop['header_text'] ?: 'HARGA SPESIAL' }}</span>
                       </div>

                       <!-- Content Body -->
                       <div class="flex-grow flex flex-col justify-between py-3 px-5 leading-none">
                           
                           <!-- Brand Block -->
                           <div>
                               <div class="brand-name-a5">{{ $activePreviewPop['brand_name'] }}</div>
                               @if($activePreviewPop['layout_type'] !== 'double_item')
                                   <div class="product-desc-a5">{{ $activePreviewPop['product_desc'] }}</div>
                               @endif
                           </div>

                           <!-- Price Area -->
                           <div class="price-area-a5">
                               <!-- 1. Single Price Layout -->
                               @if($activePreviewPop['layout_type'] === 'single_price')
                                   @php
                                       $priceParts = $this->formatPriceStatic($activePreviewPop['primary_price']);
                                   @endphp
                                   <div class="price-wrapper-a5">
                                       <span class="price-rp-a5">Rp</span>
                                       <span class="price-base-a5">{{ $priceParts['base'] }}</span>
                                       <span class="price-suffix-a5">{{ $priceParts['suffix'] }}</span>
                                   </div>
                               @endif

                               <!-- 2. Was / Is Price (Coret) -->
                               @if($activePreviewPop['layout_type'] === 'was_is_price')
                                   @php
                                       $promoParts = $this->formatPriceStatic($activePreviewPop['primary_price']);
                                       $oldParts = $this->formatPriceStatic($activePreviewPop['secondary_price']);
                                   @endphp
                                   <div class="was-is-wrapper-a5 flex flex-col items-center">
                                       <div class="was-price-row coret-diagonal-preview">
                                           <span class="was-rp">Rp</span>
                                           <span class="was-price-value">{{ $oldParts['base'] . $oldParts['suffix'] }}</span>
                                       </div>
                                       <div class="price-wrapper-a5">
                                           <span class="price-rp-a5">Rp</span>
                                           <span class="price-base-a5">{{ $promoParts['base'] }}</span>
                                           <span class="price-suffix-a5">{{ $promoParts['suffix'] }}</span>
                                       </div>
                                   </div>
                               @endif

                               <!-- 3. Discount Percent Layout (rendered as range s.d. for A5) -->
                               @if($activePreviewPop['layout_type'] === 'discount_percent')
                                   @php
                                       $p1 = $this->formatPriceStatic($activePreviewPop['additional_data']['item1_price'] ?? '');
                                       $p2 = $this->formatPriceStatic($activePreviewPop['additional_data']['item2_price'] ?? '');
                                   @endphp
                                   <div class="flex flex-col items-center justify-center gap-1.5 w-full">
                                       <div class="price-wrapper-a5">
                                           <span class="price-rp-a5">Rp</span>
                                           <span class="price-base-a5">{{ $p1['base'] }}</span>
                                           <span class="price-suffix-a5">{{ $p1['suffix'] }}</span>
                                       </div>
                                       <div class="text-[14pt] font-bold text-slate-800 uppercase tracking-wider leading-none my-0.5">
                                           S/D
                                       </div>
                                       <div class="price-wrapper-a5">
                                           <span class="price-rp-a5">Rp</span>
                                           <span class="price-base-a5">{{ $p2['base'] }}</span>
                                           <span class="price-suffix-a5">{{ $p2['suffix'] }}</span>
                                       </div>
                                   </div>
                               @endif

                               <!-- 4. Double Item Layout -->
                               @if($activePreviewPop['layout_type'] === 'double_item')
                                   @php
                                       $i1O = $this->formatPriceStatic($activePreviewPop['additional_data']['item1_old_price'] ?? '');
                                       $i1P = $this->formatPriceStatic($activePreviewPop['additional_data']['item1_price'] ?? '');
                                       $i2O = $this->formatPriceStatic($activePreviewPop['additional_data']['item2_old_price'] ?? '');
                                       $i2P = $this->formatPriceStatic($activePreviewPop['additional_data']['item2_price'] ?? '');
                                   @endphp
                                   <div class="double-container-a5">
                                       <div class="double-row-a5 double-row-border-a5">
                                           <div class="double-left-a5">
                                               <span class="double-name-a5">{{ $activePreviewPop['additional_data']['item1_name'] ?? '' }}</span>
                                               @if(!empty($activePreviewPop['additional_data']['item1_old_price']))
                                                   <span class="double-was-a5">Rp {{ $i1O['base'] . $i1O['suffix'] }}</span>
                                               @endif
                                           </div>
                                           <div class="double-right-a5">
                                               <span class="double-rp-a5">Rp</span>
                                               <span class="double-price-base-a5">{{ $i1P['base'] }}</span>
                                               <span class="double-price-suffix-a5">{{ $i1P['suffix'] }}</span>
                                           </div>
                                       </div>
                                       <div class="double-row-a5">
                                           <div class="double-left-a5">
                                               <span class="double-name-a5">{{ $activePreviewPop['additional_data']['item2_name'] ?? '' }}</span>
                                               @if(!empty($activePreviewPop['additional_data']['item2_old_price']))
                                                   <span class="double-was-a5">Rp {{ $i2O['base'] . $i2O['suffix'] }}</span>
                                               @endif
                                           </div>
                                           <div class="double-right-a5">
                                               <span class="double-rp-a5">Rp</span>
                                               <span class="double-price-base-a5">{{ $i2P['base'] }}</span>
                                               <span class="double-price-suffix-a5">{{ $i2P['suffix'] }}</span>
                                           </div>
                                       </div>
                                   </div>
                               @endif
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
        <div class="pop-card-preview bg-white relative flex flex-col justify-between overflow-hidden print-card-item-modal pop-card-a5"
             style="width: 148mm; height: 105mm; margin: 0 auto; page-break-after: always; page-break-inside: avoid; border: none; box-shadow: none; box-sizing: border-box; padding: 0px; font-family: 'Arial Narrow', 'Archivo Narrow', Arial, sans-serif;">
            
            <!-- Header Banner -->
            <div class="header-banner-a5">
                <span>{{ $pq['header_text'] ?: 'HARGA SPESIAL' }}</span>
            </div>

            <!-- Content Body -->
            <div class="flex-grow flex flex-col justify-between py-3 px-5 leading-none">
                
                <!-- Brand Block -->
                <div>
                    <div class="brand-name-a5">{{ $pq['brand_name'] }}</div>
                    @if($pq['layout_type'] !== 'double_item')
                        <div class="product-desc-a5">{{ $pq['product_desc'] }}</div>
                    @endif
                </div>

                <!-- Price Area -->
                <div class="price-area-a5">
                    <!-- 1. Single Price Layout -->
                    @if($pq['layout_type'] === 'single_price')
                        @php
                            $priceParts = $this->formatPriceStatic($pq['primary_price']);
                        @endphp
                        <div class="price-wrapper-a5">
                            <span class="price-rp-a5">Rp</span>
                            <span class="price-base-a5">{{ $priceParts['base'] }}</span>
                            <span class="price-suffix-a5">{{ $priceParts['suffix'] }}</span>
                        </div>
                    @endif

                    <!-- 2. Was / Is Price (Coret) -->
                    @if($pq['layout_type'] === 'was_is_price')
                        @php
                            $promoParts = $this->formatPriceStatic($pq['primary_price']);
                            $oldParts = $this->formatPriceStatic($pq['secondary_price']);
                        @endphp
                        <div class="was-is-wrapper-a5 flex flex-col items-center">
                            <div class="was-price-row coret-diagonal-preview">
                                <span class="was-rp">Rp</span>
                                <span class="was-price-value">{{ $oldParts['base'] . $oldParts['suffix'] }}</span>
                            </div>
                            <div class="price-wrapper-a5">
                                <span class="price-rp-a5">Rp</span>
                                <span class="price-base-a5">{{ $promoParts['base'] }}</span>
                                <span class="price-suffix-a5">{{ $promoParts['suffix'] }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- 3. Discount Percent Layout (rendered as range s.d. for A5) -->
                    @if($pq['layout_type'] === 'discount_percent')
                        @php
                            $p1 = $this->formatPriceStatic($pq['additional_data']['item1_price'] ?? '');
                            $p2 = $this->formatPriceStatic($pq['additional_data']['item2_price'] ?? '');
                        @endphp
                        <div class="flex flex-col items-center justify-center gap-1.5 w-full">
                            <div class="price-wrapper-a5">
                                <span class="price-rp-a5">Rp</span>
                                <span class="price-base-a5">{{ $p1['base'] }}</span>
                                <span class="price-suffix-a5">{{ $p1['suffix'] }}</span>
                            </div>
                            <div class="text-[14pt] font-bold text-slate-800 uppercase tracking-wider leading-none my-0.5">
                                S/D
                            </div>
                            <div class="price-wrapper-a5">
                                <span class="price-rp-a5">Rp</span>
                                <span class="price-base-a5">{{ $p2['base'] }}</span>
                                <span class="price-suffix-a5">{{ $p2['suffix'] }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- 4. Double Item Layout -->
                    @if($pq['layout_type'] === 'double_item')
                        @php
                            $i1O = $this->formatPriceStatic($pq['additional_data']['item1_old_price'] ?? '');
                            $i1P = $this->formatPriceStatic($pq['additional_data']['item1_price'] ?? '');
                            $i2O = $this->formatPriceStatic($pq['additional_data']['item2_old_price'] ?? '');
                            $i2P = $this->formatPriceStatic($pq['additional_data']['item2_price'] ?? '');
                        @endphp
                        <div class="double-container-a5">
                            <div class="double-row-a5 double-row-border-a5">
                                <div class="double-left-a5">
                                    <span class="double-name-a5">{{ $pq['additional_data']['item1_name'] ?? '' }}</span>
                                    @if(!empty($pq['additional_data']['item1_old_price']))
                                        <span class="double-was-a5">Rp {{ $i1O['base'] . $i1O['suffix'] }}</span>
                                    @endif
                                </div>
                                <div class="double-right-a5">
                                    <span class="double-rp-a5">Rp</span>
                                    <span class="double-price-base-a5">{{ $i1P['base'] }}</span>
                                    <span class="double-price-suffix-a5">{{ $i1P['suffix'] }}</span>
                                </div>
                            </div>
                            <div class="double-row-a5">
                                <div class="double-left-a5">
                                    <span class="double-name-a5">{{ $pq['additional_data']['item2_name'] ?? '' }}</span>
                                    @if(!empty($pq['additional_data']['item2_old_price']))
                                        <span class="double-was-a5">Rp {{ $i2O['base'] . $i2O['suffix'] }}</span>
                                    @endif
                                </div>
                                <div class="double-right-a5">
                                    <span class="double-rp-a5">Rp</span>
                                    <span class="double-price-base-a5">{{ $i2P['base'] }}</span>
                                    <span class="double-price-suffix-a5">{{ $i2P['suffix'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="h-2"></div>
            </div>
        </div>
    @endforeach
</div>
</div>
