<?php echo '<?xml version="1.0" encoding="UTF-8"?>' ?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/10.3/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true" backupGlobals="false"
         failOnEmptyTestSuite="false"
         failOnIncomplete="false"
         processIsolation="false"
         defaultTestSuite="tests"
         cacheResult="false"
         testdox="true"
         cacheDirectory=".phpunit.cache" backupStaticProperties="false">
    <testsuites>
    @foreach($suiteMap as $suiteName => $paths)
        <testsuite name="{{$suiteName}}">
        @foreach($paths as $path)
            <directory suffix="Test.php">{!! $path !!}</directory>
        @endforeach
        </testsuite>
    @endforeach
    </testsuites>
    <source>
        <include>
        @foreach($coveragePaths as $path)
            <directory suffix=".php">{!! $path !!}</directory>
        @endforeach
        </include>
        <exclude>
        @foreach($excludeSourcePaths as $path)
            <directory suffix=".php">{!! $path !!}</directory>
        @endforeach
            <directory>vendor</directory>
            <directory>tests</directory>
            <directory>config</directory>
            <directory>database</directory>
            <directory>zdocker</directory>
            <directory>public</directory>
            <directory>bootstrap</directory>
            <directory>app/Console</directory>
        </exclude>
    </source>
    <coverage includeUncoveredFiles="true">
        <report>
            <html outputDirectory="build/coverage" lowUpperBound="35" highLowerBound="70"/>
        </report>
    </coverage>
    <php>
        <ini name="memory_limit" value="-1"/>
        <env name="APP_KEY" value="AckfSECXIvnK5r28GVIWUAxmbBSjTsmF"/>
        <server name="APP_ENV" value="testing"/>
        <server name="BCRYPT_ROUNDS" value="4"/>
        <server name="MFOX_CACHE_DRIVER" value="array"/>
        <server name="MFOX_MAIL_PROVIDER" value="array"/>
        <server name="MFOX_MAIL_FROM" value="noreply@metafox.app"/>
        <server name="MFOX_SESSION_DRIVER" value="array"/>
        <server name="QUEUE_CONNECTION" value="sync"/>
        <server name="TELESCOPE_ENABLED" value="false"/>
    </php>
    <extensions>
        <bootstrap class="Qameta\Allure\PHPUnit\AllureExtension">
            <parameter name="config" value=".config/allure.config.php"/>
        </bootstrap>
    </extensions>
</phpunit>
