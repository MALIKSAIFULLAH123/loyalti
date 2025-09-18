@php
    $hasChildren ??= false;
    $isArrayBody = $name == "[]";
    $expandable = $hasChildren && !$isArrayBody;
@endphp

@if($expandable)
    sl-cursor-pointer
@endif
@if($expandable)
    @todo add react component to toggle.
@endif
@unless($isArrayBody)
    **{{ $name }}**

    @if($type)
        {{ $type }}
    @endif
    @if($required)
        required
    @else
        optional
    @endif
@endunless
@if($description)
    {!! $description !!}
@endif
@if($isArrayBody)
    array of:
    @if($required)
        required
    @endif
@endif
@if(!$hasChildren && !is_null($example) && $example != '')
    Example: {{ is_array($example) ? json_encode($example) : $example }}
@endif
