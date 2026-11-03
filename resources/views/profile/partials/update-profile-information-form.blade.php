<section>
    <header class="border-b border-[#D4DEE7] pb-4 mb-6">
        <h2 class="text-base font-bold text-[#243746]">
            Informasi Profil
        </h2>

        <p class="mt-1 text-sm text-[#5A6E7D]">
            Perbarui informasi identitas akun dan alamat surel kedinasan Anda.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Nama Lengkap" />
            <x-text-input id="name" name="name" type="text" class="block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" placeholder="Nama lengkap beserta gelar" />
            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="Alamat Email Dinas" />
            <x-text-input id="email" name="email" type="email" class="block w-full" :value="old('email', $user->email)" required autocomplete="username" placeholder="contoh@kalselprov.go.id" />
            <x-input-error class="mt-1.5" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2.5 p-3 rounded bg-amber-50 border border-amber-200">
                    <p class="text-xs text-amber-800">
                        Alamat email Anda belum terverifikasi.

                        <button form="send-verification" class="underline font-semibold text-amber-900 hover:text-amber-700 ml-1">
                            Kirim ulang email verifikasi.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-xs text-[#107C41]">
                            Tautan verifikasi baru telah dikirimkan ke alamat email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button>Simpan Perubahan</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-sm font-medium text-[#107C41]"
                >Perubahan berhasil disimpan.</p>
            @endif
        </div>
    </form>
</section>
