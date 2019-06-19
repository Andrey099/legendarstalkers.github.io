<?php

class Groups
{
  function __construct()
  {
  
  }

  public function infoGroups ($id)
  {
    global $go;

    $stmt = $go -> prepare('SELECT *, (SELECT COUNT(*) FROM `groups_users` WHERE `id_group` = ? and `accept` = ?) AS `total` FROM `groups` WHERE `id` = ? LIMIT 1');
    $stmt -> execute([$id, '1', $id]);
    $group = $stmt -> fetch();

    if ($group == FALSE) return 0;
    else
    {
      $return = [
        'group' => [
          'id' => $group['id_group'],
          'id_lider' => $group['id_lider'],
          'name' => $group['name'],
          'about' => $group['about'],
          'exp' => $group['exp'],
          'level' => $group['level'],
          'dateCreate' => $group['dateCreate'],
          'bolts' => $group['bolts'],
          'rubles' => $group['rubles'],
          'max_users' => $group['max_users'],
          'total' => $group['total']
        ],
        'build' => [
          'fire' => $group['fire'],
          'barracks' => $group['barracks']
        ]
      ];
      return $return;
    }
  }

  public function inGroups ($id)
  {
    global $go;

    $stmt = $go -> prepare('SELECT 
      `gu`.`id`, `gu`.`id_group`, `gu`.`id_user`, `gu`.`rank`, `gu`.`exp_today`, `gu`.`exp_all`, `gu`.`accept`, `gu`.`dateAdd`, `gu`.`donate_bolts`, `gu`.`donate_rubles`,
      `g`.`id_lider`, `g`.`name`, `g`.`about`, `g`.`exp`, `g`.`level`, `g`.`dateCreate`, `g`.`fire`, `g`.`bolts`, `g`.`rubles` 
      FROM `groups_users` AS `gu` JOIN `groups` AS `g` ON (`g`.`id` = `gu`.`id_group`) WHERE `gu`.`id_user` = ? and `gu`.`accept` = ? LIMIT 1');
    $stmt -> execute([$id, 1]);
    $group = $stmt -> fetch();

    if ($group == FALSE) return 0;
    else
    {
      $return = [
        'group' => [
          'id' => $group['id_group'],
          'id_lider' => $group['id_lider'],
          'name' => $group['name'],
          'about' => $group['about'],
          'exp' => $group['exp'],
          'level' => $group['level'],
          'dateCreate' => $group['dateCreate'],
          'bolts' => $group['bolts'],
          'rubles' => $group['rubles'],
          'max_users' => $group['max_users']
        ],
        'user' => [
          'id' => $group['id'],
          'id_group' => $group['id_group'],
          'id_user' => $group['id_user'],
          'rank' => $group['rank'],
          'exp_today' => $group['exp_today'],
          'exp_all' => $group['exp_all'],
          'accept' => $group['accept'],
          'dateAdd' => $group['dateAdd'],
          'donate_bolts' => $group['donate_bolts'],
          'donate_rubles' => $group['donate_rubles']
        ],
        'build' => [
          'fire' => $group['fire'],
          'barracks' => $group['barracks']
        ]
      ];
      return $return;
    }
  }

  public function usersGroups ($id)
  {
    global $go;

    $stmt = $go -> prepare('SELECT `id` FROM `groups_users` WHERE `id_group` = ? and `accept` = ?');
    $stmt -> execute([$id, 1]);
    $count = $stmt -> rowCount();

    return $count;
  }

  public function show_group ($id, $type='link')
  {
    global $go;

    $stmt = $go -> prepare('SELECT `id`, `name` FROM `groups` WHERE `id` = ? LIMIT 1');
    $stmt -> execute([$id]);
    $group = $stmt -> fetch();

    if ($group == FALSE) $re = '<span style="color: #ffa200;">[Неизвестно]</span>';
    elseif ($type == 'text') $re = '«'.$group['name'].'»';
    else $re = '<a href="/groups/'.$group['id'].'">«'.$group['name'].'»</a>';

    return $re;
  }

  /**
  * Даем экспу в группировку
  * @var numeric $id ID игрока
  * @var numeric $exp Сколько exp давать
  * @return numeric 0 = not ok, больше 0 - сколько уйдет в клан
  */
  public function expGive ($id, $exp)
  {
    $exp = round(abs(intval($exp / 10)));
    if ($exp >= 1)
    {
      global $go;

      $stmt = $go -> prepare('SELECT `id`, `id_group` FROM `groups_users` WHERE `id_user` = ? and `accept` = ?');
      $stmt -> execute([$id, '1']);
      $group = $stmt -> fetch();

      if ($group != FALSE)
      {
        $stmt = $go -> prepare('UPDATE `groups_users` SET `exp_today` = `exp_today` + ?, `exp_all` = `exp_all` + ? WHERE `id` = ?');
        $stmt -> execute([$exp, $exp, $group['id']]);
        $stmt = $go -> prepare('UPDATE `groups` SET `exp` = `exp` + ? WHERE `id` = ?');
        $stmt -> execute([$exp, $group['id_group']]);
        return $exp;
      }
      else return 0;
    }
    else return 0;
  }
}

?>