<?php
include '../../data/base.php';

// Обновляем ладдер
$stmt = $go -> prepare('TRUNCATE `ladder_fights`');
$stmt -> execute([]);
// Обнуляем экспу за день в группировках
$stmt = $go -> prepare('UPDATE `groups_users` SET `exp_today` = ? WHERE `exp_today` > ?');
$stmt -> execute([0, 0]);
?>