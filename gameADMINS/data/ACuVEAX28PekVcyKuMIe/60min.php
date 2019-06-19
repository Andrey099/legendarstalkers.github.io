<?php
include '../../data/base.php';

$plus['boot'] = 1;
$plus['hand'] = 1;
$plus['head'] = 1;

$stmt = $go -> prepare('UPDATE `users` SET `boot` = `boot` + ? WHERE `boot` < ?');
$stmt -> execute([$plus['boot'], 3]);

$stmt = $go -> prepare('UPDATE `users` SET `hand` = `hand` + ? WHERE `hand` < ?');
$stmt -> execute([$plus['hand'], 2]);

$stmt = $go -> prepare('UPDATE `users` SET `head` = `head` + ? WHERE `head` < ?');
$stmt -> execute([$plus['head'], 1]);
?>