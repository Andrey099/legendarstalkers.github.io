<?php

class Weapons
{

  function __construct()
  {
  
  }

  public function getWeapon ($id)
  {
    global $go;

    $stmt = $go -> prepare('SELECT * FROM `weapons` WHERE `id` = ?');
    $stmt -> execute([$id]);
    $weapon = $stmt -> fetch();

    if (!isset($weapon['id'])) return false;
    else
    {
      $out = [
        'id' => $weapon['id'], // ID
        'name' => $weapon['name'], // Название
        'about' => $weapon['about'], // Описание
        'slot' => $weapon['slot'], // Слот
        'quality' => $weapon['quality'], // Качество
        'how' => $weapon['how'], // Как можно получить
        'price' => $weapon['price'], // Цена в магазине, если how = shop,
        'amount' => $weapon['amount'],
        'lvl' => $weapon['lvl'] // С какого лвл можно одеть вещь (для баланса)
      ];

      $stmt = $go -> prepare('SELECT `id` FROM `weapons_stats` WHERE `id_weapon` = ?');
      $stmt -> execute([$id]);
      $stats['count'] = $stmt -> rowCount();

      if ($stats['count'] > 0)
      {
        $stmt = $go -> prepare('SELECT * FROM `weapons_stats` WHERE `id_weapon` = ?');
        $stmt -> execute([$id]);
        $stats['query'] = $stmt -> fetchAll();

        $i = 0;
        foreach ($stats['query'] as $wp)
        {
          $i++;
          $out['stats'][$i] = ['atrb' => $wp['atrb'], 'bonus' => $wp['bonus']];
        }
      }
      return $out;
    }
  }

  public function getEquip ($id)
  {
    global $go;

    $stmt = $go -> prepare('SELECT `wu`.`id_weapon`, `w`.`slot`, `w`.`id` FROM `weapons_users` AS `wu` JOIN `weapons` AS `w` ON (`wu`.`id_weapon` = `w`.`id`) WHERE `wu`.`id_user` = ? and `wu`.`used` = ?');
    $stmt -> execute([$id, '1']);
    $used = $stmt -> fetchAll();
    $count = $stmt -> rowCount();

    if ($count == 0) return 0;
    else
    {
      foreach ($used as $object)
      {
        $slot[] = $object['id_weapon'];
      }
      return $slot;
    }
  }

  public function ladder ($id)
  {
    global $go;
    if (empty($id)) return 0;
    else
    {
      $stmt = $go -> prepare('SELECT `id`, `power`, `dash`, `defense` FROM `users` WHERE `id` = ?');
      $stmt -> execute([$id]);
      $ladder = $stmt -> fetch();

      $array = [
        'id' => $id,
        'power' => $ladder['power'],
        'dash' => $ladder['dash'],
        'defense' => $ladder['defense']
      ];
      return $array;
    }
  }

  public function getAtrb ($id, $type)
  {
    global $go;

    $stmt = $go -> prepare('SELECT `id`, `power`, `dash`, `defense` FROM `users` WHERE `id` = ?');
    $stmt -> execute([$id]);
    $fetch = $stmt -> fetch();

    $t = ['boot' => 25, 'hand' => 20, 'head' => 40, 'knife' => 95, 'pistol' => 150, 'gun' => 330, 'power' => $fetch['power'], 'dash' => $fetch['dash'], 'defense' => $fetch['defense']];
    $damage = $t[$type]; // Стандартный урон

    if (array_key_exists($type, $t) == FALSE) return 0;
    else
    {
      // Подгружаем вещи игрока
      $stmt = $go -> prepare('SELECT `weapons_stats`.`bonus` FROM `weapons_users` JOIN `weapons_stats` ON (`weapons_users`.`id_weapon` = `weapons_stats`.`id_weapon`) WHERE `weapons_users`.`id_user` = ? and `weapons_users`.`used` = ? and `weapons_stats`.`atrb` = ?');
      $stmt -> execute([$id, '1', $type]);
      $count = $stmt -> rowCount();
      $wp = $stmt -> fetchAll();

      if ($count > 0)
      {
        foreach ($wp as $weapon)
        {
          $damage += $weapon['bonus'];
        }
      }
      if ($type == 'dash' and $damage > 100) $damage = 100;
      return $damage;
    }
  }

