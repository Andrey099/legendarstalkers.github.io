<?php
include '../../data/base.php';

$stmt = $go -> prepare('UPDATE `users` SET `hp` = `hp` + ROUND(`max_hp` / 5) WHERE `hp` < `max_hp`');
$stmt -> execute([]);
?>