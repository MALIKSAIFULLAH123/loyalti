@if(!empty($reports))
    @foreach($reports as $item)
        @if($item['severity']=='error')
            <div style="color:#f02848"><?php echo $item['message']?></div>
        @elseif($item['severity']== 'warn')
            <div style="color:#f4b400"><?php echo $item['message']?></div>
        @elseif($item['severity']== 'success')
            <div style="color:#31a24a"><?php echo $item['message']?></div>
        @elseif($item['severity']== 'debug')
            <div style="color:#050505"><?php echo $item['message']?></div>
        @endif
    @endforeach
@endif