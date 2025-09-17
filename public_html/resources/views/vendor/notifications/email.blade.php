<x-mail::message>
{{-- Greeting --}}
@if (! empty($greeting))
# {{ $greeting }}
@else
@if ($level === 'error')
# @lang('Whoops!')
@else
# {{ __p('core::phrase.greetings_comma', $variables ?? [], $locale) }}
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ $line }}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
$color = match ($level) {
    'success', 'error' => $level,
    default => 'primary',
};

if (str_ends_with($actionText, '.')) {
    $actionText = rtrim($actionText, '.');
}

$actionText = \Illuminate\Support\Str::headline($actionText);
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ $line }}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ Illuminate\Mail\Markdown::parse($salutation) }}
@else
{!! __p("mail::phrase.default_signature", ['siteTitle' => config('app.name')], $locale) !!}
@endif

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
{!! __p("core::mail.subcopy_action_text", ['actionText' => $actionText], $locale) !!} <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>
