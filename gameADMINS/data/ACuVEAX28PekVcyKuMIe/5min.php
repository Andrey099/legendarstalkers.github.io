<?php
include '../../data/base.php';

$plus['energy'] = 2;
$stmt = $go -> prepare('UPDATE `users` SET `energy` = `energy` + ? WHERE `energy` < `max_energy`');
$stmt -> execute([$plus['energy']]);
?>