<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex, nofollow">
        <title>{{ __('admin.login.title') }} — {{ $site->business_name }}</title>
        @fonts
        @vite(['resources/css/app.css'])
    </head>
    <body class="grid min-h-full place-items-center bg-stone-100 px-4 py-12">
        <div class="w-full max-w-sm">
            <div class="mb-6 flex justify-end">
                <x-locale-switcher />
            </div>

            <div class="mb-8 text-center">
                <span class="mx-auto grid size-12 place-items-center rounded-2xl bg-stone-900 text-lg font-bold text-white">{{ Str::upper(Str::substr($site->business_name, 0, 1)) }}</span>
                <h1 class="mt-4 text-2xl font-bold tracking-tight">{{ __('admin.login.welcome') }}</h1>
                <p class="mt-1 text-sm text-stone-600">{{ __('admin.login.subtitle', ['name' => $site->business_name]) }}</p>
            </div>

            <form method="POST" action="{{ route('admin.login') }}" class="card space-y-5 p-6">
                @csrf

                <div>
                    <label for="email" class="label">{{ __('admin.login.email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" @class(['input', 'input-error' => $errors->has('email')])>
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="label">{{ __('admin.login.password') }}</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" @class(['input', 'input-error' => $errors->has('password')])>
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-sm text-stone-600">
                    <input type="checkbox" name="remember" value="1" class="size-4 rounded border-stone-300 accent-stone-900">
                    {{ __('admin.login.remember') }}
                </label>

                <button type="submit" class="btn btn-primary w-full">{{ __('admin.login.submit') }}</button>
            </form>

            <p class="mt-6 text-center text-sm"><a href="{{ route('home') }}" class="text-stone-500 hover:text-stone-900">&larr; {{ __('admin.login.back') }}</a></p>
        </div>
    </body>
</html>
