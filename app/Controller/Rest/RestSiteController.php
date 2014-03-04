<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sonnt4
 * Date: 2/20/14
 * Time: 4:47 PM
 * To change this template use File | Settings | File Templates.
 */
App::uses('User', 'Model');
App::uses('Site', 'Model');
App::uses('Hit', 'Model');
App::uses('CakeTime', 'Utility');
App::uses('ErrorCode', 'Common');
App::uses('Constants', 'Common');
App::uses('AuthUtil', 'Common');
App::uses('CommonUtil', 'Common');
App::uses('HitUtil', 'Common');

class RestSiteController extends AppController{
    public $components = array('RequestHandler');

    public function deleteSite() {
        $user_id = $this->request->query('user_id');
        $session_id = $this->request->query('session_id');
        $site_id = $this->request->query('site_id');
        $site = new Site();

        if(AuthUtil::isValidSession($user_id, $session_id) !== true){
            $error_code = ErrorCode::INVALID_SESSION;
        }else{
            $site->delete($site_id, true);
            $error_code = ErrorCode::SUCCESS;
        }
        $this->set(array(
            'error_code' => $error_code,
            '_serialize' => array('error_code')
        ));
    }
    public function listSite() {
        $user_id = $this->request->query('user_id');
        $session_id = $this->request->query('session_id');
        $site = new Site();
        $site_list= array();
        if(AuthUtil::isValidSession($user_id, $session_id) !== true){
            $error_code = ErrorCode::INVALID_SESSION;
        }else{
            $site_list = $site->find('all', array(
                'conditions' => array('Site.user_id' => $user_id)
            ));
            $error_code = ErrorCode::SUCCESS;
        }
        $this->set(array(
            'error_code' => $error_code,
            'data' => $site_list,
            '_serialize' => array('error_code', 'data')
        ));
    }

