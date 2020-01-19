<?php
/**
 * Created by PhpStorm.
 * User: TOIDA Yuto
 * Date: 2015/04/24
 * Time: 18:34
 */

    require_once(__DIR__.'/init_db.php');
    require_once(__DIR__.'/../vendor/autoload.php');

    phpCAS::client(CAS_VERSION_2_0, 'auth.nagoya-u.ac.jp', 443, '/cas/');
    phpCAS::setCasServerCACert(__DIR__.'/cacert.pem');

    if (isset($_REQUEST['logout'])) {
        session_destroy();
        if (phpCAS::isAuthenticated()) {
            phpCAS::logout();
        }
        exit(0);
    }

    phpCAS::forceAuthentication();

    // CAS ATTRIBUTES
    $nu_id = phpCAS::getAttribute('NagoyaUnivID');

    $prepared = $db->prepare('SELECT * FROM user_info WHERE nuid = ? AND validation = TRUE');
    $result = $db->execute($prepared, array($nu_id));
    if (DB::isError($result)) {
        echo($nu_id);
        exit(1);
    }

    $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
    if (!is_null($row) && !DB::isError($row)) {
        session_regenerate_id();
        setcookie(session_name(), session_id(), ini_get('session.cookie_lifetime'), '/');
        $_SESSION['userid'] = $row['id'];
        $_SESSION['username'] = $row['name'];
        $check = 'check' . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['SERVER_NAME'];
        $_SESSION['check'] = sha1($check);
        $_SESSION['start'] = time();
        $_SESSION['refresh'] = time();
        $_SESSION['access'] = time();
        $url = URL_BASE;
        $_SESSION['auth_rd'] = true;
        $_SESSION['notice'] = array();
        $_SESSION['error'] = array();

        header('Location: ' . $url);
        exit(0);
    }
