<?php

/**
 * Created by PhpStorm.
 * User: mambanegra
 * Date: 20/2/16
 * Time: 16:36
 */

class ManageDatabase {
    private static $instance;

    public static function getInstance() {

        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
    }

    public function createDatabase($reset = false) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD);
        if ($mysqli === false) {
            die("ERROR: Could not connect. " . $mysqli->error);
        }

        if($reset) {
            $sql = "DROP DATABASE ". DB_NAME;
            $mysqli->query($sql);
        }

        // Attempt create database query execution
        $sql = "CREATE DATABASE ". DB_NAME;
        if ($mysqli->query($sql)) {
            $mysqli->close();
            $this->createTables();
            $rvalue = true;
        } else {
            $rvalue = $mysqli->error;
            $mysqli->close();
        }

        return $rvalue;
    }

    private function createTables() {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if($mysqli === false){
            die("ERROR: Could not connect. " . $mysqli->error);
        }

        // Attempt create table query execution
        $sql = "CREATE TABLE keyaction(
            kaid INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
            type VARCHAR(255) NOT NULL,
            username VARCHAR(255) NOT NULL,
            timestamp VARCHAR(255) NOT NULL,
            page_url VARCHAR(512) NOT NULL,
            session VARCHAR(255),
            language VARCHAR(255),
            page_from VARCHAR(512),
            post_event BOOLEAN DEFAULT 0,
            initial_referrer VARCHAR(255),
            timestamp_end VARCHAR(255)
            )";

        if (!$mysqli->query($sql)){
            $saveerror = $mysqli->error;
            $mysqli->query($mysqli, "DROP DATABASE ".DB_NAME);
            die("ERROR: Could not able to execute $sql. " . $saveerror);
        }

        $sql = "CREATE TABLE videovalue(
            vaid INT(11) NOT NULL PRIMARY KEY,
            currenttime FLOAT,
            playback_rate TINYINT,
            paused BOOLEAN,
            error VARCHAR(255),
            network_state TINYINT,
            ready_state TINYINT,
            event_timestamp VARCHAR(255),
            init_timestamp VARCHAR(255),
            type VARCHAR(255),
            prevtime FLOAT,
            FOREIGN KEY (vaid) REFERENCES keyaction(kaid) ON UPDATE CASCADE ON DELETE RESTRICT
            )";

        if (!$mysqli->query($sql)){
            $saveerror = $mysqli->error;
            $mysqli->query($mysqli, "DROP DATABASE ".DB_NAME);
            die("ERROR: Could not able to execute $sql. " . $saveerror);
        }
    }

    /**
     * @param $values hasharray with column name and value
     */
    function insertRow($values, $table) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if($mysqli === false){
            return false;
        }

        array_walk($values, create_function('&$i,$k','$i=" $k=\"$i\"";'));
        $parsevalues = implode(', ', $values);

        $sql = "INSERT INTO ".$table." SET ".$parsevalues;

        if (!$mysqli->query($sql)){
            $mysqli->close();
            return false;
        }

        $id = $mysqli->insert_id;
        $mysqli->close();

        return $id;
    }

    function searchUser($userid) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if($mysqli === false){
            return false;
        }

        $sql = sprintf("SELECT * FROM keyaction WHERE username='%s' AND username!='' ORDER BY timestamp", $userid);

        $clicks = array();
        if ($result = $mysqli->query($sql)) {
            while ($obj = $result->fetch_object()) {
                $clicks[] = $obj;
            }
        }

        $mysqli->close();
        return $clicks;
    }

    function getNClicks($userid) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if($mysqli === false){
            return false;
        }

        $sql = sprintf("SELECT count(*) as count, type FROM keyaction WHERE username='%s' GROUP BY type", $userid);

        $clicks = array();
        if ($result = $mysqli->query($sql)) {
            while ($obj = $result->fetch_object()) {
                $clicks[] = $obj;
            }
        }

        $mysqli->close();
        return $clicks;
    }

    function getConections($userid) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if($mysqli === false){
            return false;
        }

        $sql = 'SELECT count(*) as count, FROM_UNIXTIME(timestamp/1000, "%d-%m-%Y") as date FROM keyaction WHERE username="'.$userid.'" GROUP BY FROM_UNIXTIME(timestamp/1000, "%d-%m-%Y") ORDER BY timestamp';

        $dates = array();
        if ($result = $mysqli->query($sql)) {
            while ($obj = $result->fetch_object()) {
                $dates[] = $obj;
            }
        }

        $mysqli->close();
        return $dates;
    }

    function getClicksExtra($userid) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if($mysqli === false){
            return false;
        }

        $time = strtotime(COURSE_EXTRA)*1000;
        $sql = sprintf('SELECT * FROM keyaction WHERE username="%s" AND timestamp>=%s ORDER BY timestamp', $userid, $time);

        $clicks = array();
        if ($result = $mysqli->query($sql)) {
            while ($obj = $result->fetch_object()) {
                $clicks[] = $obj;
            }
        }

        $mysqli->close();
        return $clicks;
    }

    function getClicksWeek($userid) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if($mysqli === false){
            return false;
        }

        $clicks = array();
        for($i=1; $i<=4; $i++) {
            $constini = 'SEMANA'.$i.'_INI';
            $constfin = 'SEMANA'.$i.'_FIN';
            $timeini = strtotime(constant($constini)) * 1000;
            $timefin = strtotime(constant($constfin)) * 1000;
            $sql = sprintf('SELECT count(*) as count FROM keyaction WHERE username="%s" AND timestamp>=%s AND TIMESTAMP<=%s ORDER BY timestamp', $userid, $timeini, $timefin);

            if ($result = $mysqli->query($sql)) {
                while ($obj = $result->fetch_object()) {
                    $clicks[$i] = $obj->count;
                }
            }
        }

        $mysqli->close();
        return $clicks;
    }

    function getResources($userids = array()) {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        if($mysqli === false){
            return false;
        }

        $whereclause = '';
        if(!empty($userids)) {
            $temp = array();
            foreach($userids as $uid) {
                $temp[] = 'username="'.$uid.'"';
            }
            $whereclause = ' WHERE '.implode(' OR ', $temp);
        }

        $sql = sprintf("SELECT page_url FROM keyaction %s ORDER BY page_url", $whereclause);

        $resources = array();
        if ($result = $mysqli->query($sql)) {
            while ($obj = $result->fetch_object()) {
                $resources[] = $obj->page_url;
            }
        }

        $mysqli->close();
        return $resources;
    }

    function getQuizName($quizid) {
        $mysqli = new mysqli(DB_HOST_G, DB_USER_G, DB_PASSWORD_G, DB_NAME_G);
        if($mysqli === false){
            return false;
        }

        $sql = sprintf("SELECT title FROM quiz_metadata WHERE id=%s", $quizid);

        $title = '';
        if ($result = $mysqli->query($sql)) {
            $obj = $result->fetch_object();
            $title = utf8_encode($obj->title);
        }

        $mysqli->close();
        return $title;
    }

    function getLectureName($lectureid) {
        $mysqli = new mysqli(DB_HOST_G, DB_USER_G, DB_PASSWORD_G, DB_NAME_G);
        if($mysqli === false){
            return false;
        }

        $sql = sprintf("SELECT title FROM lecture_metadata WHERE id=%s", $lectureid);

        $title = '';
        if ($result = $mysqli->query($sql)) {
            $obj = $result->fetch_object();
            $title = utf8_encode($obj->title);
        }

        $mysqli->close();
        return $title;
    }
}