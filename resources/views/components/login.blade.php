<?php

use Livewire\Component;

new class extends Component
{
    public $loginUsername = '';
    public $loginPassword = '';
    public $loginError = '';
    public $showPassword = false;

    public function login()
    {
        $user = \App\Models\User::where('name', $this->loginUsername)->first();
        if ($user && \Hash::check($this->loginPassword, $user->password)) {
            session([
                'authenticated' => true,
                'user_id'       => $user->id,
                'user_name'     => $user->name,
                'user_email'    => $user->email
            ]);
            $this->loginError = '';
            return redirect('/');
        } else {
            $this->loginError = 'Username atau Password salah!';
        }
    }
};
?>

<style>
    /* Mobile-first styles (max-width: 1023px) */
    @media (max-width: 1023px) {
        .login-right-panel {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #311042 100%) !important;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.94) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.25) !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4) !important;
            border-radius: 1.5rem !important;
            padding: 2.5rem 1.75rem !important;
        }
    }
    
    /* Desktop styles (min-width: 1024px) */
    @media (min-width: 1024px) {
        .login-right-panel {
            background: #f8fafc !important;
        }
        .login-card {
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
        }
    }
</style>

<div class="min-h-screen flex" x-data="{ showPass: false }">

    {{-- ===================== LEFT PANEL (Illustration) ===================== --}}
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden"
         style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4c1d95 100%);">

        {{-- Background decorative circles --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full opacity-10"
                 style="background: radial-gradient(circle, #6366f1, transparent);"></div>
            <div class="absolute -bottom-32 -right-32 w-[500px] h-[500px] rounded-full opacity-10"
                 style="background: radial-gradient(circle, #a855f7, transparent);"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full opacity-5"
                 style="background: radial-gradient(circle, #ffffff, transparent);"></div>
        </div>

        {{-- Panel Image --}}
        <div class="absolute inset-0 bg-cover bg-center opacity-80"
             style="background-image: url('{{ asset('images/login_panel.png') }}');"></div>

        {{-- Overlay gradient bottom --}}
        <div class="absolute inset-0"
             style="background: linear-gradient(to top, rgba(30,27,75,0.95) 0%, rgba(30,27,75,0.3) 50%, transparent 100%);"></div>

        {{-- Content overlay --}}
        <div class="relative z-10 flex flex-col justify-end h-full p-12">
            {{-- Floating badge chips --}}
            <div class="flex flex-wrap gap-3 mb-8">
                <span class="inline-flex items-center gap-1.5 bg-white/10 backdrop-blur-sm border border-white/20 text-white text-xs font-bold px-3 py-1.5 rounded-full">
                    <svg class="w-3.5 h-3.5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    POP Builder
                </span>
                <span class="inline-flex items-center gap-1.5 bg-red-500/20 backdrop-blur-sm border border-red-400/30 text-red-300 text-xs font-bold px-3 py-1.5 rounded-full">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Diskon & Promo
                </span>
                <span class="inline-flex items-center gap-1.5 bg-indigo-500/20 backdrop-blur-sm border border-indigo-400/30 text-indigo-300 text-xs font-bold px-3 py-1.5 rounded-full">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak A3 / A4 / A5
                </span>
            </div>

            {{-- Headline --}}
            <div>
                <h1 class="text-4xl font-extrabold text-white leading-tight mb-3"
                    style="font-family: 'Outfit', sans-serif; letter-spacing: -0.5px;">
                    Point Of Purchase<br>
                    <span style="background: linear-gradient(90deg, #f87171, #fb923c); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                        Promotion System
                    </span>
                </h1>
                <p class="text-indigo-200 text-sm font-medium leading-relaxed max-w-sm">
                    Buat, kelola, dan cetak materi promosi harga untuk toko fashion Anda — cepat, presisi, dan profesional.
                </p>
            </div>

            {{-- Feature list --}}
            <div class="mt-6 flex flex-col gap-2">
                @foreach([
                    ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => 'Multi-format: A3, A4, A5 Landscape & Portrait'],
                    ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => 'Template Diskon, Harga Coret, Was Is Price'],
                    ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => 'Print langsung dari browser, tanpa software tambahan'],
                ] as $feat)
                    <div class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feat['icon'] }}"/>
                        </svg>
                        <span class="text-indigo-100 text-xs font-medium">{{ $feat['text'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===================== RIGHT PANEL (Form Login) ===================== --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12 relative overflow-hidden login-right-panel"
         style="background: #f8fafc;">

        {{-- Background decorative elements for mobile view --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none lg:hidden">
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full opacity-20"
                 style="background: radial-gradient(circle, #6366f1, transparent); filter: blur(40px);"></div>
            <div class="absolute -bottom-32 -right-32 w-80 h-80 rounded-full opacity-20"
                 style="background: radial-gradient(circle, #a855f7, transparent); filter: blur(40px);"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] h-[400px] rounded-full opacity-10"
                 style="background: radial-gradient(circle, #ec4899, transparent); filter: blur(60px);"></div>
        </div>

        <div class="w-full max-w-md login-card z-10">

            {{-- Mobile brand (only on small screens) --}}
            <div class="flex items-center gap-3 mb-10 lg:hidden">
                <div class="h-10 w-10 rounded-xl flex items-center justify-center font-extrabold text-lg text-white shadow-lg"
                     style="background: linear-gradient(135deg, #6366f1, #4f46e5);">%</div>
                <div>
                    <p class="text-base font-extrabold text-slate-800 leading-none">POP YKR</p>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-widest">Promotion System</p>
                </div>
            </div>

            {{-- Logo desktop --}}
            <div class="hidden lg:flex items-center gap-3 mb-10">
                <div class="h-11 w-11 rounded-xl flex items-center justify-center font-extrabold text-xl text-white shadow-lg shadow-indigo-200"
                     style="background: linear-gradient(135deg, #6366f1, #4f46e5);">%</div>
                <div>
                    <p class="text-base font-extrabold text-slate-800 leading-none">POP YKR</p>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-widest">Promotion System</p>
                </div>
            </div>

            {{-- Welcome text --}}
            <div class="mb-8">
                <h2 class="text-2xl font-extrabold text-slate-900 mb-1.5" style="letter-spacing: -0.3px;">
                    Selamat Datang 👋
                </h2>
                <p class="text-sm text-slate-500 font-medium">
                    Masuk untuk mengakses dashboard pencetakan POP.
                </p>
            </div>

            {{-- Error Alert --}}
            @if($loginError)
                <div class="mb-5 flex items-center gap-3 p-3.5 bg-red-50 border border-red-200 rounded-xl">
                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-xs font-semibold text-red-600">{{ $loginError }}</span>
                </div>
            @endif

            {{-- Form --}}
            <form wire:submit.prevent="login" class="space-y-5">

                {{-- Username --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input wire:model="loginUsername"
                               type="text"
                               required
                               autocomplete="username"
                               placeholder="Masukkan username"
                               class="w-full pl-10 pr-4 py-3 border border-slate-200 rounded-xl bg-white text-sm text-slate-900 placeholder-slate-400 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input wire:model="loginPassword"
                               :type="showPass ? 'text' : 'password'"
                               required
                               autocomplete="current-password"
                               placeholder="Masukkan password"
                               class="w-full pl-10 pr-12 py-3 border border-slate-200 rounded-xl bg-white text-sm text-slate-900 placeholder-slate-400 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition">
                        {{-- Toggle show/hide password --}}
                        <button type="button"
                                @click="showPass = !showPass"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                            <svg x-show="!showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPass" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="pt-1">
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 py-3.5 px-6 text-sm font-bold text-white rounded-xl transition duration-150 shadow-lg active:scale-[0.98]"
                            style="background: linear-gradient(135deg, #6366f1, #4f46e5); box-shadow: 0 8px 20px rgba(99,102,241,0.35);"
                            onmouseover="this.style.background='linear-gradient(135deg, #4f46e5, #4338ca)'"
                            onmouseout="this.style.background='linear-gradient(135deg, #6366f1, #4f46e5)'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Masuk ke Dashboard
                    </button>
                </div>
            </form>

            {{-- Footer --}}
            <p class="mt-10 text-center text-[11px] text-slate-400 font-medium">
                &copy; {{ date('Y') }} POP YKR — Point Of Purchase Promotion System
            </p>
        </div>
    </div>

</div>
