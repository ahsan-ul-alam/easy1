<div data-toasts class="pointer-events-none fixed inset-x-0 top-20 z-50 flex flex-col items-center gap-2 px-4 sm:top-auto sm:right-6 sm:bottom-24 sm:left-auto sm:items-end" aria-live="polite">
    @foreach (['success' => session('success'), 'error' => session('error')] as $type => $message)
        @if ($message)
            <div data-toast data-toast-type="{{ $type }}" class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-2xl px-4 py-3 text-sm shadow-lift {{ $type === 'error' ? 'bg-red-600 text-white' : 'bg-stone-900 text-white' }}">
                <x-icon :name="$type === 'error' ? 'x' : 'check'" class="mt-0.5 size-4" />
                <p class="flex-1">{{ $message }}</p>
            </div>
        @endif
    @endforeach
</div>
