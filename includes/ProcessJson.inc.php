<?php

/**
 * Created by PhpStorm.
 * User: mambanegra
 * Date: 20/2/16
 * Time: 15:15
 */

class ProcessJson {

    private $file_path;
    const VIDEO = 'user.video.lecture.action';
    const PAGE = 'pageview';

    function __construct($file) {
        $this->file_path = $file;
    }

    function loadData($reset = false)
    {
        $handle = fopen($this->file_path, "r");
        if (!$handle) {
            return false;
        }

        // Create database
        require_once('ManageDatabase.php');

        $db = ManageDatabase::getInstance();
        $db->createDatabase($reset);

        $realcolumns = array(
            'key' => 'type',
            'username' => 'username',
            'timestamp' => 'timestamp',
            'page_url' => 'page_url',
            'session' => 'session',
            'language' => 'language',
            'from' => 'page_from',
            '13' => 'post_event',
            '14' => 'initial_referrer',
            '30' => 'timestamp_end');
        $videocolumns = array(
            'currentTime' => 'currenttime',
            'playbackRate' => 'playback_rate',
            'paused' => 'paused',
            'error' => 'error',
            'networkState' => 'network_state',
            'readyState' => 'ready_state',
            'eventTimestamp' => 'event_timestamp',
            'initTimestamp' => 'init_timestamp',
            'type' => 'type',
            'prevTime' => 'prevtime',
        );
        // Process each row
        while ($line = stream_get_line($handle, 4096, "\n")) {
            // Decode line
            $json = json_decode($line, TRUE);

            if($json['key'] != self::VIDEO && $json['key'] != self::PAGE) {
                $tmpkey = json_decode($json['key'], TRUE);
                $json['key'] = $tmpkey['key'];
            }

            // Process action values
            $row = array();
            foreach ($json as $key => $val) {
                if (array_key_exists($key, $realcolumns)) { // required value
                    if (is_array($val)) {
                        $row[$realcolumns[$key]] = $val[0];
                    } else {
                        $row[$realcolumns[$key]] = $val;
                    }
                }
            }
            $id = $db->insertRow($row, 'keyaction');

            // Process video values
            if ($id !== false && $json['key'] == self::VIDEO) {
                $videovalue = json_decode($json['value'], true);

                $row = array('vaid' => $id);
                foreach ($videovalue as $key => $val) {
                    if(array_key_exists($key, $videocolumns)) {
                        $row[$videocolumns[$key]] = is_bool($val) ? (int)$val : $val;
                    }
                }
                $db->insertRow($row, 'videovalue');
            }
        }

        fclose($handle);
    }

    private function printStringLine($value, $nl = '') {
        echo !empty($nl) ? $nl.': ': '';
        echo $value. '<br />';
    }
}