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
App::uses('ErrorCode', 'Common');
App::uses('AuthUtil', 'Common');
App::uses('CommonUtil', 'Common');

class RestSiteController extends AppController{
    public $components = array('RequestHandler');

    public function listSite() {
        $error_code = ErrorCode::FAILURE;
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
        $error_code = ErrorCode::FAILURE;
        $user_id = $this->request->query('user_id');
        $session_id = $this->request->query('session_id');
        $site_id = $this->request->query('site_id');
        $hit = new Hit();
        $data= array();
        $avg = 0;
        if(AuthUtil::isValidSession($user_id, $session_id) !== true){
            $error_code = ErrorCode::INVALID_SESSION;
        }else{
            $hit_list = $hit->find('all', array(
                'conditions' => array('Hit.site_id' => $site_id),
                'fields' => array('Hit.http_code, Hit.total_time, Hit.created'),
                'order' => array('Hit.created'),
            ));
            if(!empty($hit_list)){
                foreach ($hit_list as $hit) {
                    $avg+=$hit['Hit']['total_time'];
                    $h = new stdClass();
                    $h->response_time = $hit['Hit']['total_time'];
                    $h->created = $hit['Hit']['created'];
                    $h->success = ($hit['Hit']['http_code'] == 200 || $hit['Hit']['http_code'] == 301)? true:false;
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
        $site = new Site();

        $valid_url = empty($url) !==true? CommonUtil::toValidURL($url): "";

//        CakeLog::write('info', $user_id . " ");
//        CakeLog::write('info', $session_id . " ");

        if(empty($name) || empty($url) || empty($interval)){
            $error_code = ErrorCode::MISS_PARAM;
        }else if(filter_var($valid_url, FILTER_VALIDATE_URL) === false) {
            $error_code = ErrorCode::INVALID_URL;
        }else if(AuthUtil::isValidSession($user_id, $session_id) !== true){
            $error_code = ErrorCode::INVALID_SESSION;
        }else{
            if(empty($site_id)){
                $site_id = String::uuid();
                $site->set('user_id', $user_id);
                $site->set('last_monitor_status', -1);
                $now = new DateTime('now', new DateTimeZone('Asia/Saigon'));
                $next_monitor = $now->add(new DateInterval('PT' . $interval . 'M'));
                $site->set('next_monitor_time', $next_monitor->format('Y-m-d H:i:s'));
            }else{
                $r_site = $site->find(null, $site_id);
                if(empty($r_site)){
                    $error_code = ErrorCode::NO_EXISTED_SITE;
                }
            }
            if($error_code !== ErrorCode::NO_EXISTED_SITE){
                $site->set('name', $name);
                $site->set('url', $valid_url);
                $site->set('active', $active);
                $site->set('interval', $interval);
                $site->save();
                $error_code = ErrorCode::SUCCESS;
            }
        }
        $this->set(array(
            'error_code' => $error_code,
            'site_id' => $site_id,
            '_serialize' => array('error_code', 'site_id')
        ));
    }

}