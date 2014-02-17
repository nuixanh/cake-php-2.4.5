<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sonnt4
 * Date: 2/17/14
 * Time: 9:12 AM
 * To change this template use File | Settings | File Templates.
 */

class RestUserController extends AppController{

    public $components = array('RequestHandler');

    public function signUp() {
        $oData = new stdClass();;
        $oData->sessionId = 'abc';
        $oData->email=$this->request->data('email');
//        debug($this->request);
        $this->set(array(
            'success' => true,
            'data' => $oData,
//            'email' => $this->request->data('email'),
//            'requestData' => $this->request,
//            'requestParams' => $this->request->data('email'),
            '_serialize' => array('success','data'
//                ,'requestData','requestParams'
//            ,'para'
            )
        ));
    }
}