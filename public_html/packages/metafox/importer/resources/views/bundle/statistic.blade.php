<h4 paragraph="true">Statistic</h4>

<div paragraph="true">Running: <?php echo $running ? __p('core::phrase.yes') : __p('core::phrase.no') ?></div>
<br />
<h5 paragraph="true">Bundles: </h5>
@foreach($bundleStats as $item)
    <div>Bundle <?php echo $item['status']; ?>:
        <a target="_blank"  rel="noopener" href="/admincp/importer/bundle/browse?status=<?php echo $item['status']?>"><?php echo number_format($item['total']) ?></a>
    </div>
@endforeach
<div>Errors: <a href="/admincp/importer/history/browse?level=ERROR" target="_blank" rel="noopener">
        <?php echo number_format($totalErrors) ?>
    </a>
</div>
