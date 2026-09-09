<section class="space-y-6">
    <header>
        <div class="flex items-center space-x-3">
            <div class="w-9 h-9 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </div>
            <div>
                <h2 class="text-base font-extrabold text-stone-900 uppercase tracking-tight">
                    Hapus Akun Permanen
                </h2>
                <p class="text-xs text-stone-500">
                    Penghapusan akun bersifat final dan tidak dapat dipulihkan kembali.
                </p>
            </div>
        </div>
    </header>

    <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200 text-xs text-stone-600 space-y-2">
        @if(auth()->user()->isStylist())
            <p class="font-bold text-stone-800">Kebijakan Akun Barber / Stylist:</p>
            <p>Akun login Anda akan dihapus permanen dan profil publik serta jadwal kerja akan dinonaktifkan. Pastikan tidak ada reservasi aktif yang sedang berjalan sebelum melanjutkan.</p>
        @elseif(auth()->user()->isSuperAdmin() || auth()->user()->isOutletAdmin())
            <p class="font-bold text-stone-800">Kebijakan Akun Administrator:</p>
            <p>Akun admin Anda akan dicabut hak aksesnya. Jika Anda adalah Super Admin satu-satunya di sistem, sistem akan menolak penghapusan demi mencegah kegagalan akses sistem.</p>
        @else
            <p class="font-bold text-stone-800">Kebijakan Perlindungan Privasi Pelanggan (UU PDP):</p>
            <p>Seluruh identitas personal (Nama, No WhatsApp, Email, Alamat, Tanggal Lahir) akan dianonimkan secara permanen. Reservasi mendatang akan dibatalkan otomatis. Catatan transaksi masa lalu tetap diarsipkan secara anonim untuk integritas audit keuangan studio.</p>
        @endif
    </div>

    <button
        type="button"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition shadow-sm"
    >
        Hapus Akun Saya
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 space-y-4" x-data="{ confirmPhrase: '' }">
            @csrf
            @method('delete')

            <div class="flex items-center space-x-3 text-rose-600">
                <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h2 class="text-base font-extrabold text-stone-900">
                        Konfirmasi Penghapusan Akun
                    </h2>
                    <p class="text-xs text-stone-500">
                        Apakah Anda yakin ingin menghapus akun Anda secara permanen?
                    </p>
                </div>
            </div>

            @if($errors->userDeletion->has('stylist') || $errors->userDeletion->has('user'))
                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 font-semibold">
                    {{ $errors->userDeletion->first('stylist') ?: $errors->userDeletion->first('user') }}
                </div>
            @endif

            <div class="p-3.5 bg-rose-50/70 border border-rose-100 rounded-xl text-xs text-rose-800 space-y-1">
                <p class="font-bold">Peringatan Keamanan:</p>
                <p class="text-[11px] text-rose-700 leading-relaxed">
                    Setelah akun dihapus, sesi Anda akan diakhiri dan seluruh data pribadi akan dibersihkan sesuai kebijakan privasi MORE Hair Studio.
                </p>
            </div>

            <div class="space-y-3 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">
                        Ketik kata <span class="font-mono font-bold text-rose-600 bg-rose-100 px-1.5 py-0.5 rounded">HAPUS AKUN</span> untuk mengonfirmasi:
                    </label>
                    <input
                        type="text"
                        name="confirmation"
                        x-model="confirmPhrase"
                        placeholder="HAPUS AKUN"
                        required
                        class="block w-full px-3 py-2 text-xs rounded-xl border border-stone-300 focus:border-rose-500 focus:ring-rose-500 shadow-sm font-mono"
                    />
                    <x-input-error :messages="$errors->userDeletion->get('confirmation')" class="mt-1" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">
                        Masukkan Kata Sandi Saat Ini:
                    </label>
                    <input
                        type="password"
                        name="password"
                        placeholder="Kata sandi aktif Anda"
                        required
                        class="block w-full px-3 py-2 text-xs rounded-xl border border-stone-300 focus:border-rose-500 focus:ring-rose-500 shadow-sm"
                    />
                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-stone-700 mb-1">
                        Alasan Penghapusan (Opsional):
                    </label>
                    <input
                        type="text"
                        name="reason"
                        placeholder="Contoh: Tidak lagi menggunakan layanan"
                        class="block w-full px-3 py-2 text-xs rounded-xl border border-stone-300 focus:border-stone-400 shadow-sm"
                    />
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-4 border-t border-stone-100">
                <button
                    type="button"
                    x-on:click="$dispatch('close')"
                    class="px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold rounded-xl text-xs transition"
                >
                    Batalkan
                </button>

                <button
                    type="submit"
                    :disabled="confirmPhrase !== 'HAPUS AKUN'"
                    :class="confirmPhrase === 'HAPUS AKUN' ? 'bg-rose-600 hover:bg-rose-700 cursor-pointer text-white' : 'bg-stone-200 text-stone-400 cursor-not-allowed'"
                    class="px-4 py-2 font-bold rounded-xl text-xs transition shadow-sm"
                >
                    Ya, Hapus Akun Permanen
                </button>
            </div>
        </form>
    </x-modal>
</section>
