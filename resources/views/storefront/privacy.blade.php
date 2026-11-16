<x-layouts.store :title="__('app.privacy.title')" :description="__('app.privacy.meta', ['name' => $site->business_name])">
    <div class="container-page max-w-3xl pt-10 sm:pt-16">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ __('app.privacy.title') }}</h1>

        <div class="card mt-8 p-6 leading-relaxed text-stone-700 sm:p-8">
            @if ($site->privacy_policy)
                <div class="whitespace-pre-line">{{ $site->privacy_policy }}</div>
            @else
                <div class="space-y-4">
                    <p>{{ __('app.privacy.intro') }}</p>
                    @foreach (__('app.privacy.points') as $point)
                        <p><strong class="text-stone-900">{{ $point['title'] }}</strong> {{ $point['text'] }}</p>
                    @endforeach
                    <p>{!! __('app.privacy.questions', ['link' => '<a href="'.e(route('pages.contact')).'" class="font-medium text-stone-900 underline underline-offset-4">'.e(__('app.privacy.contact_link')).'</a>']) !!}</p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.store>
