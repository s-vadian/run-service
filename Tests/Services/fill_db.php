<?php

include_once './db.php';

$db->StartTrans();

//for ($i = 1; $i < 10000; $i++) {
//    $result = $db->execute('insert into queue (id, status, name, id_service) values (?,?,?,?);', [$i, 0, 'Task'. $i, 1]);
//    if (!$result) {
//        $db->RollbackTrans();
//        die('Все плохо!');
//    }
//}

$result = $db->execute('update queue set status = ?;', [0]);
if (!$result) {
    $db->RollbackTrans();
    die('Все плохо!');
}

$db->CompleteTrans();
print_r('Скрипт выполнен успешно' . PHP_EOL);
