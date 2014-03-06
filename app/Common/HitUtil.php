<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Son
 * Date: 3/2/14
 * Time: 1:07 PM
 * To change this template use File | Settings | File Templates.
 */
App::uses('Constants', 'Common');
App::uses('CommonUtil', 'Common');
class HitUtil {
    public static function hitRedirectURL($site){
        $ch = curl_init($site->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, Constants::CONNECT_TIMEOUT);//The maximum number of seconds to allow cURL functions to execute.
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, Constants::CONNECT_TIMEOUT);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_exec($ch);
        $info = null;
        if(!curl_errno($ch))
        {
            $info = curl_getinfo($ch);
        }

        curl_close($ch);
        return $info;
    }
    public static function hitSiteByUrl($url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, Constants::CONNECT_TIMEOUT);//The maximum number of seconds to allow cURL functions to execute.
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, Constants::CONNECT_TIMEOUT);
        curl_exec($ch);
        $info = null;
        if(!curl_errno($ch))
        {
            $info = curl_getinfo($ch);
            $url = $info['url'];
            $http_code = $info['http_code'];
            if($http_code === 301){//re-direct
                $site = new stdClass();
                $site->url = $url;
                $info = HitUtil::hitRedirectURL($site);
                $info['redirect'] = 1;
            }else{
                $info['redirect'] = 0;
            }

        }

        curl_close($ch);
        return $info;
    }

    public static function multipleThreadsHitSite($sites)
    {
        $mh = curl_multi_init();//Returns a cURL multi handle resource
        $curl_array = array();

        foreach ($sites as $i => $site) {
            $curl_array[$i] = curl_init($site->url);
            curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, 1);//return the transfer as a string of the return value instead of outputting it out directly.
            curl_setopt($curl_array[$i], CURLOPT_TIMEOUT, Constants::CONNECT_TIMEOUT);//The maximum number of seconds to allow cURL functions to execute.
            curl_setopt($curl_array[$i], CURLOPT_CONNECTTIMEOUT, Constants::CONNECT_TIMEOUT);
            curl_multi_add_handle($mh, $curl_array[$i]);//Add a normal cURL handle to a cURL multi handle
        }
        $running = NULL;
        do {
            usleep(10000);
            curl_multi_exec($mh, $running);
            $info = curl_multi_info_read($mh, $msgs_in_queue);
            if (FALSE !== $info) {
                $ch_info = curl_getinfo($info['handle']);
                $url = $ch_info['url'];
                $http_code = $ch_info['http_code'];
                echo $url . "\t\t". $http_code . "\n";
                if($http_code === 301){//re-direct
                    $site = new stdClass();
                    $site->url = $url;
                    $ch_info = HitUtil::hitRedirectURL($site);
                    $ch_info['redirect'] = 1;
                }else if($http_code === 0){//FAIL - check again
                    $site = new stdClass();
                    $site->url = $url;
                    $ch_info = HitUtil::hitRedirectURL($site);
                    $ch_info['redirect'] = 0;
                }else{
                    $ch_info['redirect'] = 0;
                }

                foreach ($sites as $site) {
                    if($site->url === $url){
                        $site->http_code = $ch_info['http_code'];
                        $site->connect_time = $ch_info['connect_time'];
                        $site->total_time = $ch_info['total_time'];
                        $site->primary_ip = isset($ch_info['primary_ip'])? $ch_info['primary_ip'] : 0;
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
}