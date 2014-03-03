<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Son
 * Date: 3/3/14
 * Time: 11:04 PM
 * To change this template use File | Settings | File Templates.
 */

App::uses('HitUtil', 'Common');
App::uses('CommonUtil', 'Common');

class HitShell extends AppShell
{

    public $uses = array('Site', 'Hit');

    public function main()
    {
        $this->out('Hello world.');
    }

    public function hit_one()
    {
        $this->out('Site ID: ' . $this->args[0]);
        $site_id = $this->args[0];
        $this->Site->read(null, $site_id);
        $url = $this->Site->data['Site']['url'];
        $interval = $this->Site->data['Site']['interval'];
        $this->out('URL: ' . $url);
        $info = HitUtil::hitSiteByUrl($url);

        $this->Site->set('last_monitor_status', $info['http_code'] == 200 ? 1 : 0);
        $this->Site->set('last_response_time', $info['total_time']);
        $this->Site->set('last_monitor_time', CommonUtil::getMysqlCurrentTime());
        $this->Site->set('next_monitor_time', CommonUtil::getMysqlCurrentTimeWithInterval($interval));
        $this->Site->save();

        $this->Hit->set(array(
            'site_id' => $site_id,
            'url' => $url,
            'http_code' => $info['http_code'],
            'connect_time' => $info['connect_time'],
            'total_time' => $info['total_time'],
            'primary_ip' => $info['primary_ip'],
            'redirect' => $info['redirect']
        ));
        $this->Hit->save();
    }
}