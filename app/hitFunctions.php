<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sonnt4
 * Date: 2/21/14
 * Time: 9:41 AM
 * To change this template use File | Settings | File Templates.
 */

function get_hit($site){
    $ch = curl_init($site->url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);//The maximum number of seconds to allow cURL functions to execute.
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_exec($ch);
    $info = null;
    if(!curl_errno($ch))
    {
        $info = curl_getinfo($ch);
//        echo 'Took ' . $info['total_time'] . ' seconds to send a request to ' . $info['url'] . "\n";
    }

    curl_close($ch);
    return $info;
}
function multiple_threads_hit($sites)
{
    $mh = curl_multi_init();//Returns a cURL multi handle resource
    $curl_array = array();

    foreach ($sites as $i => $site) {
        $curl_array[$i] = curl_init($site->url);
        curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, 1);//return the transfer as a string of the return value instead of outputting it out directly.
        curl_setopt($curl_array[$i], CURLOPT_TIMEOUT, 15);//The maximum number of seconds to allow cURL functions to execute.
        curl_setopt($curl_array[$i], CURLOPT_CONNECTTIMEOUT, 15);
        //curl_setopt($curl_array[$i], CURLOPT_VERBOSE, 1);
        curl_multi_add_handle($mh, $curl_array[$i]);//Add a normal cURL handle to a cURL multi handle
    }
    $running = NULL;
//    $res = array();
    do {
        usleep(10000);
        curl_multi_exec($mh, $running);
//        curl_multi_select($mh);
        $info = curl_multi_info_read($mh, $msgs_in_queue);
//        echo "info" . $info . "\t\trunning = ". $running . "\n";
//        echo "$msgs_in_queue msgs_in_queue\t\trunning = ". $running . "\n";
//        if($info === FALSE){
//            echo "----------------no more to get at this point \t\trunning = ". $running . "\n";
//        }
        if (FALSE !== $info) {
//            $url = curl_getinfo($info['handle'], CURLINFO_EFFECTIVE_URL);
            $ch_info = curl_getinfo($info['handle']);
            $url = $ch_info['url'];
            $http_code = $ch_info['http_code'];
//            echo $ch_info['http_code'] . "\n";
            echo $url . "\t\t". $ch_info['http_code'] . "\n";
            if($http_code === 301){//re-direct
                $site = new stdClass();
                $site->url = $url;
                $ch_info = get_hit($site);
                $ch_info['redirect'] = 1;
            }else{
                $ch_info['redirect'] = 0;
            }
//            print_r($ch_info);

            foreach ($sites as $site) {
                if($site->url === $url){
                    $site->http_code = $ch_info['http_code'];
                    $site->connect_time = $ch_info['connect_time'];
                    $site->total_time = $ch_info['total_time'];
                    $site->primary_ip = $ch_info['primary_ip'];
                    $site->redirect = $ch_info['redirect'];
                }
            }

        }
    } while ($running > 0 || $msgs_in_queue > 0);

    foreach ($sites as $i => $url) {
        curl_multi_remove_handle($mh, $curl_array[$i]);
    }

    curl_multi_close($mh);
    return $sites;
}
//$r=dns_get_record('abctt.com.vn');
//print_r($r);
