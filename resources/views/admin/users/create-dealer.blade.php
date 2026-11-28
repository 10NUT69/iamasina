@extends('admin.layout')

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-8 sm:px-6">
        <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-slate-700 hover:underline">
            Înapoi la utilizatori
        </a>

        <div class="mt-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-bold text-slate-800">Creează dealer</h1>
            <p class="mt-1 text-sm text-slate-600">Contul va folosi parola implicită configurată pentru dealeri.</p>
            <x-input-error :messages="$errors->get('dealer_setup')" class="mt-3" />

            <form method="POST" action="{{ route('admin.users.dealers.store') }}" class="mt-6 space-y-4">
                @csrf

                <div>
                    <x-input-label for="name" value="Nume persoană de contact" />
                    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name')" maxlength="100" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="email" value="Email de autentificare" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" maxlength="120" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="company_name" value="Nume parc auto" />
                    <x-text-input id="company_name" name="company_name" class="mt-1 block w-full" :value="old('company_name')" maxlength="150" required />
                    <x-input-error :messages="$errors->get('company_name')" class="mt-1" />
                </div>

                <div>
                    <x-input-label for="phone" value="Telefon parc auto" />
                    <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone')" maxlength="30" required />
                    <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                </div>

                <x-primary-button>Creează contul</x-primary-button>
            </form>
        </div>
    </div>
@endsection
