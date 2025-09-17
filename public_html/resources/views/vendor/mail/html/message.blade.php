@php
    use MetaFox\Platform\Facades\Settings;
@endphp

@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            @if(Settings::get('mail.enable_site_logo', true))
                <img src="{{app('asset')->findByName('mail_logo')?->url}}"
                     alt=""
                     style="max-height: 50px">
                <br>
            @endif
            @if(Settings::get('mail.enable_site_name', true))
                {{ config('app.name') }}
            @endif
        @endcomponent
    @endslot

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')
        @endcomponent
    @endslot
@endcomponent
