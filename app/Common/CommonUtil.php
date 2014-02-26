<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sonnt4
 * Date: 2/21/14
 * Time: 2:40 PM
 * To change this template use File | Settings | File Templates.
 */

class CommonUtil
{
    public static function toValidURL($text)
    {
        $url = parse_url($text);
//        print_r($url);
        if (!isset($url['scheme'])) {
            $url = parse_url("http://" . $text);
        }
        $surl = $url['scheme'] . "://";
        if (isset($url['user']) && isset($url['pass'])) {
            $surl = $surl . $url['user'] . ":" . $url['pass'] . "@";
        }
        if (!isset($url['path'])) {
            $url['path'] = '/';
        }
        $surl = $surl . $url['host'];
        if (isset($url['port'])) {
            $surl = $surl . ":" . $url['port'];
        }
        $surl = $surl . $url['path'];
        if (isset($url['query'])) {
            $surl = $surl . "?" . $url['query'];
        }
        if (isset($url['fragment'])) {
            $surl = $surl . "#" . $url['fragment'];
        }
        //'http://username:password@hostname:8080/path?arg=value&b=1#anchor'
        return $surl;
    }
    public static function getMysqlCurrentTime(){
        $now = new DateTime('now', new DateTimeZone('Asia/Saigon'));
        return $now->format('Y-m-d H:i:s');
    }
    public static function getMysqlCurrentTimeWithInterval($interval){
        $now = new DateTime('now', new DateTimeZone('Asia/Saigon'));
        if($interval > 0){
            $now->add(new DateInterval('PT' . $interval . 'M'));
        }else{
            $now->sub(new DateInterval('PT' . (0 - $interval) . 'M'));
        }
        return $now->format('Y-m-d H:i:s');
    }
}