    public function listHits() {
        $user_id = $this->request->query('user_id');
        $session_id = $this->request->query('session_id');
        $site_id = $this->request->query('site_id');
        $utc_offset = $this->request->query('utcoffset');
        $hit = new Hit();
        $data= array();
        $avg = 0;
        if(empty($utc_offset)){
            $utc_offset = 0;
        }
        $timezoneName = CommonUtil::getTimezoneName($utc_offset);
        if($timezoneName == null){
            $timezoneName = Constants::DEFAULT_TIMEZONE;
        }
        CakeLog::write('info', $timezoneName);
        if(AuthUtil::isValidSession($user_id, $session_id) !== true){
            $error_code = ErrorCode::INVALID_SESSION;
        }else{
            $hit_list = $hit->find('all', array(
                'conditions' => array('Hit.site_id' => $site_id, 'Hit.created > ' => CommonUtil::getMysqlCurrentTimeWithInterval(0 - 60 * 24 * 7)),
                'fields' => array('Hit.http_code, Hit.total_time, Hit.created'),
                'order' => array('Hit.created DESC'),
            ));
            if(!empty($hit_list)){
                foreach ($hit_list as $hit) {
                    $avg+=$hit['Hit']['total_time'];
                    $h = new stdClass();
                    $h->response_time = $hit['Hit']['total_time'];
                    $h->created = $hit['Hit']['created'];
                    $original = new DateTime($h->created, new DateTimeZone(Constants::DEFAULT_TIMEZONE));
                    $original->setTimezone(new DateTimezone($timezoneName));
                    //var_dump($original);
                    $h->created = date_format($original, "Y-m-d H:i:s");
//                    CakeLog::write('info', var_dump($original));
//                    CakeLog::write('info', date_format($original, "Y-m-d H:i:s") . "\t\t " . date_format($modified, "Y-m-d H:i:s"));
//                    CakeLog::write('info', "convert time = " . CakeTime::convert(new DateTime($h->created), new DateTimeZone($timezoneName)));
                    $h->success = $hit['Hit']['http_code'] == 200 ? true:false;
                    array_push($data, $h);
                }
                $avg=$avg/count($hit_list);
            }
            $error_code = ErrorCode::SUCCESS;
        }
        $this->set(array(
            'error_code' => $error_code,
            'data' => $data,
            'avg' => $avg,
            '_serialize' => array('error_code', 'avg', 'data')
        ));
    }
    public function save() {
        $error_code = ErrorCode::FAILURE;
        $user_id = $this->request->data('user_id');
        $session_id = $this->request->data('session_id');
        $site_id = $this->request->data('site_id');
        $name = $this->request->data('name');
        $url = $this->request->data('url');
        $active = $this->request->data('active');
        $interval = $this->request->data('interval');

        CakeLog::write('tracking', $this->request->url . "\n" . print_r($this->request->data, true));

        $site = new Site();

        $valid_url = empty($url) !==true? CommonUtil::toValidURL($url): "";

        if(empty($name) || empty($url) || empty($interval)){
            $error_code = ErrorCode::MISS_PARAM;
        }else if(filter_var($valid_url, FILTER_VALIDATE_URL) === false) {
            $error_code = ErrorCode::INVALID_URL;
        }else if(AuthUtil::isValidSession($user_id, $session_id) !== true){
            $error_code = ErrorCode::INVALID_SESSION;
        }else{
            $user = new User();
            $existed_user = $user->find('first', array(
                'conditions' => array('User.id' => $user_id)
            ));
            $account_type = $existed_user['User']['account_type'];
            $site_count = $site->find('count', array(
                'conditions' => array('Site.user_id' => $user_id)
            ));
//            CakeLog::write('info', print_r($existed_user, true));
//            CakeLog::write('info', $site_count);
//            CakeLog::write('info', $account_type);
            if(empty($site_id) && $account_type == Constants::FREE_ACCOUNT_TYPE && $site_count >= Constants::SITE_MAXIMUM_4_FREE_ACCOUNT){
                $error_code = ErrorCode::OVER_SITE_QUOTA;
            }
            $is_new_site = false;
            if(empty($site_id)){
                $is_new_site = true;
                $site->set('user_id', $user_id);
                $site->set('active', true);
                $site->set('last_monitor_status', -1);
                $now = new DateTime('now', new DateTimeZone(Constants::DEFAULT_TIMEZONE));
                $next_monitor = $now->add(new DateInterval('PT' . $interval . 'M'));
                $site->set('next_monitor_time', $next_monitor->format('Y-m-d H:i:s'));
            }else{
                $r_site = $site->read(null, $site_id);
                if(empty($r_site)){
                    $error_code = ErrorCode::NO_EXISTED_SITE;
                }else{
                    $site->set('active', CommonUtil::toBoolean($active));
                }
            }
            if($error_code !== ErrorCode::NO_EXISTED_SITE && $error_code !== ErrorCode::OVER_SITE_QUOTA){
                $site->set('name', $name);
                $site->set('url', $valid_url);
                $site->set('interval', $interval);
                $site->save();
                if($is_new_site){
                    $app_dir = dirname(APP) . DS . basename(APP);
                    //cd /full/path/to/app && Console/cake myshell myparam
                    //exec("nohup /usr/bin/php -f sleep.php > /dev/null 2>&1 &");
                    //nohup sh -c './cmd2 >result2 && ./cmd1 >result1' &
                    $cmd = 'nohup sh -c "cd ' . $app_dir . ' && Console' . DS . 'cake hit hit_one ' . $site->id . '"  > ~/cake-shell.out 2>&1 &';
                    //echo $cmd;
                    $output = shell_exec($cmd);
                    //echo $output;
                }
                $error_code = ErrorCode::SUCCESS;
            }
        }
        $this->set(array(
            'error_code' => $error_code,
            'site_id' => $site->id,
            '_serialize' => array('error_code', 'site_id')
        ));
    }

}