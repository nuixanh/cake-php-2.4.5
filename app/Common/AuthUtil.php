<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sonnt4
 * Date: 2/20/14
 * Time: 5:35 PM
 * To change this template use File | Settings | File Templates.
 */

class AuthUtil {
    public static function isValidSession($user_id, $session_id) {
        $user = new User();
        $result = $user->find('first', array(
            'conditions' => array('User.id' => $user_id, 'User.last_session' => $session_id)
        ));
        if(empty($result)){
            return false;
        }else{
            return true;
        }
    }
}