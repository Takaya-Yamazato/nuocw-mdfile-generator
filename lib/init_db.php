<?php

// echo DSN;
try{ 
    global $db;

    $db = new PDO(DSN);
    $db->setAttribute(PDO::ATTR_PERSISTENT, true);

    } catch (PDOException $e) { 

    die("接続失敗{$e->getMessage()}"); 
}
// echo LOG_DSN;
try{
    global $log_db ; 
    $log_db = new PDO(LOG_DSN);

    } catch (PDOException $e) { 

    die("接続失敗{$e->getMessage()}"); 
}
// $result = $db->fetch(PDO::FETCH_ASSOC);
// print_r($result);
// print("\n");

// $db = DB::connect(DSN);
// if (DB::isError($db)) {
//     require_once LIBDIR . '/class/OUTPUT.class.php';

//     $message = "データベースへの接続に失敗しました。";
//     OUTPUT::printErrorMessage($message);

//     exit;
// }
// $db->setFetchMode(DB_FETCHMODE_ASSOC);

