<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sonnt4
 * Date: 2/19/14
 * Time: 1:59 PM
 * To change this template use File | Settings | File Templates.
 */

final class ErrorCode {
    const EXCEPTION = -99;
    const FAILURE = -1;
    const SUCCESS = 0;
    const MISS_PARAM = 1;
    const EXISTED_EMAIL = 100;
    const INVALID_EMAIL = 101;
    const INVALID_PASSWORD = 102;
    static public $__names = array(
        -99 => 'EXCEPTION',
        -1 => 'FAILURE',
        0 => 'SUCCESS',
        1 => 'MISS_PARAM',
        100 => 'EXISTED_EMAIL',
        101 => 'INVALID_EMAIL',
        102 => 'INVALID_PASSWORD',
    );
}