<section class="space-y-6">
    <header class="border-b border-[#D4DEE7] pb-4 mb-6">
        <h2 class="text-base font-bold text-[#243746]">
            Hapus Akun Kedinasan
        </h2>

        <p class="mt-1 text-sm text-[#5A6E7D]">
            Setelah akun dihapus, seluruh data profil dan akses ke dalam ruang kerja SIGAP-TRANS akan dihapus secara permanen.
        </p>
    </header>

    <div>
        <x-danger-button
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        >Hapus Akun</x-danger-button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-base font-bold text-[#243746]">
                Konfirmasi Penghapusan Akun
            </h2>

            <p class="mt-1 text-sm text-[#5A6E7D]">
                Apakah Anda yakin ingin menghapus akun ini secara permanen? Masukkan kata sandi akun Anda untuk mengonfirmasi tindakan ini.
            </p>

            <div class="mt-5">
                <x-input-label for="password" value="Kata Sandi Konfirmasi" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="block w-full sm:w-3/4"
                    placeholder="Masukkan kata sandi Anda"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-1.5" />
            </div>

            <div class="mt-6 flex justify-end gap-2.5">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Batal
                </x-secondary-button>

                <x-danger-button>
                    Ya, Hapus Akun
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
