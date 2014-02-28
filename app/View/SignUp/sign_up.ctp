<?php 
/*--------------------------------------------------------------*/ 
/* Millennial Media PHP Ad Coding, v.7.4.20                     */ 
/* Copyright Millennial Media, Inc. 2006                        */ 
/*                                                              */ 
/* The following code requires PHP >= 4.3.0 and                 */ 
/* allow_url_fopen 1 set in php.ini file.                       */ 
/*                                                              */ 
/* NOTE:                                                        */ 
/* It is recommended that you lower the default_socket_timeout  */ 
/* value in the php.ini file to 5 seconds.                      */ 
/* This will prevent network connectivity from affecting        */ 
/* page loading.                                                */ 
/*--------------------------------------------------------------*/ 

/*------- Publisher Specific Section -------*/ 
$mm_placementid = 153511; 
$mm_adserver = "ads.mp.mydas.mobi"; 

/* The default response will be echo'd on the page     */
/* if no Ad is returned, so any valid WML/XHTML string */
/* is acceptable.                                      */
$mm_default_response = "";

/*------------------------------------------*/

/*----------- BEGIN AD INITIALIZATION ----------*/
/*----- PLEASE DO NOT EDIT BELOW THIS LINE -----*/
$mm_id = "NONE";
$mm_ua = "NONE";
@$mm_ip = $_SERVER['REMOTE_ADDR'];

if (isset($_SERVER['HTTP_USER_AGENT'] )){
     $mm_ua = $_SERVER['HTTP_USER_AGENT'];
}

if (isset($_SERVER['HTTP_X_UP_SUBNO'])) {
          $mm_id = $_SERVER['HTTP_X_UP_SUBNO'];
} elseif (isset($_SERVER['HTTP_XID'])) {
          $mm_id = $_SERVER['HTTP_XID'];
} elseif (isset($_SERVER['HTTP_CLIENTID'])) {
          $mm_id = $_SERVER['HTTP_CLIENTID'];
} else {
          $mm_id = $_SERVER['REMOTE_ADDR'];
}

$mm_url = "http://$mm_adserver/getAd.php5?apid=$mm_placementid&auid="
          . urlencode($mm_id) . "&uip=" . urlencode($mm_ip) . "&ua="
          . urlencode($mm_ua);
/*------------ END AD INITIALIZATION -----------*/
?>

<?php
/* Place this code block where you want the ad to appear */
/*------- Reusable Ad Call -------*/
#echo $mm_url;
@$mm_response = file_get_contents($mm_url);
#echo $mm_response != FALSE ? $mm_response : $mm_default_response;
/*--------- End Ad Call ----------*/
?>

<script>
    window.mmAPI.placeAd({
        containerElementId: "adContainer",
        apid: "153511",
        placementType: "inline",
        width: 728,
        height: 90,
        allowLocation: true
    }, function(adFilled) {
        //adFilled will be true if the ad fills, else it will be false
        console.log("was the ad filled: " + adFilled);
        if (adFilled) {
        }
    });
</script>