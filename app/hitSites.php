<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sonnt4
 * Date: 2/21/14
 * Time: 9:41 AM
 * To change this template use File | Settings | File Templates.
 */
include_once "hitFunctions.php";
include_once "Common/CommonUtil.php";
include_once "../lib/Cake/basics.php";
include_once "../lib/Cake/Core/App.php";
include_once "../lib/Cake/Core/Configure.php";
include_once "../lib/Cake/Utility/Hash.php";
include_once "../lib/Cake/Utility/String.php";


$user_name = "sitemon";
$password = "s!i@t#emon";
$database = "sitemon";
$server = "127.0.0.1";

$no_of_threads = 10;

$db_handle = mysql_connect($server, $user_name, $password);
$db_found = mysql_select_db($database, $db_handle);

if ($db_found) {

    $now = new DateTime('now', new DateTimeZone('Asia/Saigon'));
    $from = $now->format('Y-m-d H:i:s');
    $now->add(new DateInterval('PT60M'));
    $to = $now->format('Y-m-d H:i:s');
    $SQL = "SELECT * FROM sites where active=true and next_monitor_time >= '" . $from . "' and next_monitor_time < '" . $to . "'";
    print $SQL;
    $result = mysql_query($SQL);

    $sites=array();
    $total=0;
    while ( $db_field = mysql_fetch_assoc($result) ) {
        $site = new stdClass();
        $site->id = $db_field['id'];
        $site->url = $db_field['url'];
        $site->interval = $db_field['interval'];
//        var_dump($site);
        $total=array_push($sites, $site);
    }
//    print_r($sites);
    print "\n--- Total --- " . $total . "\n";
    if($total > 0){
        $hits = multiple_threads_hit($sites);
//        print_r($hits);
        $create = CommonUtil::getMysqlCurrentTime();
        foreach ($hits as $i => $hit) {
//            $insert_query = "insert INTO hits (id, site_id, url, http_code, connect_time, total_time, primary_ip) VALUES ('"
//                . String::uuid() . "', '" . $hit->id . "')";
            $insert_query = "insert INTO hits (id, site_id, url, http_code, connect_time, total_time, primary_ip, created) VALUES ('"
                . String::uuid() . "', '" . $hit->id . "', '" . $hit->url . "', " . $hit->http_code . ", " . $hit->connect_time
                . ", " . $hit->total_time . ",'". $hit->primary_ip ."','" . $create . "')";
            mysql_query($insert_query);
            $site_update = "update sites set last_monitor_time = '" . $create . "', next_monitor_time = next_monitor_time + INTERVAL " . $hit->interval . " MINUTE where id = '" . $hit->id ."'";
            mysql_query($site_update);
        }
    }

    mysql_close($db_handle);

}else {

    print "Database NOT Found ";
    mysql_close($db_handle);

}