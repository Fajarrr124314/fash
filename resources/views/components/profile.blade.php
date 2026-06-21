<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

new class extends Component
{
    public $userId;
    public $username = '';
    public $email = '';
    
    // Password change fields
    public $currentPassword = '';
    public $newPassword = '';
    public $newPasswordConfirmation = '';
    
    public $successMsg = '';
    public $errorMsg = '';

    public function mount()
    {
        $this->userId = session('user_id');
        $user = User::find($this->userId);
        if ($user) {
            $this->username = $user->name;
            $this->email = $user->email;
        } else {
            // Fallback if session is active but user is deleted
            $this->username = session('user_name', '123456');
            $this->email = session('user_email', '123456@ykr.com');
        }
    }

    public function updateProfile()
    {
        $this->successMsg = '';
        $this->errorMsg = '';

        $this->validate([
            'username' => 'required|string|min:3|unique:users,name,' . $this->userId,
            'email' => 'required|email|unique:users,email,' . $this->userId,
        ]);

        $user = User::find($this->userId);
        if ($user) {
            $user->update([
                'name' => $this->username,
                'email' => $this->email,
            ]);
            
            session([
                'user_name' => $this->username,
                'user_email' => $this->email
            ]);
            
            $this->successMsg = 'Profil berhasil diperbarui!';
        } else {
            $this->errorMsg = 'User tidak ditemukan di database!';
        }
    }

    public function changePassword()
    {
        $this->successMsg = '';
        $this->errorMsg = '';

        $this->validate([
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:6|same:newPasswordConfirmation',
        ], [
            'newPassword.same' => 'Password baru dan konfirmasi password tidak cocok.',
            'newPassword.min' => 'Password baru harus minimal 6 karakter.'
        ]);

        $user = User::find($this->userId);
        if ($user) {
            if (!Hash::check($this->currentPassword, $user->password)) {
                $this->errorMsg = 'Password saat ini salah!';
                return;
            }

            $user->update([
                'password' => Hash::make($this->newPassword),
            ]);

            $this->currentPassword = '';
            $this->newPassword = '';
            $this->newPasswordConfirmation = '';
            
            $this->successMsg = 'Password berhasil diganti!';
        } else {
            $this->errorMsg = 'User tidak ditemukan di database!';
        }
    }
};
?>

<div class="w-full max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="no-print">
        <h3 class="text-base font-extrabold text-slate-800">Pengaturan Profil</h3>
        <p class="text-xs text-slate-400 font-semibold uppercase mt-0.5">Edit Akun & Keamanan</p>
    </div>

    <!-- Alert Notifications -->
    @if($successMsg)
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold rounded-xl flex items-center gap-3 shadow-sm animate-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $successMsg }}</span>
        </div>
    @endif

    @if($errorMsg)
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm font-semibold rounded-xl flex items-center gap-3 shadow-sm animate-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>{{ $errorMsg }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Box 1: Edit Profile (Solid White) -->
        <div class="bg-white border border-slate-200 shadow-md rounded-2xl p-6 space-y-5">
            <div class="border-b border-slate-100 pb-3 flex items-center gap-2">
                <div class="p-2 rounded-lg bg-indigo-50 text-indigo-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-slate-800">Detail Profil</h4>
            </div>

            <form wire:submit.prevent="updateProfile" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Username / Nama</label>
                    <input type="text" wire:model="username" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none transition font-semibold">
                    @error('username')
                        <span class="text-red-500 text-xs mt-1 block font-semibold">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Alamat Email</label>
                    <input type="email" wire:model="email" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none transition font-semibold">
                    @error('email')
                        <span class="text-red-500 text-xs mt-1 block font-semibold">{{ $message }}</span>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition duration-150 shadow-md active:scale-[0.98]">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Box 2: Change Password (Solid White) -->
        <div class="bg-white border border-slate-200 shadow-md rounded-2xl p-6 space-y-5">
            <div class="border-b border-slate-100 pb-3 flex items-center gap-2">
                <div class="p-2 rounded-lg bg-red-50 text-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h4 class="text-sm font-bold text-slate-800">Keamanan / Ganti Password</h4>
            </div>

            <form wire:submit.prevent="changePassword" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Password Saat Ini</label>
                    <input type="password" wire:model="currentPassword" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none transition font-semibold">
                    @error('currentPassword')
                        <span class="text-red-500 text-xs mt-1 block font-semibold">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Password Baru</label>
                    <input type="password" wire:model="newPassword" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none transition font-semibold">
                    @error('newPassword')
                        <span class="text-red-500 text-xs mt-1 block font-semibold">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Konfirmasi Password Baru</label>
                    <input type="password" wire:model="newPasswordConfirmation" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:border-indigo-500 focus:outline-none transition font-semibold">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-2.5 px-4 rounded-xl text-xs transition duration-150 shadow-md active:scale-[0.98]">
                        Ganti Password
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
