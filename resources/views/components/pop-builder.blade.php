<?php

use App\Models\Pop;
use Livewire\Component;

new class extends Component
{
    public $activeView = 'a5-single-price'; // default view
    
    // Sidebar toggle state (desktop hide/show)
    public $sidebarCollapsed = false;

    public function selectView($view)
    {
        $this->activeView = $view;
    }

    public function toggleSidebar()
    {
        $this->sidebarCollapsed = !$this->sidebarCollapsed;
    }

    public function logout()
    {
        session()->forget('authenticated');
        session()->forget('user_id');
        session()->forget('user_name');
        session()->forget('user_email');
        return redirect('/');
    }
};
?>

<div class="flex flex-col lg:flex-row min-h-screen bg-gradient-to-tr from-[#f1f5f9] to-[#e2e8f0] w-full text-slate-800"
     x-data="{ 
        mobileMenuOpen: false,
        sidebarCollapsed: @entangle('sidebarCollapsed'),
        openA5: true,
        openA4: true,
        openA3: true,
        notification: { show: false, message: '', type: 'success' },
        confirmModal: {
            show: false,
            title: '',
            message: '',
            icon: 'delete',
            confirmLabel: 'Ya, Hapus',
            confirmClass: 'bg-red-600 hover:bg-red-700',
            action: null
        },
        openConfirm(opts) {
            this.confirmModal.title = opts.title || 'Konfirmasi';
            this.confirmModal.message = opts.message || 'Apakah Anda yakin?';
            this.confirmModal.icon = opts.icon || 'delete';
            this.confirmModal.confirmLabel = opts.confirmLabel || 'Ya, Lanjutkan';
            this.confirmModal.confirmClass = opts.confirmClass || 'bg-red-600 hover:bg-red-700';
            this.confirmModal.action = opts.action;
            this.confirmModal.show = true;
        },
        runConfirm() {
            if (typeof this.confirmModal.action === 'function') {
                this.confirmModal.action();
            }
            this.confirmModal.show = false;
        }
     }"
     x-on:notify.window="
        notification.show = true; 
        notification.message = $event.detail[0].message; 
        notification.type = $event.detail[0].type; 
        setTimeout(() => notification.show = false, 3000);
     "
     x-on:request-confirm.window="openConfirm($event.detail)">

    <!-- MOBILE HEADER NAVIGATION BAR (no-print) -->
    <header class="no-print h-16 bg-white border-b border-slate-200 px-6 flex items-center justify-between lg:hidden shrink-0 z-30">
        <div class="flex items-center gap-3">
            <div class="h-9 w-9 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                %
            </div>
            <div>
                <h2 class="text-sm font-extrabold tracking-tight text-slate-800 leading-none">POP YKR</h2>
                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Dashboard</span>
            </div>
        </div>
        <button type="button" 
                @click="mobileMenuOpen = !mobileMenuOpen" 
                class="text-slate-500 p-2 rounded-lg hover:bg-slate-100 transition focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </header>

    <!-- MOBILE SIDEBAR BACKDROP OVERLAY -->
    <div x-show="mobileMenuOpen" 
         @click="mobileMenuOpen = false" 
         class="fixed inset-0 bg-slate-900/40 z-35 lg:hidden" 
         style="display:none;" 
         x-transition></div>

    <!-- SIDEBAR: Solid White Theme with Accordion Sub-menus (no-print) -->
    <aside :class="{
                'translate-x-0': mobileMenuOpen,
                '-translate-x-full': !mobileMenuOpen,
                'lg:w-64 lg:p-6': !sidebarCollapsed,
                'lg:w-20 lg:p-4 lg:overflow-x-hidden': sidebarCollapsed
            }" 
           class="no-print fixed inset-y-0 left-0 w-64 p-6 bg-white border-r border-slate-200 flex flex-col shrink-0 z-40 transition-all duration-300 ease-in-out lg:static lg:z-10 lg:translate-x-0 h-screen lg:h-auto shadow-md">
        
        <!-- Sidebar Brand Header -->
        <div class="flex items-center justify-between mb-8 shrink-0" :class="sidebarCollapsed ? 'flex-col gap-4 justify-center' : 'flex-row'">
            <div class="flex items-center gap-3" :class="sidebarCollapsed ? 'justify-center' : ''">
                <div class="h-10 w-10 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-xl shadow-md shadow-indigo-100 shrink-0">
                    %
                </div>
                <div x-show="!sidebarCollapsed" x-transition.opacity>
                    <h2 class="text-base font-extrabold tracking-tight text-slate-800">POP YKR</h2>
                    <span class="text-[10px] text-slate-400 font-extrabold uppercase tracking-wider">Promotion</span>
                </div>
            </div>
            
            <!-- Collapse Button (Inside Sidebar, visible on desktop) -->
            <button type="button" 
                    wire:click="toggleSidebar"
                    class="hidden lg:flex text-slate-400 hover:text-slate-600 hover:bg-slate-50 p-1.5 rounded-lg border border-slate-200/50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" :class="sidebarCollapsed ? 'rotate-180' : ''">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
        </div>

        <!-- Sidebar Navigation List -->
        <nav class="flex-1 space-y-4 overflow-y-auto pr-1">
            
            <!-- POP MENU Header Group -->
            <div class="space-y-2">
                <div x-show="!sidebarCollapsed" class="flex items-center gap-2 px-3 py-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">
                    <span>POP Sub-fitur</span>
                </div>
                
                <!-- 1. POP A5 Accordion -->
                <div class="space-y-1">
                    <button type="button" 
                            @click="if (sidebarCollapsed) { sidebarCollapsed = false; } openA5 = !openA5"
                            class="w-full flex items-center px-3 py-2.5 rounded-xl text-slate-700 hover:bg-slate-50 transition-all duration-200 hover:translate-x-1.5"
                            :class="sidebarCollapsed ? 'justify-center' : 'justify-start gap-2.5'"
                            title="POP Frame A5">
                        <svg class="h-6 w-6 text-indigo-500 transition-transform hover:scale-110 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        <span x-show="!sidebarCollapsed" x-transition.opacity class="text-lg font-light text-slate-700">POP Frame A5</span>
                    </button>
                    
                    <div x-show="openA5 && !sidebarCollapsed" x-collapse class="pl-5 space-y-1">
                        <button type="button" 
                                wire:click="selectView('a5-single-price')"
                                @click="mobileMenuOpen = false"
                                class="w-full text-left px-3 py-2 rounded-lg text-base font-light transition-all duration-200 hover:translate-x-1.5 {{ $activeView === 'a5-single-price' ? 'bg-indigo-50 text-indigo-600 font-normal' : 'text-slate-500 hover:text-slate-800' }} flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Harga Tunggal</span>
                        </button>
                        <button type="button" 
                                wire:click="selectView('a5-was-is-price')"
                                @click="mobileMenuOpen = false"
                                class="w-full text-left px-3 py-2 rounded-lg text-base font-light transition-all duration-200 hover:translate-x-1.5 {{ $activeView === 'a5-was-is-price' ? 'bg-indigo-50 text-indigo-600 font-normal' : 'text-slate-500 hover:text-slate-800' }} flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M6 20h12a2 2 0 002-2v-12a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2zM9 15l6-6" />
                            </svg>
                            <span>Harga Coret</span>
                        </button>
                        <button type="button" 
                                wire:click="selectView('a5-discount-percent')"
                                @click="mobileMenuOpen = false"
                                class="w-full text-left px-3 py-2 rounded-lg text-base font-light transition-all duration-200 hover:translate-x-1.5 {{ $activeView === 'a5-discount-percent' ? 'bg-indigo-50 text-indigo-600 font-normal' : 'text-slate-500 hover:text-slate-800' }} flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l6 6m0-6L9 15m12-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Diskon %</span>
                        </button>
                        <button type="button" 
                                wire:click="selectView('a5-double-item')"
                                @click="mobileMenuOpen = false"
                                class="w-full text-left px-3 py-2 rounded-lg text-base font-light transition-all duration-200 hover:translate-x-1.5 {{ $activeView === 'a5-double-item' ? 'bg-indigo-50 text-indigo-600 font-normal' : 'text-slate-500 hover:text-slate-800' }} flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H10a2 2 0 01-2-2z" />
                            </svg>
                            <span>Daftar Dua Item</span>
                        </button>
                    </div>
                </div>

                <!-- 2. POP A4 Accordion -->
                <div class="space-y-1">
                    <button type="button" 
                            @click="if (sidebarCollapsed) { sidebarCollapsed = false; } openA4 = !openA4"
                            class="w-full flex items-center px-3 py-2.5 rounded-xl text-slate-700 hover:bg-slate-50 transition-all duration-200 hover:translate-x-1.5"
                            :class="sidebarCollapsed ? 'justify-center' : 'justify-start gap-2.5'"
                            title="POP Frame A4">
                        <svg class="h-6 w-6 text-emerald-500 transition-transform hover:scale-110 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" x-transition.opacity class="text-lg font-light text-slate-700">POP Frame A4</span>
                    </button>
                    
                    <div x-show="openA4 && !sidebarCollapsed" x-collapse class="pl-5 space-y-1">
                        <button type="button" 
                                wire:click="selectView('a4-single-price')"
                                @click="mobileMenuOpen = false"
                                class="w-full text-left px-3 py-2 rounded-lg text-base font-light transition-all duration-200 hover:translate-x-1.5 {{ $activeView === 'a4-single-price' ? 'bg-indigo-50 text-indigo-600 font-normal' : 'text-slate-500 hover:text-slate-800' }} flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Harga Tunggal</span>
                        </button>
                        <button type="button" 
                                wire:click="selectView('a4-was-is-price')"
                                @click="mobileMenuOpen = false"
                                class="w-full text-left px-3 py-2 rounded-lg text-base font-light transition-all duration-200 hover:translate-x-1.5 {{ $activeView === 'a4-was-is-price' ? 'bg-indigo-50 text-indigo-600 font-normal' : 'text-slate-500 hover:text-slate-800' }} flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M6 20h12a2 2 0 002-2v-12a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2zM9 15l6-6" />
                            </svg>
                            <span>Harga Coret</span>
                        </button>
                        <button type="button" 
                                wire:click="selectView('a4-discount-percent')"
                                @click="mobileMenuOpen = false"
                                class="w-full text-left px-3 py-2 rounded-lg text-base font-light transition-all duration-200 hover:translate-x-1.5 {{ $activeView === 'a4-discount-percent' ? 'bg-indigo-50 text-indigo-600 font-normal' : 'text-slate-500 hover:text-slate-800' }} flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l6 6m0-6L9 15m12-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Diskon %</span>
                        </button>
                        <button type="button" 
                                wire:click="selectView('a4-double-item')"
                                @click="mobileMenuOpen = false"
                                class="w-full text-left px-3 py-2 rounded-lg text-base font-light transition-all duration-200 hover:translate-x-1.5 {{ $activeView === 'a4-double-item' ? 'bg-indigo-50 text-indigo-600 font-normal' : 'text-slate-500 hover:text-slate-800' }} flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H10a2 2 0 01-2-2z" />
                            </svg>
                            <span>Daftar Dua Item</span>
                        </button>
                    </div>
                </div>

                <!-- 3. POP A3 Accordion -->
                <div class="space-y-1">
                    <button type="button" 
                            @click="if (sidebarCollapsed) { sidebarCollapsed = false; } openA3 = !openA3"
                            class="w-full flex items-center px-3 py-2.5 rounded-xl text-slate-700 hover:bg-slate-50 transition-all duration-200 hover:translate-x-1.5"
                            :class="sidebarCollapsed ? 'justify-center' : 'justify-start gap-2.5'"
                            title="POP Frame A3">
                        <svg class="h-6 w-6 text-amber-500 transition-transform hover:scale-110 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span x-show="!sidebarCollapsed" x-transition.opacity class="text-lg font-light text-slate-700">POP Frame A3</span>
                    </button>
                    
                    <div x-show="openA3 && !sidebarCollapsed" x-collapse class="pl-5 space-y-1">
                        <button type="button" 
                                wire:click="selectView('a3-single-price')"
                                @click="mobileMenuOpen = false"
                                class="w-full text-left px-3 py-2 rounded-lg text-base font-light transition-all duration-200 hover:translate-x-1.5 {{ $activeView === 'a3-single-price' ? 'bg-indigo-50 text-indigo-600 font-normal' : 'text-slate-500 hover:text-slate-800' }} flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Harga Tunggal</span>
                        </button>
                        <button type="button" 
                                wire:click="selectView('a3-was-is-price')"
                                @click="mobileMenuOpen = false"
                                class="w-full text-left px-3 py-2 rounded-lg text-base font-light transition-all duration-200 hover:translate-x-1.5 {{ $activeView === 'a3-was-is-price' ? 'bg-indigo-50 text-indigo-600 font-normal' : 'text-slate-500 hover:text-slate-800' }} flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M6 20h12a2 2 0 002-2v-12a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2zM9 15l6-6" />
                            </svg>
                            <span>Harga Coret</span>
                        </button>
                        <button type="button" 
                                wire:click="selectView('a3-discount-percent')"
                                @click="mobileMenuOpen = false"
                                class="w-full text-left px-3 py-2 rounded-lg text-base font-light transition-all duration-200 hover:translate-x-1.5 {{ $activeView === 'a3-discount-percent' ? 'bg-indigo-50 text-indigo-600 font-normal' : 'text-slate-500 hover:text-slate-800' }} flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 9l6 6m0-6L9 15m12-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Diskon %</span>
                        </button>
                        <button type="button" 
                                wire:click="selectView('a3-double-item')"
                                @click="mobileMenuOpen = false"
                                class="w-full text-left px-3 py-2 rounded-lg text-base font-light transition-all duration-200 hover:translate-x-1.5 {{ $activeView === 'a3-double-item' ? 'bg-indigo-50 text-indigo-600 font-normal' : 'text-slate-500 hover:text-slate-800' }} flex items-center gap-2">
                            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H10a2 2 0 01-2-2z" />
                            </svg>
                            <span>Daftar Dua Item</span>
                        </button>
                    </div>
                </div>

            </div>

            <!-- OTHER Header Group -->
            <div class="space-y-1 border-t border-slate-200/50 pt-4">
                <button type="button" 
                        wire:click="selectView('profile')"
                        @click="mobileMenuOpen = false"
                        class="w-full flex items-center rounded-xl text-base font-light transition-all duration-200 hover:translate-x-1.5 {{ $activeView === 'profile' ? 'bg-indigo-50 text-indigo-600 font-normal' : 'text-slate-600 hover:bg-slate-50' }}"
                        :class="sidebarCollapsed ? 'justify-center px-3 py-2.5' : 'px-3 py-2.5 gap-2.5'"
                        title="Profil Saya">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span x-show="!sidebarCollapsed" x-transition.opacity>Profil Saya</span>
                </button>
            </div>

        </nav>

        <!-- Logout Trigger -->
        <div class="pt-4 border-t border-slate-200/50 shrink-0">
            <button type="button" 
                    @click="openConfirm({
                        title: 'Keluar Aplikasi',
                        message: 'Apakah Anda yakin ingin keluar dari aplikasi?',
                        icon: 'logout',
                        confirmLabel: 'Ya, Keluar',
                        confirmClass: 'bg-red-600 hover:bg-red-700',
                        action: () => $wire.logout()
                    })"
                    class="w-full flex items-center rounded-xl text-base font-light text-red-500 hover:bg-red-50 transition-all duration-200 hover:translate-x-1.5"
                    :class="sidebarCollapsed ? 'justify-center px-3 py-2.5' : 'px-3 py-2.5 gap-3'"
                    title="Keluar Aplikasi">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span x-show="!sidebarCollapsed" x-transition.opacity>Keluar Aplikasi</span>
            </button>
        </div>
    </aside>

    <!-- MAIN DASHBOARD CONTENT -->
    <main class="flex-grow p-6 lg:p-8 flex flex-col gap-6 overflow-x-hidden min-w-0">
        
        <!-- TOP DESKTOP SUB-HEADER (no-print) -->
        <div class="no-print flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <!-- Expand Sidebar Button (Shown only when collapsed) -->
                <button type="button" 
                        x-show="sidebarCollapsed"
                        wire:click="toggleSidebar"
                        class="text-slate-500 hover:text-slate-800 bg-white border border-slate-200 p-2 rounded-xl transition shadow-sm"
                        style="display: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                
                <div>
                    <h2 class="text-xl font-black text-slate-800 tracking-tight">Point Of Purchase</h2>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest">Fashion YKR Portal</p>
                </div>
            </div>
            
            <!-- User Status Info -->
            <button type="button" 
                    wire:click="selectView('profile')"
                    class="flex items-center gap-3 bg-white border border-slate-200 px-4 py-2 rounded-xl shadow-sm cursor-pointer hover:bg-slate-50 transition active:scale-[0.98]">
                <div class="h-8 w-8 rounded-full bg-gradient-to-tr from-indigo-500 to-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-inner">
                    U
                </div>
                <div class="hidden sm:block text-left">
                    <p class="text-xs font-black text-slate-700 leading-none">{{ session('user_name', '123456') }}</p>
                    <span class="text-[9px] text-slate-400 font-bold uppercase">{{ session('user_email', '123456@ykr.com') }}</span>
                </div>
            </button>
        </div>

        <!-- Alert Notification UI -->
        <div x-show="notification.show"
             x-transition
             :class="{
                'bg-emerald-50 border-emerald-200 text-emerald-700': notification.type === 'success',
                'bg-blue-50 border-blue-200 text-blue-700': notification.type === 'info',
                'bg-amber-50 border-amber-200 text-amber-700': notification.type === 'warning'
             }"
             class="p-4 rounded-xl border text-sm font-semibold flex items-center gap-3 shadow-sm no-print bg-white"
             style="display: none;">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span x-text="notification.message"></span>
        </div>

        <!-- DYNAMIC INNER COMPONENT VIEW ROUTING -->
        <div class="flex-grow w-full">
            @if($activeView === 'a5-single-price')
                <livewire:a5.single-price />
            @elseif($activeView === 'a5-was-is-price')
                <livewire:a5.was-is-price />
            @elseif($activeView === 'a5-discount-percent')
                <livewire:a5.discount-percent />
            @elseif($activeView === 'a5-double-item')
                <livewire:a5.double-item />
                
            @elseif($activeView === 'a4-single-price')
                <livewire:a4.single-price />
            @elseif($activeView === 'a4-was-is-price')
                <livewire:a4.was-is-price />
            @elseif($activeView === 'a4-discount-percent')
                <livewire:a4.discount-percent />
            @elseif($activeView === 'a4-double-item')
                <livewire:a4.double-item />
                
            @elseif($activeView === 'a3-single-price')
                <livewire:a3.single-price />
            @elseif($activeView === 'a3-was-is-price')
                <livewire:a3.was-is-price />
            @elseif($activeView === 'a3-discount-percent')
                <livewire:a3.discount-percent />
            @elseif($activeView === 'a3-double-item')
                <livewire:a3.double-item />
                
            @elseif($activeView === 'profile')
                <livewire:profile />
            @endif
        </div>

    <!-- ============================================================ -->
    <!-- GLOBAL CONFIRMATION MODAL -->
    <!-- ============================================================ -->
    <div
        x-show="confirmModal.show"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm no-print"
        style="display: none;"
        @keydown.escape.window="confirmModal.show = false">

        <div
            x-show="confirmModal.show"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 flex flex-col gap-5">

            <!-- Icon + Title -->
            <div class="flex flex-col items-center gap-3 text-center">
                <!-- Delete Icon -->
                <template x-if="confirmModal.icon === 'delete'">
                    <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </div>
                </template>
                <!-- Logout Icon -->
                <template x-if="confirmModal.icon === 'logout'">
                    <div class="w-14 h-14 rounded-full bg-orange-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                </template>

                <div>
                    <h3 class="text-base font-extrabold text-slate-800" x-text="confirmModal.title"></h3>
                    <p class="text-xs text-slate-500 mt-1 font-medium" x-text="confirmModal.message"></p>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3">
                <button type="button"
                    @click="confirmModal.show = false"
                    class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 px-4 rounded-xl text-sm transition duration-150">
                    Batal
                </button>
                <button type="button"
                    @click="runConfirm()"
                    :class="confirmModal.confirmClass"
                    class="flex-1 text-white font-bold py-2.5 px-4 rounded-xl text-sm transition duration-150 shadow-sm"
                    x-text="confirmModal.confirmLabel">
                </button>
            </div>
        </div>
    </div>

</div>