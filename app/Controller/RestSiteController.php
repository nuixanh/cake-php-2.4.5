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
App::uses('ErrorCode', 'Common');
App::uses('AuthUtil', 'Common');

class RestSiteController extends AppController{
    public $components = array('RequestHandler');

    public function save() {
        $error_code = ErrorCode::FAILURE;
        $user_id = $this->request->data('user_id');
        $session_id = $this->request->data('session_id');
        $site_id = $this->request->data('site_id');
        $name = $this->request->data('name');
        $url = $this->request->data('url');
        $active = $this->request->data('active');
        $site = new Site();

        CakeLog::write('info', $user_id . " ");
        CakeLog::write('info', $session_id . " ");

        if(AuthUtil::isValidSession($user_id, $session_id) !== true){
            $error_code = ErrorCode::INVALID_SESSION;
        }else{
            if(empty($site_id)){
                $site_id = String::uuid();
                $site->set('user_id', $user_id);
            }else{
                $r_site = $site->find(null, $site_id);
                if(empty($r_site)){
                    $error_code = ErrorCode::NO_EXISTED_SITE;
                }
            }
            if($error_code !== ErrorCode::NO_EXISTED_SITE){
                $site->set('name', $name);
                $site->set('url', $url);
                $site->set('active', $active);
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