<div><b>Database</b></div>
<div>Name: <?= $dbName ?></div>
<div>Driver: <?= $dbDriver ?></div>
<div>Size: <?= $dbSize ?></div>
<div>Version: <?= $dbVersion ?></div>
<br/>
<div><b>Include Files</b></div>
@foreach(config('backup.backup.source.files.include') as $file)
    <div>- <?php echo substr($file, strlen(base_path())) ?></div>
@endforeach
<br/>
<div><b>Exclude Files</b></div>
@foreach(config('backup.backup.source.files.exclude') as $file)
    <div>- <?php echo substr($file, strlen(base_path())) ?></div>
@endforeach