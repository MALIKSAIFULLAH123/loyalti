import { ApiGroup, Tab, Tabs, Callout, FileTree } from "@/components";

<ApiGroup value={{'{'}} {!! json_encode($group) !!} {{'}'}}>
# {!! $group['name'] !!}

{!! $group['description'] !!}

@foreach($group['subgroups'] as $subgroupName => $subgroup)
    @if($subgroupName !== "")
## {{ $subgroupName }}
        @php($subgroupDescription = collect($subgroup)->first(fn ($e) => $e->metadata->subgroupDescription)?->metadata?->subgroupDescription)
        @if($subgroupDescription)
            {!! $subgroupDescription !!}
        @endif
    @endif
    @foreach($subgroup as $endpoint)
        @include("scribe::mdx.endpoint")
    @endforeach
@endforeach
</ApiGroup>