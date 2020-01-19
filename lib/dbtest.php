<?php

require_once('ocw_init.php');

$sql = "select * from instructors";

$res = $db->getAll($sql);

print_r($res);
