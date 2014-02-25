<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sonnt4
 * Date: 2/20/14
 * Time: 5:35 PM
 * To change this template use File | Settings | File Templates.
 */

App::uses('UserSession', 'Model');
class AuthUtil {
    public static function isValidSession($user_id, $session_id) {
        $user_session = new UserSession();
        $result = $user_session->find('first', array(
            'conditions' => array('UserSession.user_id' => $user_id, 'UserSession.id' => $session_id)
        ));
        if(empty($result)){
            return false;
        }else{
            return true;
        }
    }
}