<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sonnt4
 * Date: 2/21/14
 * Time: 9:41 AM
 * To change this template use File | Settings | File Templates.
 */
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "hitFunctions.php";
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "Common/CommonUtil.php";
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "../lib/Cake/basics.php";
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "../lib/Cake/Core/App.php";
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "../lib/Cake/Core/Configure.php";
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "../lib/Cake/Utility/Hash.php";
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "../lib/Cake/Utility/String.php";


$user_name = "sitemon";
$password = "s!i@t#emon";
$database = "sitemon";
$server = "db";

$no_of_threads = 10;

$db_handle = mysql_connect($server, $user_name, $password);
$db_found = mysql_select_db($database, $db_handle);

if ($db_found) {

    $now = new DateTime('now', new DateTimeZone('Asia/Saigon'));
    $from = $now->format('Y-m-d H:i:s');
    $now->add(new DateInterval('PT60M'));
    $to = $now->format('Y-m-d H:i:s');
    $SQL = "SELECT * FROM sites where active=true and next_monitor_time >= '" . $from . "' and next_monitor_time < '" . $to . "' order by last_response_time";
    print $SQL . "\n";
    $result = mysql_query($SQL);

    $sites=array();
    $arr_of_arr=array();
    $total=0;
    $i=0;
    while ( $db_field = mysql_fetch_assoc($result) ) {
        if($i % $no_of_threads === 0){
            $sites=array();
            array_push($arr_of_arr, $sites);
        }
        $site = new stdClass();
        $site->id = $db_field['id'];
        $site->url = $db_field['url'];
        $site->interval = $db_field['interval'];
        $j = floor ($i / $no_of_threads);
        $total=array_push($arr_of_arr[$j], $site);
        $i++;
    }
//    print_r($arr_of_arr);
    print "\n--- Total --- " . $total . "\n";
    if($total > 0){
        foreach ($arr_of_arr as $sites) {
            $hits = multiple_threads_hit($sites);
//            print_r($hits);
            $create = CommonUtil::getMysqlCurrentTime();
            foreach ($hits as $hit) {
                $insert_query = "insert INTO hits (id, site_id, url, http_code, connect_time, total_time, primary_ip, created, redirect) VALUES ('"
                    . String::uuid() . "', '" . $hit->id . "', '" . $hit->url . "', " . $hit->http_code . ", " . $hit->connect_time
                    . ", " . $hit->total_time . ",'". $hit->primary_ip ."','" . $create . "'," . $hit->redirect . ")";
                mysql_query($insert_query);
//                $last_monitor_status = ($hit->http_code == 200 || $hit->http_code == 301)? 1: 0;
                $last_monitor_status = $hit->http_code == 200 ? 1: 0;

                $site_update = "update sites set last_monitor_time = '" . $create . "', next_monitor_time = next_monitor_time + INTERVAL " . $hit->interval
                    . " MINUTE, last_monitor_status = " . $last_monitor_status . ", last_response_time = " . $hit->total_time . " where id = '" . $hit->id ."'";
//                $site_update = "update sites set last_monitor_time = '" . $create . "', next_monitor_time = next_monitor_time + INTERVAL " . 1
//                    . " MINUTE, last_monitor_status = " . $last_monitor_status . ", last_response_time = " . $hit->total_time . " where id = '" . $hit->id ."'";
                mysql_query($site_update);
            }
            print "\n\n";
        }

    }

    mysql_close($db_handle);

}else {

    print "Database NOT Found ";
    mysql_close($db_handle);

}