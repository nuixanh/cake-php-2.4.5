<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sonnt4
 * Date: 2/21/14
 * Time: 9:41 AM
 * To change this template use File | Settings | File Templates.
 */

function multiple_threads_hit($sites)
{
    $mh = curl_multi_init();//Returns a cURL multi handle resource
    $curl_array = array();

    foreach ($sites as $i => $site) {
        $curl_array[$i] = curl_init($site->url);
        curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, 1);//return the transfer as a string of the return value instead of outputting it out directly.
        curl_setopt($curl_array[$i], CURLOPT_TIMEOUT, 5);//The maximum number of seconds to allow cURL functions to execute.
        curl_setopt($curl_array[$i], CURLOPT_CONNECTTIMEOUT, 5);
        //curl_setopt($curl_array[$i], CURLOPT_VERBOSE, 1);
        curl_multi_add_handle($mh, $curl_array[$i]);//Add a normal cURL handle to a cURL multi handle
    }
    $running = NULL;
//    $res = array();
    do {
        usleep(10000);
        curl_multi_exec($mh, $running);
        $info = curl_multi_info_read($mh);
        if (false !== $info) {
            //var_dump($info);
            $url = curl_getinfo($info['handle'], CURLINFO_EFFECTIVE_URL);
            $ch_info = curl_getinfo($info['handle']);
//            print_r($ch_info);

            foreach ($sites as $i => $site) {
                if($site->url === $url){
                    $site->http_code = $ch_info['http_code'];
                    $site->connect_time = $ch_info['connect_time'];
                    $site->total_time = $ch_info['total_time'];
                    $site->primary_ip = $ch_info['primary_ip'];
                }
            }
//            echo curl_getinfo($info['handle'],CURLINFO_EFFECTIVE_URL) . "\n";
//            echo curl_getinfo($info['handle'],CURLINFO_TOTAL_TIME ) . " seconds \n";
        }
    } while ($running > 0);

    foreach ($sites as $i => $url) {
        curl_multi_remove_handle($mh, $curl_array[$i]);
    }

    curl_multi_close($mh);
    return $sites;
}
//$r=dns_get_record('abctt.com.vn');
//print_r($r);
