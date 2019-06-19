<?php

class Objects
{
  function __construct()
  {
  
  }

  /**
  * @var integer $user id user
  * @var integer $object id object
  * @return integer count
  */
  public function getCountObject($user, $object)
  {
    global $go;

    $stmt = $go -> prepare('SELECT `id`, `count` FROM `objects_users` WHERE `id_object` = ? and `id_user` = ?');
    $stmt -> execute([$object, $user]);
    $fetch = $stmt -> fetch();

    if (!isset($fetch['id'])) return 0;
    else return $fetch['count'];
  }

  public function getObject($id)
  {
    global $go;
    $stmt = $go -> prepare('SELECT * FROM `objects` WHERE `id` = ?');
    $stmt -> execute([$id]);
    $object = $stmt -> fetch();

    if (!isset($object['id'])) return 0;
    else
    {
      $return = [
        'id' => $object['id'], // ID
        'name' => $object['name'], // Название
        'about' => $object['about'], // Описание
        'types' => $object['types'], // Что это (ключ, аптека, энергетик)
        'what' => $object['what'] // сколько дает
      ];
      return $return;
    }
  }

  /**
  * @var integer $user id user
  * @var integer $object id object
  * @var integer $count count give object
  * @return bool
  */
  public function giveObject($user, $object, $count)
  {
    global $go;

    $stmt = $go -> prepare('SELECT `id` FROM `objects_users` WHERE `id_object` = ? and `id_user` = ?');
    $stmt -> execute([$object, $user]);
    $fetch = $stmt -> fetch();

    if (!isset($fetch['id']))
    {
      $stmt = $go -> prepare('INSERT INTO `objects_users` (`id_object`, `id_user`, `count`, `dateAdd`) VALUES (?, ?, ?, ?)');
      $stmt -> execute([$object, $user, $count, time()]);
      return true;
    }
    else
    {
      $stmt = $go -> prepare('UPDATE `objects_users` SET `count` = `count` + ? WHERE `id` = ?');
      $stmt -> execute([$count, $fetch['id']]);
      return true;
    }
  }

  /**
  * @var integer $user id user
  * @var integer $object id object
  * @var integer $count count take object
  * @return bool
  */
  public function takeObject($user, $object, $count)
  {
    global $go;

    $stmt = $go -> prepare('SELECT `id`, `count` FROM `objects_users` WHERE `id_object` = ? and `id_user` = ?');
    $stmt -> execute([$object, $user]);
    $fetch = $stmt -> fetch();

    if (!isset($fetch['id'])) return false;
    else
    {
      if ($fetch['count'] == $count)
      {
        $stmt = $go -> prepare('DELETE FROM `objects_users` WHERE `id` = ?');
        $stmt -> execute([$fetch['id']]);
      }
      else
      {
        $stmt = $go -> prepare('UPDATE `objects_users` SET `count` = `count` - ?, `dateAdd` = ? WHERE `id` = ?');
        $stmt -> execute([$count, time(), $fetch['id']]);
      }
      return true;
    }
  }

  public function show_wall ($id)
  {
    global $go;
    $stmt = $go -> prepare('SELECT `id`, `name` FROM `background` WHERE `id` = ? LIMIT 1');
    $stmt -> execute([$id]);
    $wall = $stmt -> fetch();

    if (!isset($wall['id'])) $re = '<span style="color: #ffa200;">[неизвестно]</span>';
    else $re = '<a style="color: #ffa200;" href="/info/house/'.$wall['id'].'">'.$wall['name'].'</a>';

    return $re;
  }

  public function show_medal ($id)
  {
    global $go;
    $stmt = $go -> prepare('SELECT `id`, `name` FROM `objects` WHERE `id` = ? and `types` = ? LIMIT 1');
    $stmt -> execute([$id, 'key']);
    $medal = $stmt -> fetch();

    if (!isset($medal['id'])) $re = '<span style="color: #ffa200;">[неизвестно]</span>';
    else $re = '<img src="/imgs/medal.png" width="12px" /> <a style="color: #ffa200;" href="/info/objects/'.$medal['id'].'">'.$medal['name'].'</a>';

    return $re;
  }

  public function show_ammo ($id)
  {
    global $go;
    $stmt = $go -> prepare('SELECT `id`, `name`, `quality` FROM `weapons` WHERE `id` = ? LIMIT 1');
    $stmt -> execute([$id]);
    $ammo = $stmt -> fetch();

    if (!isset($ammo['id'])) $re = '<span style="color: #ffa200;">[неизвестно]</span>';
    else $re = '<img src="/imgs/ammo.png" width="12px" /> <a style="color: #ffa200;" href="/info/items/'.$ammo['id'].'">'.$ammo['name'].'</a>';

    return $re;
  }

  public function weapon ($id)
  {
    global $go;
    $stmt = $go -> prepare('SELECT `id`, `name` FROM `weapons` WHERE `id` = ? LIMIT 1');
    $stmt -> execute([$id]);
    $weapon = $stmt -> fetch();

    if (!isset($weapon['id'])) $re = '<span style="color: #ffa200;">[неизвестно]</span>';
    else $re = '<a style="color: #ffa200;" href="/info/items/'.$weapon['id'].'">'.$weapon['name'].'</a>';

    return $re;
  }
}

?>