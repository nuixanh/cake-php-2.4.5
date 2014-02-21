<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sonnt4
 * Date: 2/21/14
 * Time: 2:38 PM
 * To change this template use File | Settings | File Templates.
 */
include_once "../Common/CommonUtil.php";

$url = 'http://username:password@hostname:8080/path?arg=value&b=1#anchor';

echo CommonUtil::toValidURL($url) . "\n";
//print_r(parse_url($url));

$url = 'thanhnien.com.vn';
echo CommonUtil::toValidURL($url) . "\n";
//$url = 'http://thanhnien.com.vn';
//echo CommonUtil::toValidURL($url);

//$url = 'http://example.com/redirect?url=http://planio.com';
$url = 'http://example.com/redirect?url=http%3A%2F%2Fplanio.com' . "\n";
echo CommonUtil::toValidURL($url);