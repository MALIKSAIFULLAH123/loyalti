<?php

namespace MetaFox\Log\Support;

use ArrayObject;

class FileLogReader
{
    /**
     * @param  string      $filename
     * @return ArrayObject
     */
    public function get(string $filename): ArrayObject
    {
        // todo partition data to offset for larger log file : etc: 200 MB.
        $result = new ArrayObject();

        if (!file_exists($filename) || !is_readable($filename)) {
            return $result;
        }

        $pattern = "/^\[(?<timestamp>.*)\]\s(?<env>\w+)\.(?<level>\w+):(?<message>.*)/m";
        $content = mf_get_contents($filename);
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER, 0);

        rsort($matches);

        foreach ($matches as $match) {
            $result->append([
                'timestamp' => $match['timestamp'],
                'env'       => $match['env'],
                'level'     => $match['level'],
                'message'   => trim($match['message']),
            ]);
        }

        return $result;
    }
}
