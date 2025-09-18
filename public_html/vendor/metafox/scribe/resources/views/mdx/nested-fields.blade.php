@php
    $level ??= 0;
    $levelNestingClass = match($level) {
        0 => "sl-ml-px",
        default => "sl-ml-7"
    };
    $expandable ??= !isset($fields["[]"]);
@endphp

@foreach($fields as $name => $field)

    @todo: implement collapse expand - {{$levelNestingClass}} - {{ $expandable }}
    @component('scribe::mdx.field-details', [
      'name' => $name,
      'type' => $field['type'] ?? 'string',
      'required' => $field['required'] ?? false,
      'description' => $field['description'] ?? '',
      'example' => $field['example'] ?? '',
      'endpointId' => $endpointId,
      'hasChildren' => !empty($field['__fields']),
      'component' => 'body',
    ])
    @endcomponent

    @if(!empty($field['__fields']))
        @component('scribe::mdx.nested-fields', [
          'fields' => $field['__fields'],
          'endpointId' => $endpointId,
          'level' => $level + 1,
          'expandable'=> $expandable,
        ])
        @endcomponent
    @endif
@endforeach
