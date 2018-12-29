<?php

/**
 * Сервис, обрабатывающий задачи из очереди.
 */

include_once '../db.php';

if ($argc < 2) {
    die('Необходимы парамтеры запуска.');
}
for ($i = 2; $i < $argc; $i++) {
    if (!is_numeric($argv[$i])) {
        die('Ошибка: Входные параметры не числа.');
    }
}
$qids = implode(',', array_slice($argv, 1));
$sql = 'select * from queue where id in ('.$qids.')';
$result = $db->execute($sql);

$tasks = [];
if ($result) {
    while (!$result->EOF) {
        $tasks[] = $result->fields;
        $result->MoveNext();
    }
}
foreach ($tasks as $task) {
    $result = $db->execute('update queue set status = 1 where id = ?', [$task['id']]);
    if (!$result) {
        die('Все плохо!');
    }
}