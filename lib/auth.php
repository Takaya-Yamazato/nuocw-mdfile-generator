<?php

session_start();

$check = "check" . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['SERVER_NAME'];
if (
  !isset($_SESSION['check']) || $_SESSION['check'] !== sha1($check) /* Check Validation Constant */
    || (
      !strpos($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']) /* Check Access From *THIS* Server (Preventing CSRF), PROBABLY */
        && !$_SESSION['auth_rd'] /* ? */
        && (time() - $_SESSION['access']) > 300 /* Check Expired Session */
      )
  ) {
    if ($_SERVER['SCRIPT_NAME'] !== LOGIN_SCRIPT_WEB_PATH) {
        header('Location: '.PROTOCOL.'://' . $_SERVER['SERVER_NAME'] . LOGIN_SCRIPT_WEB_PATH);
        exit();
    }
}

$_SESSION['auth_rd'] = false;
$_SESSION['access'] = time();
