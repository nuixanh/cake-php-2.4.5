<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Son
 * Date: 3/3/14
 * Time: 11:04 PM
 * To change this template use File | Settings | File Templates.
 */
//include_once APP . 'Config' . DS . 'database.php';

App::uses('HitUtil', 'Common');
App::uses('CommonUtil', 'Common');
App::uses('Constants', 'Common');
App::uses('WindowsPhonePushNotification', 'Common');

class HitShell extends AppShell
{

    public $uses = array('Site', 'Hit', 'Channel');

    public function main()
    {
        $no_of_threads = 10;
        $this->out('Started.');

        $site_list = $this->Site->find('all', array(
            'conditions' => array('Site.active' => true,
                'Site.next_monitor_time < ' => CommonUtil::getMysqlCurrentTimeWithInterval(-60)
            )
        ));
        foreach ($site_list as $site) {
//            $this->out(print_r($site, true));
            $this->out($site['Site']['id']);
            $this->Site->read(null, $site['Site']['id']);
            $this->Site->set('next_monitor_time', CommonUtil::getMysqlCurrentTimeWithInterval(10));
            $this->Site->save();
        }

        $sites=array();
        $arr_of_arr=array();
        $total=0;
        $i=0;

        $site_list = $this->Site->find('all', array(
            'conditions' => array('Site.active' => true,
                'Site.next_monitor_time >= ' => CommonUtil::getMysqlCurrentTime(),
                'Site.next_monitor_time < ' => CommonUtil::getMysqlCurrentTimeWithInterval(60)
            ),
            'order' => array('Site.last_response_time')
        ));
        foreach ($site_list as $one_site) {
            if($i % $no_of_threads === 0){
                $sites=array();
                array_push($arr_of_arr, $sites);
            }
            $site = new stdClass();
            $site->id = $one_site['Site']['id'];
            $site->url = $one_site['Site']['url'];
            $site->interval = $one_site['Site']['interval'];
            $j = floor ($i / $no_of_threads);
            $total=array_push($arr_of_arr[$j], $site);
            $i++;

//            $this->out(print_r($site, true));
        }
        print "\n--- Total --- " . $total . "\n";
        if($total > 0){
            foreach ($arr_of_arr as $sites) {
                $hits = HitUtil::multipleThreadsHitSite($sites);
//            print_r($hits);
                $create = CommonUtil::getMysqlCurrentTime();
                foreach ($hits as $hit) {
                    $this->Hit->create();
                    $this->Hit->set(array(
                        'site_id' => $hit->id,
                        'url' => $hit->url,
                        'http_code' => property_exists($hit,'http_code') && $hit->http_code != null? $hit->http_code: 0,
                        'connect_time' => property_exists($hit,'connect_time') && $hit->connect_time != null? $hit->connect_time: 0,
                        'total_time' => property_exists($hit,'total_time') && $hit->total_time != null? $hit->total_time: 15,
                        'primary_ip' => property_exists($hit,'primary_ip') && $hit->primary_ip != null? $hit->primary_ip: 0,
                        'created' => $create,
                        'redirect' => property_exists($hit,'redirect') && $hit->redirect != null? $hit->redirect: 0
                    ));
                    $this->Hit->save();
//                    $insert_query = "insert INTO hits (id, site_id, url, http_code, connect_time, total_time, primary_ip, created, redirect) VALUES ('"
//                        . String::uuid() . "', '" . $hit->id . "', '" . $hit->url . "', " . $hit->http_code . ", " . $hit->connect_time
//                        . ", " . $hit->total_time . ",'". $hit->primary_ip ."','" . $create . "'," . $hit->redirect . ")";
//                    mysql_query($insert_query);
                    $last_monitor_status = property_exists($hit,'http_code') && $hit->http_code == 200 ? 1: 0;
                    $this->Site->read(null, $hit->id);
//                    print $this->Site->data['Site']['next_monitor_time'];
                    $nowUtc = new DateTime($this->Site->data['Site']['next_monitor_time'],  new DateTimeZone(Constants::DEFAULT_TIMEZONE));
                    $nowUtc->add(new DateInterval('PT' . $this->Site->data['Site']['interval'] . 'M'));
//                    $this->out(date_format($nowUtc, "Y-m-d H:i:s"));
                    $this->Site->set(array(
                        'last_monitor_time' => $create,
                        'next_monitor_time' => date_format($nowUtc, "Y-m-d H:i:s"),
                        'last_monitor_status' => $last_monitor_status,
                        'last_response_time' => property_exists($hit,'total_time') && $hit->total_time != null? $hit->total_time: 15
                    ));
                    $this->Site->save();
//
//                    $site_update = "update sites set last_monitor_time = '" . $create . "', next_monitor_time = next_monitor_time + INTERVAL " . $hit->interval
//                        . " MINUTE, last_monitor_status = " . $last_monitor_status . ", last_response_time = " . $hit->total_time . " where id = '" . $hit->id ."'";
//                $site_update = "update sites set last_monitor_time = '" . $create . "', next_monitor_time = next_monitor_time + INTERVAL " . 1
//                    . " MINUTE, last_monitor_status = " . $last_monitor_status . ", last_response_time = " . $hit->total_time . " where id = '" . $hit->id ."'";
//                    mysql_query($site_update);
                }
                print "\n\n";
            }

        }
        $this->out('End.');

    }

    public function hit_one()
    {
        $this->out('Site ID: ' . $this->args[0]);
        $site_id = $this->args[0];
        $this->Site->read(null, $site_id);
        $url = $this->Site->data['Site']['url'];
        $interval = $this->Site->data['Site']['interval'];
        $user_id = $this->Site->data['Site']['user_id'];
        $site_name = $this->Site->data['Site']['name'];
        $info = HitUtil::hitSiteByUrl($url);

        $this->Site->set('last_monitor_status', $info['http_code'] == 200 ? 1 : 0);
        $this->Site->set('last_response_time', $info['total_time']);
        $this->Site->set('last_monitor_time', CommonUtil::getMysqlCurrentTime());
        $this->Site->set('next_monitor_time', CommonUtil::getMysqlCurrentTimeWithInterval($interval));
        $this->Site->save();

        $this->Hit->set(array(
            'site_id' => $site_id,
            'url' => $url,
            'http_code' => $info['http_code'] != null? $info['http_code'] : 0,
            'connect_time' => $info['connect_time'] != null? $info['connect_time'] : 0,
            'total_time' => $info['total_time'] != null? $info['total_time'] : 15,
            'primary_ip' => $info['primary_ip'] != null? $info['primary_ip'] : 0,
            'redirect' => $info['redirect'] != null? $info['redirect'] : 0
        ));
        $this->Hit->save();
        if($info['http_code'] != 200){
            $channels = $this->Channel->find('all', array(
                'conditions' => array('Channel.user_id' => $user_id)
            ));
            foreach ($channels as $channel) {
                $ch_url = $channel['Channel']['url'];
                $notif = new WindowsPhonePushNotification($ch_url);
                $this->out('Channel URL: ' . $ch_url);
                $notif->push_toast("DOWN site [" . $site_name . ']', "[" . $url ."]");
            }
        }
    }
}