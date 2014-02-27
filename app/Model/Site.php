<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sonnt4
 * Date: 2/19/14
 * Time: 11:09 AM
 * To change this template use File | Settings | File Templates.
 */

App::uses('AppModel', 'Model');

class Site extends AppModel{
    public $name = 'Site';
    public $validate = array('name' => 'notEmpty', 'url' => 'notEmpty');
//    public $hasMany = array(
//        'Hit' => array(
//            'className' => 'Hit',
//        )
//    );
}