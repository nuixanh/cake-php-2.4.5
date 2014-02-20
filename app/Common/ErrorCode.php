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
    const INVALID_SESSION = 2;
    const EXISTED_EMAIL = 100;
    const INVALID_EMAIL = 101;
    const INVALID_PASSWORD = 102;
    const NO_EXISTED_SITE = 103;
    static public $__names = array(
        -99 => 'EXCEPTION',
        -1 => 'FAILURE',
        0 => 'SUCCESS',
        1 => 'MISS_PARAM',
        2 => 'INVALID_SESSION',
        100 => 'EXISTED_EMAIL',
        101 => 'INVALID_EMAIL',
        102 => 'INVALID_PASSWORD',
        103 => 'NO_EXISTED_SITE',
    );
}