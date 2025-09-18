@php
    use Knuckles\Scribe\Tools\Utils as u;
    /** @var  Knuckles\Camel\Output\OutputEndpointData $endpoint */
@endphp


## {!! preg_replace("/{(\w+)}/mi", ":$1", $endpoint->uri) !!}

@foreach($endpoint->httpMethods as $method)
**{{ $method }} /{!! preg_replace("/{(\w+)}/mi", ":$1", $endpoint->uri) !!}**
@endforeach

@if($endpoint->metadata->authenticated)
    requires authentication
@endif

{!! Parsedown::instance()->text($endpoint->metadata->description ?: '') !!}
@if(count($endpoint->headers))
**{{ u::trans("scribe::endpoint.headers") }}**
    @foreach($endpoint->headers as $header => $value)
        @component('scribe::mdx.field-details', [
          'name' => $header,
          'type' => null,
          'required' => false,
          'description' => null,
          'example' => $value,
          'endpointId' => $endpoint->endpointId(),
          'component' => 'header',
          'isInput' => true,
        ])
        @endcomponent
    @endforeach
@endif

@if(count($endpoint->urlParameters))
**{{ u::trans("scribe::endpoint.url_parameters") }} **

    @foreach($endpoint->urlParameters as $attribute => $parameter)
        @component('scribe::mdx.field-details', [
          'name' => $parameter->name,
          'type' => $parameter->type ?? 'string',
          'required' => $parameter->required,
          'description' => $parameter->description,
          'example' => $parameter->example ?? '',
          'endpointId' => $endpoint->endpointId(),
          'component' => 'url',
          'isInput' => true,
        ])
        @endcomponent
    @endforeach
@endif


@if(count($endpoint->queryParameters))
{{ u::trans("scribe::endpoint.query_parameters") }}

    @foreach($endpoint->queryParameters as $attribute => $parameter)
        @component('scribe::mdx.field-details', [
          'name' => $parameter->name,
          'type' => $parameter->type,
          'required' => $parameter->required,
          'description' => $parameter->description,
          'example' => $parameter->example ?? '',
          'endpointId' => $endpoint->endpointId(),
          'component' => 'query',
          'isInput' => true,
        ])
        @endcomponent
    @endforeach
@endif

@if(count($endpoint->nestedBodyParameters))
{{ u::trans("scribe::endpoint.body_parameters") }}

    @component('scribe::mdx.nested-fields', [
                                              'fields' => $endpoint->nestedBodyParameters,
                                              'endpointId' => $endpoint->endpointId(),
                                            ])
    @endcomponent
@endif

@if(count($endpoint->responseFields))
    {{ u::trans("scribe::endpoint.response_fields") }}

    @component('scribe::mdx.nested-fields', [
      'fields' => $endpoint->nestedResponseFields,
      'endpointId' => $endpoint->endpointId(),
      'isInput' => false,
    ])
    @endcomponent
@endif


@if($metadata['try_it_out']['enabled'] ?? false)
    @include("scribe::mdx.try_it_out")
@endif

@if($metadata['example_languages'])
    {{ u::trans("scribe::endpoint.example_request") }}:
<Tabs items={['bash','php','javascript']}>

    @foreach($metadata['example_languages'] as $index => $language)
<Tab>
@include("scribe::mdx.example-requests.$language")
</Tab>
    @endforeach
</Tabs>
@endif

@if($endpoint->isGet() || $endpoint->hasResponses())

{{ u::trans("scribe::endpoint.example_response") }} :

    @foreach($endpoint->responses as $index => $response)
        {{ $response->fullDescription() }}
    @endforeach
    @foreach($endpoint->responses as $index => $response)
        {{ $endpoint->endpointId() }}-{{ $index }}
        @if(count($response->headers))

            Headers
            ```http
            @foreach($response->headers as $header => $value)
                {{ $header }}
                : {{ is_array($value) ? implode('; ', $value) : $value }}
            @endforeach
        @endif
        @if(is_string($response->content) && Str::startsWith($response->content, "<<binary>>"))
            [{{ u::trans("scribe::endpoint.responses.binary") }}] - {{ (str_replace("<<binary>>", "", $response->content)) }}
        @elseif($response->status == 204)
            [{{ u::trans("scribe::endpoint.responses.empty") }}]
        @else
            @php($parsed = json_decode($response->content))
            {{-- If response is a JSON string, prettify it. Otherwise, just print it --}}
            {!! $parsed != null ? json_encode($parsed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $response->content !!}
        @endif
    @endforeach
@endif