  public function getLadderDamage ($id, $enemy)
  {
    // Рассчет урона.
    $power = $this -> getAtrb($id, 'power');
    $defense = $this -> getAtrb($enemy, 'defense');
    $random = mt_rand(85, 100)/100;

    $step[1] = (2 + 10) / 250;
    $step[2] = $power / $defense;

    $damage['normal'] = floor(($step[1] * $step[2] * ($power * 2) + 2) * 1 * $random);
    $damage['crit'] = floor(($step[1] * $step[2] * ($power * 2) + 2) * 1.5 * $random);
    $damage['min'] = floor(($step[1] * $step[2] * ($power * 2) + 2) * 1 * 0.85);
    $damage['max'] = floor(($step[1] * $step[2] * ($power * 2) + 2) * 1.5 * 1);
    return $damage;
  }

  /**
  * Экипировка игрока
  * @var numeric $id ID предмета
  * @var numiric $user ID пользователя
  */
  public function equipWeapons ($id, $user)
  {
    global $go;

    $stmt = $go -> prepare('SELECT `wu`.`id`, `wu`.`used`, `w`.`slot`, `wu`.`id_weapon`, `wu`.`id_user`, `w`.`lvl`, `u`.`level` FROM `weapons_users` AS `wu` JOIN `weapons` AS `w` ON (`wu`.`id_weapon` = `w`.`id`) JOIN `users` AS `u` ON (`wu`.`id_user` = `u`.`id`) WHERE `wu`.`id` = ? and `wu`.`id_user` = ?');
    $stmt -> execute([$id, $user]);
    $gun = $stmt -> fetch();

    if ($gun == FALSE) $re['error'] = 'У вас нет такого предмета в инвентаре.';
    elseif ($gun['lvl'] > $gun['level']) $re['error'] = 'Минимальный уровень вещи выше вашего уровня.';
    else
    {
      if ($gun['used'] == 1) // Если предмет одет.
      {
        $stmt = $go -> prepare('SELECT `wu`.`id` FROM `weapons_users` AS `wu` JOIN `weapons` AS `w` ON (`wu`.`id_weapon` = `w`.`id`) WHERE `wu`.`id_user` = ? and `wu`.`used` = ? and `w`.`slot` = ? and `wu`.`id` != ?');
        $stmt -> execute([$user, '1', $gun['slot'], $gun['id']]);
        $count = $stmt -> rowCount();
        $used = $stmt -> fetchAll();

        if ($count > 0)
        {
          foreach ($used as $clear)
          {
            $stmt = $go -> prepare('SELECT `ws`.`atrb`, `ws`.`bonus`, `wu`.`id_user` FROM `weapons_users` AS `wu` JOIN `weapons_stats` AS `ws` ON (`wu`.`id_weapon` = `ws`.`id_weapon`) WHERE `wu`.`id` = ? and (`ws`.`atrb` = ? or `ws`.`atrb` = ?)');
            $stmt -> execute([$clear['id'], 'hp', 'energy']);
            $atrb = $stmt -> fetchAll();
            if ($atrb != FALSE)
            {
              foreach ($atrb as $bn)
              {
                if ($bn['atrb'] == 'energy')
                {
                  $stmt = $go -> prepare('UPDATE `users` SET `max_energy` = `max_energy` - ? WHERE `id` = ?');
                  $stmt -> execute([$bn['bonus'], $bn['id_user']]);
                }
                elseif ($bn['atrb'] == 'hp')
                {
                  $stmt = $go -> prepare('UPDATE `users` SET `max_hp` = `max_hp` - ? WHERE `id` = ?');
                  $stmt -> execute([$bn['bonus'], $bn['id_user']]);
                }
              }
            }
            $stmt = $go -> prepare('UPDATE `weapons_users` SET `used` = ? WHERE `id` = ?');
            $stmt -> execute(['0', $clear['id']]);
            $re['success'] = 'Предмет успешно снят с персонажа.';
          }
        }
        elseif ($count == 0)
        {
          $stmt = $go -> prepare('SELECT `ws`.`atrb`, `ws`.`bonus`, `wu`.`id_user` FROM `weapons_users` AS `wu` JOIN `weapons_stats` AS `ws` ON (`wu`.`id_weapon` = `ws`.`id_weapon`) WHERE `wu`.`id` = ? and (`ws`.`atrb` = ? or `ws`.`atrb` = ?)');
          $stmt -> execute([$gun['id'], 'hp', 'energy']);
          $atrb = $stmt -> fetchAll();
          if ($atrb != FALSE)
          {
            foreach ($atrb as $bn)
            {
              if ($bn['atrb'] == 'energy')
              {
                $stmt = $go -> prepare('UPDATE `users` SET `max_energy` = `max_energy` - ? WHERE `id` = ?');
                $stmt -> execute([$bn['bonus'], $bn['id_user']]);
              }
              elseif ($bn['atrb'] == 'hp')
              {
                $stmt = $go -> prepare('UPDATE `users` SET `max_hp` = `max_hp` - ? WHERE `id` = ?');
                $stmt -> execute([$bn['bonus'], $bn['id_user']]);
              }
            }
          }
          $stmt = $go -> prepare('UPDATE `weapons_users` SET `used` = ? WHERE `id` = ?');
          $stmt -> execute(['0', $gun['id']]);
          $re['success'] = 'Предмет успешно снят с персонажа.';
        }
        else $re['error'] = 'Код ошибки: #2, сообщите это администратору.';
      }
      elseif ($gun['used'] == 0) // Если предмет не одет
      {
        $stmt = $go -> prepare('SELECT `wu`.`id` FROM `weapons_users` AS `wu` JOIN `weapons` AS `w` ON (`wu`.`id_weapon` = `w`.`id`) WHERE `wu`.`id_user` = ? and `wu`.`used` = ? and `w`.`slot` = ?');
        $stmt -> execute([$user, '1', $gun['slot']]);
        $count = $stmt -> rowCount();
        $used = $stmt -> fetchAll();

        if ($count > 0)
        {
          foreach ($used as $clear)
          {
            $stmt = $go -> prepare('SELECT `ws`.`atrb`, `ws`.`bonus`, `wu`.`id_user` FROM `weapons_users` AS `wu` JOIN `weapons_stats` AS `ws` ON (`wu`.`id_weapon` = `ws`.`id_weapon`) WHERE `wu`.`id` = ? and (`ws`.`atrb` = ? or `ws`.`atrb` = ?)');
            $stmt -> execute([$clear['id'], 'hp', 'energy']);
            $atrb = $stmt -> fetchAll();
            if ($atrb != FALSE)
            {
              foreach ($atrb as $bn)
              {
                if ($bn['atrb'] == 'energy')
                {
                  $stmt = $go -> prepare('UPDATE `users` SET `max_energy` = `max_energy` + ? WHERE `id` = ?');
                  $stmt -> execute([$bn['bonus'], $bn['id_user']]);
                }
                elseif ($bn['atrb'] == 'hp')
                {
                  $stmt = $go -> prepare('UPDATE `users` SET `max_hp` = `max_hp` + ? WHERE `id` = ?');
                  $stmt -> execute([$bn['bonus'], $bn['id_user']]);
                }
              }
            }
            $stmt = $go -> prepare('UPDATE `weapons_users` SET `used` = ? WHERE `id` = ?');
            $stmt -> execute(['0', $clear['id']]);
            $re['success'] = 'Предмет успешно одет на персонажа.';
          }
          $stmt = $go -> prepare('UPDATE `weapons_users` SET `used` = ? WHERE `id` = ?');
          $stmt -> execute(['1', $gun['id']]);
        }
        elseif ($count == 0)
        {
          $stmt = $go -> prepare('SELECT `ws`.`atrb`, `ws`.`bonus`, `wu`.`id_user` FROM `weapons_users` AS `wu` JOIN `weapons_stats` AS `ws` ON (`wu`.`id_weapon` = `ws`.`id_weapon`) WHERE `wu`.`id` = ? and (`ws`.`atrb` = ? or `ws`.`atrb` = ?)');
          $stmt -> execute([$gun['id'], 'hp', 'energy']);
          $atrb = $stmt -> fetchAll();
          if ($atrb != FALSE)
          {
            foreach ($atrb as $bn)
            {
              if ($bn['atrb'] == 'energy')
              {
                $stmt = $go -> prepare('UPDATE `users` SET `max_energy` = `max_energy` + ? WHERE `id` = ?');
                $stmt -> execute([$bn['bonus'], $bn['id_user']]);
              }
              elseif ($bn['atrb'] == 'hp')
              {
                $stmt = $go -> prepare('UPDATE `users` SET `max_hp` = `max_hp` + ? WHERE `id` = ?');
                $stmt -> execute([$bn['bonus'], $bn['id_user']]);
              }
            }
          }
          $stmt = $go -> prepare('UPDATE `weapons_users` SET `used` = ? WHERE `id` = ?');
          $stmt -> execute(['1', $gun['id']]);
          $re['success'] = 'Предмет успешно одет на персонажа.';
        }
        else $re['error'] = 'Код ошибки: #3, сообщите это администратору.';
      }
      else $re['error'] = 'Код ошибки: #1, сообщите это администратору.';
    }

    return $re;
  }
}

?>