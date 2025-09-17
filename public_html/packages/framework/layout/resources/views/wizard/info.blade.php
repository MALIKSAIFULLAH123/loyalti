
<div>{{__p('core::phrase.platform_version')}}: <b>v<?php echo \MetaFox\Platform\MetaFox::getVersion() ?></b></div>
<?php if(!empty($appChannel)): ?>
    <div>{{__p('core::web.channel')}}: <b><?php echo ucfirst($appChannel) ?></b></div>
<?php endif;?>
<div>{{__p('layout::phrase.build_service')}}: <b><?php echo $buildService ?></b></div>
<br/>

<code class="srOnly">
    <div><b>{{__p('core::phrase.environment_variables')}}</b>:</div>
    <?php echo nl2br($env)?>
</code>

