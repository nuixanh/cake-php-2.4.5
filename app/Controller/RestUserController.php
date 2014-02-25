<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sonnt4
 * Date: 2/17/14
 * Time: 9:12 AM
 * To change this template use File | Settings | File Templates.
 */

App::uses('User', 'Model');
App::uses('UserSession', 'Model');
App::uses('ErrorCode', 'Common');

class RestUserController extends AppController{

    public $components = array('RequestHandler');

    public function login() {
        $email = $this->request->data('email');
        $password = $this->request->data('password');
        $user = new User();
        $old_user = $user->find('first', array(
            'conditions' => array('User.email' => $email, 'User.password' => $password)
        ));
        $user_id = '';
        $session_id = '';
        if(empty($old_user)){
            $error_code = ErrorCode::FAILURE;
        }else{
            $user_id = $old_user['User']['id'];
            $user->read(null, $user_id);

            $user_session = new UserSession();
            $user_session->set('user_id', $user_id);
            $user_session->save();
//            $session_id = String::uuid();
            $session_id = $user_session->id;
            $user->set('last_session', $session_id);
            $user->save();
//            CakeLog::write('info', print_r($user_session, true));
            $error_code = ErrorCode::SUCCESS;
        }
        $this->set(array(
            'error_code' => $error_code,
            'user_id' => $user_id,
            'session_id' => $session_id,
            '_serialize' => array('error_code', 'user_id', 'session_id')
        ));
    }
    public function signUp() {
        $error_code = ErrorCode::FAILURE;
        $email = $this->request->data('email');
        $password = $this->request->data('password');
        if(empty($email) || empty($password)){
            $error_code = ErrorCode::MISS_PARAM;
        }else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $error_code = ErrorCode::INVALID_EMAIL;
        }else if(strlen($password) < 4){
            $error_code = ErrorCode::INVALID_PASSWORD;
        }else{
            $email = trim($email);
            $password = trim($password);
            $user = new User();
            $old_user = $user->find('first', array(
                'conditions' => array('User.email' => $email)
            ));
            if(empty($old_user)){
                CakeLog::write('info', '---> No user with email ' . $email);
                $user->set('email', $email);
                $user->set('password', $password);
                $user->save();
                $error_code = ErrorCode::SUCCESS;
            }else{
                CakeLog::write('info', '---> existed email ' . $email);
                $error_code = ErrorCode::EXISTED_EMAIL;
            }
        }


//        Debugger::dump($user);
//        CakeLog::write('info', $user->toString());


        $this->set(array(
            'error_code' => $error_code,
            '_serialize' => array('error_code')
        ));
    }
}