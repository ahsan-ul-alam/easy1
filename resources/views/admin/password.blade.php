<x-layouts.admin :title="__('admin.password.title')">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ __('admin.password.title') }}</h1>
        <p class="mt-1 text-sm text-stone-600">{{ __('admin.password.intro') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.password.update') }}" class="card mt-6 max-w-lg space-y-5 p-5 sm:p-6">
        @csrf
        @method('PUT')

        <div>
            <label for="current_password" class="label">{{ __('admin.password.current') }}</label>
            <input id="current_password" type="password" name="current_password" required autocomplete="current-password" @class(['input', 'input-error' => $errors->has('current_password')])>
            @error('current_password') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="label">{{ __('admin.password.new') }}</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" @class(['input', 'input-error' => $errors->has('password')])>
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="label">{{ __('admin.password.confirm') }}</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="input">
        </div>

        <button type="submit" class="btn btn-primary">{{ __('admin.password.submit') }}</button>
    </form>
</x-layouts.admin>
