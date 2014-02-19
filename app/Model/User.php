<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sonnt4
 * Date: 2/19/14
 * Time: 11:09 AM
 * To change this template use File | Settings | File Templates.
 */

App::uses('AppModel', 'Model');

class User extends AppModel{
    public $name = 'User';
    public $validate = array('email' => 'notEmpty', 'password' => 'notEmpty');

}