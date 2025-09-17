<?php

return [
    'allow' => [
        'image' => [
            'avif', 'bmp', 'cgm', 'g3', 'gif', 'ief', 'jpeg', 'jpg', 'jpe', 'ktx', 'png', 'btif',
            'sgi', 'svg', 'svgz', 'tiff', 'tif', 'psd', 'uvi', 'uvvi', 'uvg', 'uvvg', 'djvu', 'djv',
            'sub', 'dwg', 'dxf', 'fbs', 'fpx', 'fst', 'mmr', 'rlc', 'mdi', 'wdp', 'npx', 'wbmp', 'xif',
            'webp', '3ds', 'ras', 'cmx', 'fh', 'fhc', 'fh4', 'fh5', 'fh7', 'ico', 'sid', 'pcx', 'pic', 'pct',
            'pnm', 'pbm', 'pgm', 'ppm', 'rgb', 'tga', 'xbm', 'xpm', 'xwd', 'heic', 'heif',
        ],
        'video' => [
            '3gp', '3g2', 'h261', 'h263', 'h264', 'jpgv', 'jpm', 'jpgm', 'mj2', 'mjp2', 'ts', 'm2t', 'm2ts', 'mts',
            'mp4', 'mp4v', 'mpg4', 'mpeg', 'mpg', 'mpe', 'm1v', 'm2v', 'ogv', 'qt', 'mov', 'uvh', 'uvvh', 'uvm', 'uvvm',
            'uvp', 'uvvp', 'uvs', 'uvvs', 'uvv', 'uvvv', 'dvb', 'fvt', 'mxu', 'm4u', 'pyv', 'uvu', 'uvvu', 'viv', 'webm',
            'f4v', 'fli', 'flv', 'm4v', 'mkv', 'mk3d', 'mks', 'mng', 'asf', 'asx', 'vob', 'wm', 'wmv', 'wmx', 'wvx', 'avi',
            'movie', 'smv', 'hevc',
        ],
        'audio' => [
            'adp', 'au', 'snd', 'mid', 'midi', 'kar', 'rmi', 'm4a', 'mp4a', 'mpga', 'mp2', 'mp2a', 'mp3', 'm2a', 'm3a',
            'oga', 'ogg', 'spx', 'opus', 's3m', 'sil', 'uva', 'uvva', 'eol', 'dra', 'dts', 'dtshd', 'lvp', 'pya', 'ecelp4800',
            'ecelp7470', 'ecelp9600', 'rip', 'weba', 'aac',	'aif', 'aiff', 'aifc', 'caf', 'flac', 'mka', 'm3u', 'wax', 'wma',
            'ram', 'ra', 'rmp', 'wav', 'xm',
        ],
        'document' => ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'pdf', 'csv'],
        'archive'  => ['rar', 'zip', 'gz'],
    ],
    'forbidden' => [
        // Executable file extensions
        'exe', 'bat', 'com', 'cmd', 'inf', 'ipa', 'osx', 'pif', 'run', 'wsh', 'sh', 'csh', 'phar', 'bin',

        // Possible server files
        'php', 'php2', 'php3', 'php4', 'php5', 'php6', 'php7', 'phps', 'phps', 'py', 'go', 'java',
        'bytecode', 'js', 'ts', 'rs', 'cs', 'rb', 'cpp', 'c', 'pht', 'phtm', 'phtml', 'pgif', 'shtml',
        'html', 'htaccess', 'inc', 'hphp', 'ctp', 'module', 'cgi', 'sql', 'env',
    ],
];
