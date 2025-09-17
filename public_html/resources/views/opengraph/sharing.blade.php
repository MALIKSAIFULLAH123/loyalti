<!doctype html>
<html lang="<?php echo app()->getLocale() ?>">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <meta name="msapplication-config" content="/browserconfig.xml"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title><?php echo $data['title'] ?? 'Social Network' ?></title>
<?php echo $header_html ?? null; ?>

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png"/>
    <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#2682d5"/>
    <link rel="icon" type="image/x-icon" href="/favicon.ico"/>
</head>
<body>
<div id="root">
    <h1><?php echo $data['title'] ?? 'Social Network' ?></h1>
    <p>keywords: <?php echo $data['keywords'] ?? null; ?></p>
    <p>description: <?php echo $data['description'] ?? null; ?></p>
    @if(isset($data['og:image']) && $data['og:image'])
        <img src="<?php echo $data['og:image']; ?>" alt="Image"/>
    @endif
</div>
</body>
</html>