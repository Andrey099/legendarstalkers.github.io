<?php
include 'data/base.php';
switch ($act)
{
default:
$stmt = $go -> prepare('SELECT * FROM `users` WHERE `id` = ? LIMIT 1');
$stmt -> execute([$ids]);
$profile = $stmt -> fetch();

if (!isset($profile['id'])) die(header('Location: /'));

$title = $profile['login'];
include 'data/head.php';
user();

$g['profile'] = $grp -> inGroups($ids);

$access = [
  '<span class="access-user">Сталкер</span>',
  '<span class="access-mod">Модератор</span>',
  '<span class="access-adm">Администратор</span>',
  '<span class="access-dev">Разработчик</span>'
];

$stmt = $go -> prepare('SELECT `b`.`id`, `b`.`background` FROM `background` AS `b` JOIN `background_users` AS `bu` ON (`b`.`id` = `bu`.`id_background`) WHERE `bu`.`id_user` = ? and `bu`.`used` = ?');
$stmt -> execute([$profile['id'], '1']);
$background = $stmt -> fetch();

?>
<div class="background-block" style="background: url(/files/background/<?php echo (isset($background['id']) ? $background['background']:'default.png');?>) center no-repeat;">
  <div class="background-text">
    <strong><?php echo (time() < ($profile['updDate']+900) ? '<img width="5px" alt="[online]" title="онлайн" src="/imgs/online.png" />':NULL)?> <?php echo $profile['login'];?></strong><br/>
    <small><?php echo $access[$profile['access']];?></small>
  </div>
</div>
<?php
if ($uid != $profile['id'])
{
  if (isset($_GET['invite']) and $g['profile'] == 0 and $g['user']['user']['rank'] > 2)
  {
    $stmt = $go -> prepare('SELECT `id` FROM `groups_users` WHERE `id_user` = ? and `id_group` = ?');
    $stmt -> execute([$profile['id'], $g['user']['group']['id']]);
    $check = $stmt -> rowCount();

    if ($check > 0) show_error('Этот игрок уже приглашен в Вашу группировку.');
    else
    {
      if (isset($_GET['invite']) and isset($_GET['ok']))
      {
        $stmt = $go -> prepare('INSERT INTO `groups_users` (`id_user`, `id_group`, `accept`, `invite`) VALUES (?, ?, ?, ?)');
        $stmt -> execute([$ids, $g['user']['group']['id'], 0, $uid]);
        show_error('Заявка успешно отправлена.');
      }
      else
      {
        ?>
        <div class="fights fights-about center">
          Вы действительно хотите пригласить <?php echo show_user($ids);?> в свою группировку <a href="/groups/<?php echo $g['user']['group']['id']?>">«<?php echo $g['user']['group']['name']?>»</a>?
          <div style="margin: 5px 0 0 0;">
            <div class="grid fights-link">
              <div class="six columns ln">
                <a href="?invite&ok">Пригласить</a>
              </div>
              <div class="six columns">
                <a href="?">Отменить</a>
              </div>
            </div>
          </div>
        </div>
        <?php
      }
    }
  }
  $stmt = $go -> prepare('SELECT * FROM `friends` WHERE (`id_user` = ? or `id_friend` = ?) and (`id_user` = ? or `id_friend` = ?) LIMIT 1');
  $stmt -> execute([$uid, $uid, $profile['id'], $profile['id']]);
  $fetch = $stmt -> fetch();

  if (isset($_GET['friend']))
  {
    if (isset($fetch['id']) and $fetch['request'] == 1) echo show_error('Этот игрок уже у вас в друзьях.');
    else
    {
      if (!isset($fetch['id']))
      {
        $stmt = $go -> prepare('INSERT INTO `friends` (`id_user`, `id_friend`, `time`) VALUES (?, ?, ?)');
        $stmt -> execute([$uid, $profile['id'], time()]);

        $_SESSION['success'] = 'Заявка успешно отправлена.';
        die(header('Location:/id/'.$profile['id']));
      }
      elseif ($fetch['request'] == 0 and $fetch['id_friend'] == $uid)
      {
        $stmt = $go -> prepare('UPDATE `friends` SET `request` = ? WHERE `id` = ?');
        $stmt -> execute(['1', $fetch['id']]);

        $_SESSION['success'] = 'Заявка успешно принята.';
        die(header('Location:/id/'.$profile['id']));
      }
      else echo show_error('Неизвестная ошибка.');
    }
  }
  elseif (isset($_GET['unfriend']))
  {
    if (!isset($fetch['id'])) echo show_error('Этого пользователя нет в вашем списке друзей.');
    else 
    {
      if ($fetch['request'] == 0 and $fetch['id_user'] == $uid)
      {
        $stmt = $go -> prepare('DELETE FROM `friends` WHERE `id` = ?');
        $stmt -> execute([$fetch['id']]);

        $_SESSION['success'] = 'Ваша заявка успешно отменена.';
        die(header('Location:/id/'.$profile['id']));
      }
      elseif ($fetch['request'] == 1)
      {
        $stmt = $go -> prepare('DELETE FROM `friends` WHERE `id` = ?');
        $stmt -> execute([$fetch['id']]);

        $_SESSION['success'] = 'Данный игрок удален из списка ваших друзей.';
        die(header('Location:/id/'.$profile['id']));
      }
      else echo show_error('Неизвестная ошибка.');
    }
  }
}
$stmt = $go -> prepare('SELECT * FROM `users_data` WHERE `id_user` = ?');
$stmt -> execute([$profile['id']]);
$ank = $stmt -> fetch();
?>
<div class="fights">
  <div class="grid">
    <div class="four columns fix">
      <div class="col">
        <?php
        if (!empty($ank['head']) or !empty($ank['eyes']) or !empty($ank['beard']) or !empty($ank['color']) or !empty($ank['hair']))
        {
          echo '<img src="/ava/'.$ank['head'].'/'.$ank['eyes'].'/'.$ank['beard'].'/'.$ank['color'].'/'.$ank['hair'].'/" />';
        }
        else
        {
          echo '<img src="/files/portrait/default.png" />';
        }
        ?>
      </div>
    </div>
    <div class="eight columns fix">
      <div class="col">Информация</div>
      <div class="fights fights-about" style="margin: 2px;text-align: left;">
        Имя: <?php echo ($ank['name'] != NULL ? $ank['name']:'Неизвестно');?><br/>
        Уровень: <?php echo $profile['level']?> (<?php echo $profile['exp']?>/<?php echo $level[$profile['level']+1];?>)<br/>
        Репутация: <?php echo $profile['repute']?> <img src="/imgs/repute.png" width="12px" /><hr/>
        Сила: <?php echo $wpn -> getAtrb($profile['id'], 'power');?><br/>
        Рывок: <?php echo $wpn -> getAtrb($profile['id'], 'dash');?>%<br/>
        Защита: <?php echo $wpn -> getAtrb($profile['id'], 'defense');?><br/>
        Здоровье: <?php echo $u['max_hp'];?>
      </div>
    </div>
  </div>
  <div class="grid">
    <div class="<?php echo ($g['profile'] == 0 ? 'twelve':'six')?> columns">
      <div class="col">Топ рейтинги</div>
      <div class="fights fights-about" style="margin: 2px;">
        <?php
        // Позиция в общем рейтинге
        $stmt = $go -> prepare('SELECT `id` FROM `users` WHERE `repute` > ? and `id` != ? ORDER BY `repute` DESC');
        $stmt -> execute([$profile['repute'], $profile['id']]);
        $place['repute'] = $stmt -> rowCount();
        echo 'По репутации: '.($place['repute']+1).' место <br/>';
        // Позиция в ладдере
        $stmt = $go -> prepare('SELECT `points` FROM `ladder` WHERE `id_user` = ?');
        $stmt -> execute([$profile['id']]);
        $stats['ladder'] = $stmt -> fetch();
        if (isset($stats['ladder']['points']))
        {
          $stmt = $go -> prepare('SELECT `id` FROM `ladder` WHERE `points` > ? and `id_user` != ?');
          $stmt -> execute([$stats['ladder']['points'], $profile['id']]);
          $place['ladder'] = $stmt -> rowCount();
          echo 'В ладдере: '.($place['ladder']+1).' место <br/>';
        }
        ?>
      </div>
    </div>
    <?php if ($g['profile'] != 0): ?>
    <div class="six columns">
      <div class="col">Группировка</div>
      <div class="fights fights-about" style="margin: 2px;">
        <a href="/groups/<?php echo $g['profile']['group']['id']?>">«<?php echo $g['profile']['group']['name']?>»</a><br/>
        Ранг: <?php echo $rank[$g['profile']['user']['rank']]?>
      </div>
    </div>
    <?php endif;?>
  </div>
</div>
<div class="out">
  <div class="grid">
  <?php
  if ($profile['id'] != $uid)
  {
    ?>
    <div class="six columns">
      <div style="margin: 1px;">
        <?php
        if (!isset($fetch['id'])) echo '<div class="fights-link"><a href="?friend">Добавить в друзья</a></div>';
        elseif ($fetch['request'] == 0 and $fetch['id_friend'] == $uid) echo '<div class="fights-link"><a href="?friend">Принять заявку</a></div>';
        elseif ($fetch['request'] == 0 and $fetch['id_user'] == $uid) echo '<div class="fights-link"><a href="?unfriend">Отменить заявку</a></div>';
        else echo '<div class="fights-link"><a href="?unfriend">Удалить из друзей</a></div>';
        ?>
      </div>
    </div>
    <div class="six columns">
      <div style="margin: 1px;">
        <div class="fights-link"><a href="/pda/sms/im/<?php echo $profile['id']?>">Написать сообщение</a></div>
      </div>
    </div>
    <?php
  }
  ?>
    <div class="six columns">
      <div style="margin: 1px;">
        <div class="fights-link"><a href="/id/<?php echo $profile['id'];?>/equip">Экипировка</a></div>
      </div>
    </div>
    <div class="six columns">
      <div style="margin: 1px;">
        <div class="fights-link"><a href="/id/<?php echo $profile['id'];?>/stats">Статистика</a></div>
      </div>
    </div>
  </div>
</div>
<?php if ($g['profile'] == 0 and $g['user'] != 0 and $g['user']['user']['rank'] > 2 and $ids != $uid): ?>
<div class="fights-link center" style="margin: 0 5px"><a href="?invite">Пригласить в группировку</a></div>
<?php endif;?>
<div class="list margin margin-left-right">
  Дополнительная информация <hr/>
  Дата регистрации › <?php echo date('j.m.Y в H:i:s', $profile['addDate']);?><br/>
  Последняя активность › <?php echo date('j.m.Y в H:i', $profile['updDate']);?><br/>
</div>
<?php
if ($profile['id'] == $uid) echo '<div class="fights-link" style="margin: 5px;"><a href="/home/">Сменить жилище</a></div>';
break;

case 'background':
  $title = 'Выбор жилища';
  include 'data/head.php';
  user();

  $pages = new Paginator(10, 'page');
  $stmt = $go -> prepare('SELECT `background`.`id` FROM `background` JOIN `background_users` ON (`background`.`id` = `background_users`.`id_background`) WHERE `background_users`.`id_user` = ?');
  $stmt -> execute([$uid]);
  $total = $stmt -> rowCount();

  $pages -> set_total($total);

  if ($total > 0)
  {
    if (isset($_GET['use']))
    {
      if (empty($_GET['use'])) echo show_error('Выберите новое жилище.');
      elseif (!is_numeric($_GET['use'])) echo show_error('Ошибка в запросе.');
      else
      {
        $stmt = $go -> prepare('SELECT `background`.`id`, `background_users`.`used` FROM `background` JOIN `background_users` ON (`background`.`id` = `background_users`.`id_background`) WHERE `background_users`.`id_user` = ? and `background`.`id` = ?');
        $stmt -> execute([$uid, $_GET['use']]);
        $background = $stmt -> fetch();

        if (!isset($background['id'])) echo show_error('У вас нет этого жилища.');
        elseif ($background['used']) echo show_error('Вы уже в этом жилище.');
        else
        {
          $stmt = $go -> prepare('UPDATE `background_users` SET `used` = ? WHERE `id_user` = ? and `used` = ?');
          $stmt -> execute(['0', $uid, '1']);

          $stmt = $go -> prepare('UPDATE `background_users` SET `used` = ? WHERE `id_user` = ? and `id_background` = ?');
          $stmt -> execute(['1', $uid, $_GET['use']]);

          $_SESSION['success'] = 'Вы успешно сменили жилище.';
          die(header('Location: /home/'));
        }
      }
    }
    $stmt = $go -> prepare('SELECT `background`.`id`, `background`.`name`, `background`.`background`, `background_users`.`used` FROM `background` JOIN `background_users` ON (`background`.`id` = `background_users`.`id_background`) WHERE `background_users`.`id_user` = ? ORDER BY `background_users`.`id` DESC '.$pages -> get_limit());
    $stmt -> execute([$uid]);
    $get = $stmt -> fetchAll();

    foreach ($get as $wall)
    {
      ?>
      <div class="fights fights-about">
        <div class="background-block nmfull" style="background: url(/files/background/<?php echo $wall['background'];?>) center no-repeat;">
          <div class="background-text">
            <strong><?php echo $wall['name'];?></strong><br/>
            <?php echo ($wall['used'] == 1 ? '<span class="access-dev">Текущее жилье</span>':null);?>
          </div>
        </div>
        <?php echo ($wall['used'] == 0 ? '<div class="fights-link"><a href="?use='.$wall['id'].'">Переехать сюда</a></div>':null);?>
      </div>
      <?
    }
    if ($total > 10) echo $pages -> page_links();
  }
  else
  {
    echo show_error('У вас нет другого жилища.');
  }
  echo '<div class="fights-link" style="margin: 5px;"><a href="/id/'.$uid.'">Вернуться назад</a></div>';
break;

case 'equip':
  $stmt = $go -> prepare('SELECT `id`, `login` FROM `users` WHERE `id` = ? LIMIT 1');
  $stmt -> execute([$ids]);
  $profile = $stmt -> fetch();
  if (!isset($profile['id'])) die(header('Location: /'));

  $title = 'Экипировка '.$profile['login'];
  include 'data/head.php';
  user();
  $equip = $wpn -> getEquip($ids);
  if ($equip == 0) show_error('На данном игроке ничего не экипировано.');
  else
  {
    $slot = [
      'boot' => ['name' => 'Ноги', 'damage' => 'Удар с ноги'],
      'hand' => ['name' => 'Тело', 'damage' => 'Удар с руки'],
      'head' => ['name' => 'Голова', 'damage' => 'Удар с головы'],
      'knife' => ['name' => 'Нож', 'damage' => 'Удар с ножа'],
      'pistol' => ['name' => 'Пистолет', 'damage' => 'Выстрел с пистолета'],
      'gun' => ['name' => 'Автомат', 'damage' => 'Выстрел с автомата']
    ];
    foreach ($equip as $eq)
    {
      $inv = $wpn -> getWeapon($eq);
      ?>
      <div class="fights fights-about">
        <table width="100%">
          <tr>
            <td width="44px" valign="top"><img width="44px" src="/files/<?php echo $inv['slot'];?>/<?php echo $inv['id'];?>.png" title="<?php echo $inv['name'];?>" /></td>
            <td valign="top">
              <div class="attack-text">
                <a href="/info/items/<?php echo $inv['id'];?>"><?php echo $inv['name'];?></a><br/>
                <small>
                  <?php echo $slot[$inv['slot']]['name'];?><br/>
                </small>
              </div>
            </td>
          </tr>
        </table>
      </div>
      <?php
    }
  }
  echo '<div class="fights-link" style="margin: 5px;"><a href="/id/'.$ids.'">Вернуться назад</a></div>';
break;

case 'stats':
  $stmt = $go -> prepare('SELECT * FROM `users` WHERE `id` = ? LIMIT 1');
  $stmt -> execute([$ids]);
  $profile = $stmt -> fetch();
  if (!isset($profile['id'])) die(header('Location: /'));

  $title = 'Статистика '.$profile['login'];
  include 'data/head.php';
  user();
  ?>
  <div class="out">
    <div class="grid">
      <div class="six columns">
        <div class="col">Общая</div>
        <div class="fights fights-about" style="margin: 2px;text-align: left;">
          Уровень: <?php echo $profile['level']?> (<?php echo $profile['exp']?>/<?php echo $level[$profile['level']+1];?>)<br/>
          Репутация: <?php echo $profile['repute']?><br/>
          Здоровье: <?php echo $profile['max_hp']?><br/>
        </div>
      </div>
      <div class="six columns">
        <div class="col">Параметры</div>
        <div class="fights fights-about" style="margin: 2px;text-align: left;">
          Сила: <?php echo $wpn -> getAtrb($profile['id'], 'power');?><br/>
          Рывок: <?php echo $wpn -> getAtrb($profile['id'], 'dash');?>%<br/>
          Защита: <?php echo $wpn -> getAtrb($profile['id'], 'defense');?><br/>
        </div>
      </div>
      <?php
      $stmt = $go -> prepare('SELECT * FROM `ladder` WHERE `id_user` = ?');
      $stmt -> execute([$profile['id']]);
      $ladder = $stmt -> fetch();
      if (isset($ladder['id']))
      {
        ?>
        <div class="six columns">
          <div class="col">Ладдер</div>
          <div class="fights fights-about" style="margin: 2px;text-align: left;">
            Очков: <?php echo $ladder['points']?><br/>
            Побед: <?php echo $ladder['win']?><br/>
            Поражений: <?php echo $ladder['lose']?><br/>
          </div>
        </div>
        <?php
      }
      $stmt = $go -> prepare('SELECT `id`,`success_1`, `success_2` FROM `zone` WHERE `id_user` = ?');
      $stmt -> execute([$profile['id']]);
      $stat['zone'] = $stmt -> fetch();
      if (isset($stat['zone']['id']))
      {
        ?>
        <div class="six columns">
          <div class="col">Окрестности</div>
          <div class="fights fights-about" style="margin: 2px;text-align: left;">
            <?php echo $zones[1]['name'];?>: <?php echo declension($stat['zone']['success_1'], ['раз','раза','раз']);?><br/>
            <?php echo $zones[2]['name'];?>: <?php echo declension($stat['zone']['success_2'], ['раз','раза','раз']);?><br/>
          </div>
        </div>
        <?php
      }
      ?>
    </div>
  </div>
  <?php
  echo '<div class="fights-link" style="margin: 5px;"><a href="/id/'.$ids.'">Вернуться назад</a></div>';
break;

case 'objects':
  $title = 'Предметы';
  include 'data/head.php';
  user();
  ?>
  <div style="margin: 2px 5px;">
    <div class="grid fights-link">
      <div class="six columns ln">
        <a href="/inv">Экипировка</a>
      </div>
      <div class="six columns">
        <a href="/inv/objects">Предметы</a>
      </div>
    </div>
  </div>
  <?
  if (isset($_GET['use']))
  {
    if (empty($_GET['use'])) echo show_error('Выберите предмет, который хотите использовать.');
    elseif (!is_numeric($_GET['use'])) echo show_error('Ошибка в запросе.');
    else
    {
      $stmt = $go -> prepare('SELECT `objects_users`.`id`, `objects_users`.`id_object`, `objects_users`.`id_user`, `objects_users`.`count`, `objects`.`types`, `objects`.`name`, `objects`.`what` FROM `objects_users` JOIN `objects` ON (`objects_users`.`id_object` = `objects`.`id`) WHERE `objects_users`.`id` = ? and `objects_users`.`id_user` = ?');
      $stmt -> execute([$_GET['use'], $uid]);
      $object = $stmt -> fetch();
      if (!isset($object['id'])) echo show_error('У вас нет такого предмета.');
      elseif ($object['types'] == 'none' or $object['types'] == 'key') echo show_error('Этот предмет нельзя использовать.');
      else
      {
        if ($object['types'] == 'hp')
        {
          if ($obg -> getCountObject($uid, $object['id_object']) == 0) echo show_error('У вас нет такого предмета.');
          elseif ($u['hp'] == $u['max_hp']) echo show_error('У вас полное здоровье.');
          else
          {
            $obg -> takeObject($uid, $object['id_object'], 1);

            if ($object['id_object'] == 1) $object['what'] = round($u['max_hp']/4);
            $stmt = $go -> prepare('UPDATE `users` SET `hp` = `hp` + ? WHERE `id` = ?');
            $stmt -> execute([$object['what'], $uid]);
            $_SESSION['success'] = '«'.$object['name'].'» успешно использован(а).';
            die(header('Location: /inv/objects'));
          }
        }
        elseif ($object['types'] == 'energy')
        {

        }
      }
    }
  }
  $pages = new Paginator(10, 'page');
  $stmt = $go -> prepare('SELECT `id` FROM `objects_users` WHERE `id_user` = ?');
  $stmt -> execute([$uid]);
  $total = $stmt -> rowCount();

  if ($total > 0)
  {
    $stmt = $go -> prepare('SELECT `objects_users`.`id`,`objects_users`.`id_user`,`objects_users`.`id_object`,`objects_users`.`count`,`objects`.`name`,`objects`.`about`,`objects`.`types` FROM `objects_users` JOIN `objects` ON (`objects_users`.`id_object` = `objects`.`id`) WHERE `objects_users`.`id_user` = ? ORDER BY `objects_users`.`dateAdd` DESC '.$pages -> get_limit());
    $stmt -> execute([$uid]);
    $get = $stmt -> fetchAll();
    foreach ($get as $inv)
    {
      if ($inv['types'] == 'hp' and $inv['id_object'] == 1) $inv['what'] = round($u['max_hp']/4);
      ?>
      <div class="fights fights-about">
        <table width="100%">
          <tr>
            <td width="44px" valign="top"><img src="/files/objects/<?php echo (file_exists($_SERVER['DOCUMENT_ROOT'].'/files/objects/'.$inv['id_object'].'.png') != FALSE ? $inv['id_object']:'default');?>.png" /></td>
            <td valign="top">
              <div class="attack-text">
                <a href="/info/objects/<?php echo $inv['id_object'];?>"><?php echo $inv['name'];?></a> <?php if($inv['types'] == 'hp' or $inv['types'] == 'energy') echo '+'.$inv['what'].' <img src="/imgs/'.$inv['types'].'.png" width="12px" />';?><br/>
                <small>
                  В наличии <?php echo declension($inv['count'], ['штука','штуки','штук']);?><br/>
                  <?php if ($inv['types'] == 'hp' or $inv['types'] == 'energy'):?>
                    <a href="?use=<?php echo $inv['id'];?>">[Использовать]</a>
                  <?php endif;?>
                </small>
              </div>
            </td>
          </tr>
        </table>
      </div>
      <?php
    }
    if ($total > 10) echo $pages -> page_links();
  }
  else
  {
    echo show_error('Ваш список предметов пуст.');
  }
break;

case 'inv':
  $title = 'Экипировка';
  include 'data/head.php';
  user();
  ?>
  <div style="margin: 2px 5px;">
    <div class="grid fights-link">
      <div class="six columns ln">
        <a href="/inv">Экипировка</a>
      </div>
      <div class="six columns">
        <a href="/inv/objects">Предметы</a>
      </div>
    </div>
  </div>
  <?
  if (isset($_GET['equip']))
  {
    if (empty($_GET['equip'])) echo show_error('Выберите вещь, что желаете одеть или снять.');
    elseif (!is_numeric($_GET['equip'])) echo show_error('Ошибка в выборе предмета.');
    else
    {
      $info = $wpn -> equipWeapons($_GET['equip'], $uid);
      if (isset($info['error']))
      {
        $_SESSION['error'] = $info['error'];
        die(header('Location: /inv'));
      }
      else
      {
        $_SESSION['success'] = $info['success'];
        die(header('Location: /inv'));
      }
    }
  }

  $pages = new Paginator(10, 'page');
  $stmt = $go -> prepare('SELECT `weapons_users`.`id` FROM `weapons_users` JOIN `weapons` ON (`weapons_users`.`id_weapon` = `weapons`.`id`) WHERE `weapons_users`.`id_user` = ?');
  $stmt -> execute([$uid]);
  $total = $stmt -> rowCount();

  $pages -> set_total($total);

  if ($total > 0)
  {
    $stmt = $go -> prepare('SELECT `weapons_users`.`id`, `weapons_users`.`id_weapon`, `weapons_users`.`used`, `weapons`.`name`, `weapons`.`slot` FROM `weapons_users` JOIN `weapons` ON (`weapons_users`.`id_weapon` = `weapons`.`id`) WHERE `weapons_users`.`id_user` = ? ORDER BY `weapons_users`.`dateAdd` DESC '.$pages -> get_limit());
    $stmt -> execute([$uid]);
    $get = $stmt -> fetchAll();

    $slot = [
      'boot' => ['name' => 'Ноги', 'damage' => 'Удар с ноги'],
      'hand' => ['name' => 'Тело', 'damage' => 'Удар с руки'],
      'head' => ['name' => 'Голова', 'damage' => 'Удар с головы'],
      'knife' => ['name' => 'Нож', 'damage' => 'Удар с ножа'],
      'pistol' => ['name' => 'Пистолет', 'damage' => 'Выстрел с пистолета'],
      'gun' => ['name' => 'Автомат', 'damage' => 'Выстрел с автомата']
    ];
    foreach ($get as $inv)
    {
      ?>
      <div class="fights fights-about">
        <table width="100%">
          <tr>
            <td width="58px" valign="top"><img width="58px" src="/files/<?php echo $inv['slot'];?>/<?php echo $inv['id_weapon'];?>.png" title="<?php echo $inv['name'];?>" /></td>
            <td valign="top">
              <div class="attack-text">
                <h1 class="human"><?php echo $obg -> show_ammo($inv['id_weapon']);?> <span class="small" style="color: #888;"><?php echo $slots[$inv['slot']]['ru']?></span></h1><hr/>
                <div class="quest-btn" style="margin: 5px 0"><?php echo ($inv['used'] == 1 ? '<a href="/inv/equip/'.$inv['id'].'">— Снять с себя</a>':'<a href="/inv/equip/'.$inv['id'].'">— Надеть на себя</a>');?></div>
              </div>
            </td>
          </tr>
        </table>
      </div>
      <?php
    }
    if ($total > 10) echo $pages -> page_links();
  }
  else
  {
    echo show_error('Ваш инвентарь пуст.');
  }
break;
}
include 'data/foot.php';