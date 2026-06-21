<?php

use Livewire\Component;

new class extends Component
{
    public $loginUsername = '';
    public $loginPassword = '';
    public $loginError = '';

    public function login()
    {
        $user = \App\Models\User::where('name', $this->loginUsername)->first();
        if ($user && \Hash::check($this->loginPassword, $user->password)) {
            session([
                'authenticated' => true,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email
            ]);
            $this->loginError = '';
            return redirect('/');
        } else {
            $this->loginError = 'Username atau Password salah!';
        }
    }
};
?>

<div class="min-h-screen bg-slate-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 border border-slate-200 shadow-xl rounded-2xl">
        <div class="text-center">
            <div class="mx-auto h-12 w-12 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-2xl shadow-lg shadow-indigo-100">
                %
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-slate-900 tracking-tight">
                POP Promotion YKR
            </h2>
            <p class="mt-2 text-center text-sm text-slate-500 font-medium">
                Silakan login untuk mengakses portal pencetakan POP.
            </p>
        </div>
        
        <form class="mt-8 space-y-6" wire:submit.prevent="login">
            @if($loginError)
                <div class="p-3 bg-red-50 border border-red-200 text-red-600 text-xs font-semibold rounded-lg text-center">
                    {{ $loginError }}
                </div>
            @endif
            
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Username</label>
                    <input wire:model="loginUsername" type="text" required class="appearance-none rounded-xl relative block w-full px-4 py-3 border border-slate-200 placeholder-slate-400 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm animate-none" placeholder="Ketik 123456">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Password</label>
                    <input wire:model="loginPassword" type="password" required class="appearance-none rounded-xl relative block w-full px-4 py-3 border border-slate-200 placeholder-slate-400 text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm animate-none" placeholder="Ketik 123456">
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3.5 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 shadow-md hover:shadow-lg active:scale-[0.98]">
                    Masuk Ke Dashboard
                </button>
            </div>
        </form>
    </div>
</div>
