<?php
/**
 * Created by JetBrains PhpStorm.
 * User: sonnt4
 * Date: 3/3/14
 * Time: 11:02 AM
 * To change this template use File | Settings | File Templates.
 */
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "../Common/WindowsPhonePushNotification.php";
//$uri="http://db3.notify.live.net/throttledthirdparty/01.00/AQEd6spHMvCXSo-xmYbMQ61WAgAAAAADAQAAAAQUZm52OkJCMjg1QTg1QkZDMkUxREQFBkVVTk8wMQ"; //uri sended by Microsoft plateform
$uri="http://am3.notify.live.net/throttledthirdparty/01.00/AQEOWC89PDNtTo7i4Yf5OuHAAgAAAAADxg4DAAQUZm52OkJCMjg1QTg1QkZDMkUxREQFBkxFR0FDWQ"; //uri sended by Microsoft plateform
$notif = new WindowsPhonePushNotification($uri);
$notif->push_toast("this is a title","this is the sub title");