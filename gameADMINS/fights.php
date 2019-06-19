<?php
include 'data/base.php';
user();

if ($u['repute'] < 300)
{
$_SESSION['error'] = 'Бои с боссами будут доступны после того, как Вы наберете 300 репутации в деревне.';
die(header('Location: /'));
}

$stmt = $go -> prepare('SELECT * FROM `fights_users` WHERE `id_user` = ?');
$stmt -> execute([$uid]);
$bs = $stmt -> fetch();

if (!isset($bs['id']))
{
$stmt = $go -> prepare('INSERT INTO `fights_users` (`id_user`) VALUES (?)');
$stmt -> execute([$uid]);
header('Location: /fights');
die();
}

switch ($act)
{

default:
$title = 'Вылазка';
include 'data/head.php';
$stmt = $go -> prepare('SELECT `f`.`id`,`f`.`date_start` FROM `fights` AS `f` JOIN `fights_members` AS `fm` ON (`f`.`id` = `fm`.`id_fight`) WHERE `fm`.`id_user` = ? and `fm`.`banned` = ? and `fm`.`end` = ?');
$stmt -> execute([$uid, '0', '0']);
$fight = $stmt -> fetch();

if (isset($fight['id']))
{
  if ($fight['date_start'] != NULL) die(header('Location: /fights/battle/'.$fight['id']));
  else die(header('Location: /fights/room/'.$fight['id']));
}

$pages = new Paginator(10, 'page');
$stmt = $go -> prepare('SELECT `id` FROM `fights_boss`');
$stmt -> execute([]);
$total = $stmt -> rowCount();
$pages -> set_total($total);
if ($total == 0) show_error('Боссов еще нет.');
else
{
  $stmt = $go -> prepare('SELECT * FROM `fights_boss` ORDER BY `id` ASC '.$pages -> get_limit());
  $stmt -> execute([]);
  $bosses = $stmt -> fetchAll();
  foreach ($bosses as $boss)
  {
    $timeout = 'timeout_'.$boss['id'];
    $success = 'success_'.$boss['id'];
    $amount = $obg -> getCountObject($uid, $boss['need_key']);
    ?>
    <div class="fights">
      <div class="background-block" style="background: url(/files/boss/<?php echo $boss['id'];?>.png) center no-repeat;"></div>
      <div class="fights fights-about" style="margin: 5px 0;">
        <strong><?php echo $boss['name'];?></strong> <span class="label pull-right"><img src="/imgs/hp.png" width="10px" /> <?php echo $boss['health'];?></span>
      </div>
      <?php
      if (isset($_GET['award']) and $_GET['award'] == $boss['id'])
      {
        ?>
        <div style="margin-bottom: 5px;text-align: center;">
          <div class="fights-award"><?php echo $obg -> show_medal($boss['give_key']);?></div>
          <div class="fights-award"><img src="/imgs/bolts.png" width="12px" /> <?php echo $boss['bolts'];?></div>
          <div class="fights-award"><img src="/imgs/repute.png" width="12px" /> <?php echo $boss['repute'];?></div>
          <div class="fights-award"><img src="/imgs/house.png" width="12px" /> <?php echo $obg -> show_wall($boss['background']);?> *</div>
          <?php
          $stmt = $go -> prepare('SELECT * FROM `fights_boss_awards` WHERE `id_boss` = ?');
          $stmt -> execute([$boss['id']]);
          $drop = $stmt -> fetchAll();
          $count['drop'] = $stmt -> rowCount();
          if ($count['drop'] > 0)
          {
            foreach ($drop as $item)
            {
              echo '<div class="fights-award">'.$obg -> show_ammo($item['id_weapon']).'</div>';
            }
          }
          ?>
          <br/>
          <small style="color: #ffa200;">* - есть шанс выпадения данной вещи.</small>
        </div>
        <?php
      }
      else
      {
        echo '<div style="margin: 5px 0;" class="fights-link fights-orange-link center"><a href="?award='.$boss['id'].'">Посмотреть награду</a></div>';
      }
      if ($boss['id'] != 1 and ($amount < 3)): ?>
      <div class="zone fights-about no-margin center">
        Для нападения нужно - <?php echo $obg -> show_medal($boss['need_key']);?>.<hr/>
        У вас <?php echo $amount;?> из 3 медалей
      </div>
      <?php elseif ($bs[$timeout] > time() and $bs[$timeout] != NULL): ?>
        <div class="fights fights-about nmfull center ">
          Следующий бой будет доступен через<br/>
          <?php echo downcounter(date('Y-m-j H:i:s', $bs[$timeout]));?>
        </div>
      <?php else: ?>
        <div class="grid fights-link">
          <div class="six columns ln">
            <a href="/fights/create/solo/<?php echo $boss['id'];?>">Одному</a>
          </div>
          <div class="six columns">
            <a href="/fights/create/party/<?php echo $boss['id'];?>">С друзьями</a>
          </div>
        </div>
        <?php echo ($boss['id'] != 1 ? '<div style="margin-top: 2px;" class="zone fights-about no-margin center">У вас '.declension($amount, ['медаль','медали','медалей']).'</div>':'');?>
      <?php endif; ?>
    </div>
    <?php
  }
  if ($total > 10) echo $pages -> page_links();
}
$stmt = $go -> prepare('SELECT `fights`.`id` FROM `fights` INNER JOIN `friends` ON (`fights`.`id_user` = `friends`.`id_user` or `fights`.`id_user` = `friends`.`id_friend`) WHERE `fights`.`date_end` IS NOT NULL and `fights`.`date_start` IS NULL and (`friends`.`id_user` = ? or `friends`.`id_friend` = ?) and `fights`.`id_user` != ?');
$stmt -> execute([$uid, $uid, $uid]);
$count = $stmt -> rowCount();
if ($count > 0) echo '<div class="fights-link fights-orange-link" style="margin: 5px;"><a href="/fights/list">Присоединиться к друзьям</a></div>'; 
break;

case 'create':
if (empty($ids) or empty($_GET['type']))
{
  // Если не передан id босса или тип битвы
  $_SESSION['success'] = 'Не выбран босс или тип битвы';
  die(header('Location: /fights'));
}
elseif (!is_numeric($ids))
{
  // если id не является числом
  $_SESSION['success'] = 'Ошибка в запросе';
  die(header('Location: /fights'));
}
elseif ($_GET['type'] != 'solo' and $_GET['type'] != 'party')
{
  // если не тот тип
  $_SESSION['success'] = 'Неправильно выбран тип битвы';
  die(header('Location: /fights'));
}
else
{
  $stmt = $go -> prepare('SELECT * FROM `fights_boss` WHERE `id` = ?');
  $stmt -> execute([$ids]);
  $boss = $stmt -> fetch();

  if (!isset($boss['id']))
  {
    // Если босса не существует.
  $_SESSION['success'] = 'Такого босса не существует';
  die(header('Location: /fights'));
  }
  else
  {
    $stmt = $go -> prepare('SELECT `id` FROM `fights_members` WHERE `id_user` = ? and `banned` = ? and `end` = ?');
    $stmt -> execute([$uid, 0, 0]);
    $check = $stmt -> fetch();

    $timeout = 'timeout_'.$ids;

    if (isset($check['id']))
    {
      // Если игрок уже участвует в битве
      $_SESSION['success'] = 'Вы участвуете в другой битве. Покиньте ее или закончите бой, чтобы создать новую.';
      die(header('Location: /fights'));
    }
    elseif ($ids != 1 and $obg -> getCountObject($uid, $boss['need_key']) < 3)
    {
      // Если недостаточно медалей
      $_SESSION['success'] = 'Недостаточно медалей, чтобы атаковать данного босса.';
      die(header('Location: /fights'));
    }
    elseif ($bs[$timeout] > time() and $bs[$timeout] != NULL)
    {
      // Если игрок уже недавно атаковал босса
      $_SESSION['success'] = 'Вы участвуете в другой битве. Покиньте ее или закончите бой, чтобы создать новую.';
      die(header('Location: /fights'));
    }
    else
    {
      if ($_GET['type'] == 'solo')
      {
        $fight = time() + 10800; // Даем 3 часа
        $start = time(); // Стартуем сразу
        $obg -> takeObject($uid, $boss['need_key'], 3);
      }
      else
      {
        $fight = null;
        $start = null;
      }

      $stmt = $go -> prepare('INSERT INTO `fights` (`id_user`, `id_boss`, `hp_boss`, `type_boss`, `date_fight`, `date_start`, `reward`) VALUES (?, ?, ?, ?, ?, ?, ?)');
      $stmt -> execute([$uid, $ids, $boss['health'], $_GET['type'], $fight, $start, 0]);
      $battle = $go -> lastInsertId();

      $stmt = $go -> prepare('INSERT INTO `fights_members` (`id_user`, `id_fight`, `time_add`) VALUES (?, ?, ?)');
      $stmt -> execute([$uid, $battle, time()]);

      if ($_GET['type'] == 'solo') die(header('Location: /fights/battle/'.$battle)); // В битву
        else die(header('Location: /fights/room/'.$battle)); // В комнату ожидания
    }
  }
}
break;

case 'room':
$title = 'Комната ожидания вылазки';
include 'data/head.php';
$stmt = $go -> prepare('SELECT * FROM `fights` WHERE `id` = ?');
$stmt -> execute([$ids]);
$room = $stmt -> fetch();

if (!isset($room['id']))
{
  $_SESSION['success'] = 'Такой битвы не существует.';
  die(header('Location: /fights'));
}
else
{
  $stmt = $go -> prepare('SELECT * FROM `fights_boss` WHERE `id` = ?');
  $stmt -> execute([$room['id_boss']]);
  $boss = $stmt -> fetch();

  $stmt = $go -> prepare('SELECT * FROM `fights_members` WHERE `id_fight` = ? and `banned` = ? ORDER BY `time_add` ASC');
  $stmt -> execute([$ids, 0]);
  $members = $stmt -> fetchAll();
  $total = $stmt -> rowCount();

  $stmt = $go -> prepare('SELECT `id`,`id_fight`,`end` FROM `fights_members` WHERE `id_user` = ? and `banned` = ? and `end` = ?');
  $stmt -> execute([$uid, 0, 0]);
  $other = $stmt -> fetch();

  if ($room['date_end'] != NULL)  $battle = 'Битва окончена';
  elseif ($room['date_start'] == NULL) $battle = 'Сейчас идет сбор';
  elseif ($room['date_start'] < time()) $battle = 'Битва уже началась';
  else $battle = 'неизвестно';
  ?>
  <div class="col margin-left-right">
    <strong><?php echo $battle;?></strong><br/>
  </div>
  <?php
  if (isset($_GET['dissolve']) and $uid == $room['id_user'])
  {
    if ($room['date_end'] != NULL or ($room['date_start'] < time() and $room['date_start'] != NULL)) show_error('Слишком поздно, битву уже нельзя отменить.');
    else
    {
      if (isset($_GET['dissolve']) and isset($_GET['ok']))
      {
        $stmt = $go -> prepare('DELETE FROM `fights` WHERE `id` = ?');
        $stmt -> execute([$room['id']]);

        $stmt = $go -> prepare('DELETE FROM `fights_members` WHERE `id_fight` = ?');
        $stmt -> execute([$room['id']]);

        $_SESSION['success'] = 'Отряд успешно распущен, а битва отменена.';
        die(header('Location: /fights'));
      }
      else
      {
        ?>
        <div class="dialog">
          <h1 class="pda">КПК</h1>
          <p>
            › Вы действительно хотите распустить весь отряд и отменить битву?
          </p>
          <div class="grid fights-link">
            <div class="six columns ln">
              <a href="?dissolve&ok">Распустить</a>
            </div>
            <div class="six columns">
              <a href="/fights/room/<?php echo $room['id'];?>">Отменить</a>
            </div>
          </div>
        </div>
        <?php
      }
    }
  }
  if (isset($_GET['force']) and $uid == $room['id_user'])
  {
    if ($room['date_end'] != NULL or ($room['date_start'] < time() and $room['date_start'] != NULL)) show_error('Данное действие уже невозможно.');
    else
    {
      if (isset($_GET['force']) and isset($_GET['ok']))
      {
        foreach ($members as $check)
        {
          if ($check['id_user'] != $room['id_user'])
          {
            $stmt = $go -> prepare('SELECT * FROM `friends` WHERE (`id_user` = ? or `id_friend` = ?) and (`id_user` = ? or `id_friend` = ?) and `request` = ? LIMIT 1');
            $stmt -> execute([$room['id_user'], $room['id_user'], $check['id_user'], $check['id_user'], '1']);
            $for = $stmt -> fetch();

            if (!isset($for['id']))
            {
              $stmt = $go -> prepare('DELETE FROM `fights_members` WHERE `id_user` = ?');
              $stmt -> execute([$check['id_user']]);

              $logText = 'исключен из битвы, так как не является другом лидера битвы.';
              $stmt = $go -> prepare('INSERT INTO `fights_logs` (`id_user`, `id_fight`, `log`, `time`) VALUES (?, ?, ?, ?)');
              $stmt -> execute([$check['id_user'], $room['id'], $logText, time()]);

              $noteText = 'Вы были исключены из битвы [ID:'.$room['id'].'], так как не являетесь другом лидера битвы.';
              $stmt = $go -> prepare('INSERT INTO `notify` (`id_user`, `note`, `time`) VALUES (?, ?, ?)');
              $stmt -> execute([$check['id_user'], $noteText, time()]);
            }
          }
          if ($boss['id'] != 1) $obg -> takeObject($check['id_user'], $boss['need_key'], 3);
        }

        $stmt = $go -> prepare('UPDATE `fights` SET `date_start` = ?, `date_fight` = ? WHERE `id_user` = ? and `id` = ?');
        $stmt -> execute([time(), (time() + 10800), $uid, $room['id']]);

        $_SESSION['success'] = 'Битва начата. Удачи в сражении!';
        die(header('Location: /fights/battle/'.$room['id']));
      }
      else
      {
        ?>
        <div class="dialog">
          <h1 class="pda">КПК</h1>
          <p>
            › Вы действительно хотите начать битву прямо сейчас?
          </p>
          <div class="grid fights-link">
            <div class="six columns ln">
              <a href="?force&ok">Начать</a>
            </div>
            <div class="six columns">
              <a href="/fights/room/<?php echo $room['id'];?>">Отменить</a>
            </div>
          </div>
        </div>
        <?php
      }
    }
  }
  ?>
  <div class="background-block" style="background: url(/files/boss/<?php echo $room['id_boss'];?>.png) center no-repeat;">
  </div>
  <div class="zone">
    <div class="fights-about">
      <?php echo $boss['name'];?> <span class="pull-right"><?php echo $room['hp_boss'];?>/<?php echo $boss['health'];?></span>
      <div class="exp"><div style="width: <?php echo 100 * $room['hp_boss']/$boss['health'];?>%;" class="exp-line"></div></div><hr/>
    </div>
    <div class="fights-about">
      Создал › <?php echo show_user($room['id_user']);?><br/>
      <?php echo ($room['date_start'] != NULL ? 'Начало › '.date('j.m.Y в H:i:s', $room['date_start']).'<br/>' : null);?>
      <?php echo ($room['date_end'] != NULL ? 'Окончание › '.date('j.m.Y в H:i:s', $room['date_end']):''); ?>
    </div><hr/>
    <?php if ($uid == $room['id_user'] and $room['date_start'] == NULL):?>
    <div class="grid fights-link">
      <div class="six columns ln">
        <a href="/fights/room/<?php echo $room['id'];?>?force">Начать</a>
      </div>
      <div class="six columns">
        <a href="/fights/room/<?php echo $room['id'];?>?dissolve">Распустить</a>
      </div>
    </div><hr/>
    <?php elseif ($room['date_start'] != NULL and $other['id_fight'] == $room['id']): ?>
      <div class="fights-link fights-orange-link center" style="margin: 2px 0;"><a href="/fights/battle/<?php echo $room['id'];?>">Перейти к битве</a></div>
    <?php endif;?>
    <div class="fights-link center"><a href="/fights/room/<?php echo $room['id'];?>?refresh=<?php echo rand(111111,999999);?>">Обновить страницу</a></div>
    <?php
    $stmt = $go -> prepare('SELECT * FROM `fights_members` WHERE `id_user` = ? and `id_fight` = ? and `banned` = ?');
    $stmt -> execute([$uid, $room['id'], 0]);
    $infight = $stmt -> fetch();
    if (isset($infight['id']) and $room['id_user'] != $uid)
    {
      if (isset($_GET['leave']))
      {
        if (isset($_GET['leave']) and isset($_GET['ok']))
        {
          $stmt = $go -> prepare('DELETE FROM `fights_members` WHERE `id_user` = ? and `id_fight` = ?');
          $stmt -> execute([$uid, $room['id']]);

          $_SESSION['success'] = 'Вы успешно покинули данную битвы.';
          die(header('Location: /fights/room/'.$room['id']));
        }
        else
        {
          ?>
          <div class="dialog no-margin">
            <h1 class="pda">КПК</h1>
            <p>
              › Вы действительно хотите покинуть данную битву?<br/>
            </p>
            <div class="grid fights-link">
              <div class="six columns ln">
                <a href="?leave&ok">Покинуть</a>
              </div>
              <div class="six columns">
                <a href="/fights/room/<?php echo $room['id'];?>">Отменить</a>
              </div>
            </div>
          </div>
          <?php
        }
      }
      else
      {
        echo '<div class="fights-link center" style="margin: 2px 0;"><a href="?leave">Покинуть битву</a></div>';
      }
    }
    elseif (!isset($infight['id']))
    {
      $stmt = $go -> prepare('SELECT * FROM `friends` WHERE (`id_user` = ? or `id_friend` = ?) and (`id_user` = ? or `id_friend` = ?) and `request` = ? LIMIT 1');
      $stmt -> execute([$uid, $uid, $room['id_user'], $room['id_user'], '1']);
      $outfight = $stmt -> fetch();

      if (isset($outfight['id']) and $room['date_start'] == NULL)
      {
        if (isset($_GET['join']))
        {
          if (isset($other['id']))
          {
            $_SESSION['success'] = 'Покиньте прошлую битву, чтобы присоединиться к этой.';
            die(header('Location: /fights/room/'.$room['id']));
          }
          elseif ($obg -> getCountObject($uid, $boss['need_key']) < 3 and $room['id_boss'] != 1)
          {
            $_SESSION['success'] = 'Недостаточно медалей для доступа к боссу.';
            die(header('Location: /fights/room/'.$room['id']));
          }
          elseif ($total >= 50)
          {
            $_SESSION['success'] = 'В битве уже максимальное количество участников.';
            die(header('Location: /fights/room/'.$room['id']));
          }
          else
          {
            $stmt = $go -> prepare('INSERT INTO `fights_members` (`id_user`, `id_fight`, `time_add`) VALUES (?, ?, ?)');
            $stmt -> execute([$uid, $room['id'], time()]);

            $_SESSION['success'] = 'Вы успешно присоединились к битве.';
            die(header('Location: /fights/room/'.$room['id']));
          }
        }
        echo '<div class="fights-link center" style="margin: 2px 0;"><a href="?join">Присоединиться к битве</a></div>';
      }
    }
    ?>
  </div>
  <div class="col margin-left-right">
    <strong>Участники сражения</strong><br/>
    <small><?php echo $total;?> из 50 возможных</small>
  </div>
  <?php
  if (isset($_GET['kick']) and $room['id_user'] == $uid)
  {
    if ($room['date_end'] != NULL or ($room['date_start'] < time() and $room['date_start'] != NULL)) show_error('Слишком поздно, уже нельзя исключать.');
    elseif (empty($_GET['kick'])) show_error('Выберите, кого следует исключить.');
    elseif (!is_numeric($_GET['kick'])) show_error('Неправильное значение.');
    elseif ($_GET['kick'] == $uid) show_error('Нельзя исключить самого себя.');
    else {
      $stmt = $go -> prepare('SELECT * FROM `fights_members` WHERE `id_user` = ? and `id_fight` = ?');
      $stmt -> execute([$_GET['kick'], $room['id']]);
      $kick = $stmt -> fetch();

      if (!isset($kick['id'])) show_error('В данном походе нет такого игрока.');
      elseif ($kick['banned'] == 1) show_error('Этот игрок уже был исключен.');
      else
      {
        if (isset($_GET['kick']) and $room['id_user'] == $uid and isset($_GET['ok']))
        {
          $stmt = $go -> prepare('UPDATE `fights_members` SET `banned` = ? WHERE `id_user` = ? and `id_fight` = ?');
          $stmt -> execute([1, $_GET['kick'], $room['id']]);
          $_SESSION['success'] = 'Данный игрок успешно исключен и больше не сможет присоединиться к данной битве.';
          die(header('Location: /fights/room/'.$room['id']));
        }
        else
        {
          ?>
          <div class="dialog">
            <h1 class="pda">КПК</h1>
            <p>
              › Вы действительно хотите исключить <?php echo show_user($_GET['kick']);?> из битвы?<br/>
              <small>* Игрок больше не сможет присоединиться к данной битве.</small>
            </p>
            <div class="grid fights-link">
              <div class="six columns ln">
                <a href="?kick=<?php echo $_GET['kick'];?>&ok">Исключить</a>
              </div>
              <div class="six columns">
                <a href="/fights/room/<?php echo $room['id'];?>">Отменить</a>
              </div>
            </div>
          </div>
          <?php
        }
      }
    }
  }
  ?>
  <div class="fights-list fights-about margin-left-right">
    <?php foreach ($members as $mb):?>
      › <?php echo show_user($mb['id_user']);?> <?php echo ($mb['id_user'] == $room['id_user'] ? '<b>[Л]</b>':NULL);?> <?php echo ($uid == $room['id_user'] && $mb['id_user'] != $room['id_user'] ? '- <a href="?kick='.$mb['id_user'].'">исключить</a>':NULL);?><br/>
    <?php endforeach;?>
  </div>
  <?php
}
break;

case 'battle':
$title = 'Сражение с боссом';
include 'data/head.php';
$stmt = $go -> prepare('SELECT * FROM `fights` WHERE `id` = ?');
$stmt -> execute([$ids]);
$battle = $stmt -> fetch();

if (!isset($battle['id']))
{
  $_SESSION['success'] = 'Такой битвы не существует.';
  die(header('Location: /fights'));
}
else
{
  $stmt = $go -> prepare('SELECT * FROM `fights_boss` WHERE `id` = ?');
  $stmt -> execute([$battle['id_boss']]);
  $boss = $stmt -> fetch();

  $stmt = $go -> prepare('SELECT * FROM `fights_members` WHERE `id_fight` = ? and `id_user` = ? and `banned` = ?');
  $stmt -> execute([$ids, $uid, 0]);
  $mem = $stmt -> fetch();
  $check = $stmt -> rowCount();

  if ($check == 0)
  {
    $_SESSION['success'] = 'Вы не участвуете в данной битве.';
    die(header('Location: /fights'));
  }
  elseif ($battle['date_start'] == NULL)
  {
    $_SESSION['success'] = 'Битва еще не началась, ожидайте.';
    die(header('Location: /fights/room/'.$battle['id']));
  }
  elseif ($mem['end'] == 1)
  {
    $_SESSION['success'] = 'Битва окончена.';
    die(header('Location: /fights/room/'.$battle['id']));
  }
  elseif (isset($_GET['members'])) // Список участников битвы.
  {
    $pages = new Paginator(10, 'page');
    $stmt = $go -> prepare('SELECT `id` FROM `fights_members` WHERE `id_fight` = ? and `banned` = ?');
    $stmt -> execute([$battle['id'], 0]);
    $total = $stmt -> rowCount();
    $pages -> set_total($total);

    $stmt = $go -> prepare('SELECT `id_user`, `damage` FROM `fights_members` WHERE `id_fight` = ? and `banned` = ? ORDER BY `damage` DESC '.$pages -> get_limit());
    $stmt -> execute([$battle['id'], 0]);
    $get = $stmt -> fetchAll();

    if ($pages->_page == 1) $pl = 0;
      else $pl = (10 * $pages->_page) - 10;
    echo '<div class="fights fights-list fights-about" style="margin: 2px 5px;">';
    echo '<strong>Топ рейтинг по урону</strong><br/>';
    foreach($get as $place)
    {
      $pl += 1;
      echo '#'.$pl.'. '.show_user($place['id_user']).' / Нанесено урона › '.numb($place['damage']).'<br/>';
    }
    echo '<a href="/fights/battle/'.$battle['id'].'" class="fights-a">Вернуться назад</a>';
    echo '</div>';
    if ($total > 10) echo $pages -> page_links();
  }
  elseif (isset($_GET['logs'])) // Лог битвы
  {
    $pages = new Paginator(10, 'page');
    $stmt = $go -> prepare('SELECT * FROM `fights_logs` WHERE `id_fight` = ?');
    $stmt -> execute([$battle['id']]);
    $total = $stmt -> rowCount();
    $pages -> set_total($total);

    $stmt = $go -> prepare('SELECT * FROM `fights_logs` WHERE `id_fight` = ? ORDER BY `time` DESC '.$pages -> get_limit());
    $stmt -> execute([$battle['id']]);
    $get = $stmt -> fetchAll();

    echo '<div class="fights-list fights-about" style="margin: 2px 5px;">';
    echo '<strong>Полный журнал боя</strong><br/>';
    foreach($get as $log)
    {
      echo '['.date('H:i', $log['time']).'] '.show_user($log['id_user']).' › '.$log['log'].' <br/>';
    }
    echo '<a href="/fights/battle/'.$battle['id'].'" class="fights-a">Вернуться назад</a>';
    echo '</div>';
    if ($total > 10) echo $pages -> page_links();
  }
  elseif (!empty($battle['date_end']))
  {
    // Забрать награду, узнать результат.
    ?>
    <div class="background-block" style="background: url(/files/boss/<?php echo $battle['id_boss'];?>.png) center no-repeat;">
    </div>
    <div class="col margin-left-right">
      <strong>Битва окончена</strong><br/>
      <small><?php echo ($battle['reward'] == 0 ? 'Вы победили!':'Вы проиграли!');?></small>
    </div>
    <div class="fights fights-about margin">
      Начало битвы › <?php echo date('j.m.Y в H:i:s', $battle['date_start']);?><br/>
      Окончание битвы › <?php echo date('j.m.Y в H:i:s', $battle['date_end']);?><br/>
    </div>
    <div class="fights fights-about margin">
      <div class="grid">
        <?php
        $stmt = $go -> prepare('SELECT * FROM `fights_members` WHERE `id_fight` = ? ORDER BY `damage` DESC LIMIT 3');
        $stmt -> execute([$battle['id']]);
        $top = $stmt -> fetchAll();

        $place = 0;
        foreach ($top as $t)
        {
          $place += 1;
          ?>
          <div class="four columns">
            <div class="cl">
              #<?php echo $place; ?><br/>
              <?php echo show_user($t['id_user']); ?><br/>
              <?php echo numb($t['damage']); ?>
            </div>
          </div>
          <?php
        }
        ?>
      </div>
    </div>
    <?php
    if ($mem['end'] == 0 and $battle['reward'] == 0)
    {
      if (isset($_GET['award']))
      {
        $timeout = 'timeout_'.$battle['id_boss'];
        $success = 'success_'.$battle['id_boss'];

        $obg -> giveObject($uid, $boss['give_key'], 1);

        $sql = 'UPDATE `fights_users` SET `'.$success.'` = `'.$success.'` + ?, `'.$timeout.'` = ? WHERE `id_user` = ?';
        $stmt = $go -> prepare($sql);
        $stmt -> execute([1, time() + 14400, $uid]);

        $stmt = $go -> prepare('UPDATE `users` SET `bolts` = `bolts` + ?, `repute` = `repute` + ? WHERE `id` = ?');
        $stmt -> execute([$boss['bolts'], $boss['repute'], $uid]);

        $stmt = $go -> prepare('UPDATE `fights_members` SET `end` = ? WHERE `id_user` = ?');
        $stmt -> execute([1, $uid]);

        $stmt = $go -> prepare('DELETE FROM `fights_logs` WHERE `id_fight` = ?');
        $stmt -> execute([$battle['id']]);

        if ($battle['hp_boss'] < 0)
        {
          $stmt = $go -> prepare('UPDATE `fights` SET `hp_boss` = ? WHERE `id` = ?');
          $stmt -> execute([0, $battle['id']]);
        }

        $stmt = $go -> prepare('SELECT `id` FROM `background_users` WHERE `id_background` = ? and `id_user` = ?');
        $stmt -> execute([$boss['background'], $uid]);
        $check = $stmt -> rowCount();

        if ($check == 0)
        {
          $drop['background'] = drop(['0' => 90, '1' => 10]);
          if ($drop['background'] == 1)
          {
            $stmt = $go -> prepare('INSERT INTO `background_users` (`id_user`, `id_background`) VALUES (?, ?)');
            $stmt -> execute([$uid, $boss['background']]);

          }
        }

        $exp['groups'] = $grp -> expGive($uid, $boss['repute']);

        ?>
        <div class="fights fights-about">Награда: <br/>
          <div class="fights-award"><?php echo $obg -> show_medal($boss['give_key']);?></div>
          <div class="fights-award"><img src="/imgs/bolts.png" width="12px" /> <?php echo $boss['bolts'];?></div>
          <div class="fights-award"><img src="/imgs/repute.png" width="12px" /> <?php echo $boss['repute'];?></div>
          <?php if ($check == 0 and $drop['background'] == 1): ?>
          <div class="fights-award"><img src="/imgs/house.png" width="12px" /> <?php echo $obg -> show_wall($boss['background']);?></div>
          <?php endif; ?>
          <?php if ($exp['groups'] != 0): ?>
          <div class="fights-award"><img src="/imgs/repute.png" width="12px" /> ГП: <?php echo $exp['groups'];?></div>
          <?php endif; ?>
        </div>
        <?php
      }
      else
      {
        ?>
        <div class="fights-link fights-orange-link center" style="margin: 5px"><a href="?award">Забрать награду</a></div>
        <?php
      }
    }
    elseif ($mem['end'] == 0 and $battle['reward'] == 1)
    {
      if (isset($_GET['leave']))
      {
        $stmt = $go -> prepare('UPDATE `fights_members` SET `end` = ? WHERE `id_user` = ?');
        $stmt -> execute([1, $uid]);
        die(header('Location: /fights/room/'.$battle['id']));
      }
      else
      {
        ?>
        <div class="fights-link fights-orange-link center" style="margin: 5px"><a href="?leave">Покинуть битву</a></div>
        <?php
      }
    }
  }
  elseif ($battle['hp_boss'] <= 0) // Если успели добить
  {
    $stmt = $go -> prepare('UPDATE `fights` SET `date_end` = ?, `reward` = ? WHERE `id` = ?');
    $stmt -> execute([time(), 0, $battle['id']]);
    die(header('Location: /fights/battle/'.$battle['id']));
  }
  elseif ($battle['date_fight'] <= time() and $battle['hp_boss'] > 0 and empty($battle['date_end'])) // Если не добили по истечению времени.
  {
    $stmt = $go -> prepare('UPDATE `fights` SET `date_end` = ?, `reward` = ? WHERE `id` = ?');
    $stmt -> execute([time(), 1, $battle['id']]);
    die(header('Location: /fights/battle/'.$battle['id']));
  }
  else
  {
    if (isset($_GET['leave']))
    {
      $stmt = $go -> prepare('SELECT * FROM `fights_members` WHERE `id_fight` = ? and `id_user` != ? and `banned` = ?');
      $stmt -> execute([$battle['id'], $uid, 0]);
      $how = $stmt -> rowCount();

      if (isset($_GET['leave']) and isset($_GET['ok']))
      {
        if ($how == 0)
        {
          $stmt = $go -> prepare('DELETE FROM `fights` WHERE `id` = ?');
          $stmt -> execute([$battle['id']]);

          $stmt = $go -> prepare('DELETE FROM `fights_members` WHERE `id_fight` = ?');
          $stmt -> execute([$battle['id']]);

          $_SESSION['success'] = 'Вы сбежали с битвы. Я подчистила за Вами следы, никто и не узнает, что произошло.';
          die(header('Location: /fights'));
        }
        else
        {
          $stmt = $go -> prepare('DELETE FROM `fights_members` WHERE `id_fight` = ? and `id_user` = ?');
          $stmt -> execute([$battle['id'], $uid]);

          $_SESSION['success'] = 'Вы успешно сбежали с битвы.';
          die(header('Location: /fights'));
        }
      }
      else
      {
        ?>
        <div class="dialog">
          <h1 class="human">Шрам</h1>
          <p>
            › Так-так, сталкер. Сбежать собрался? Ну беги, никто не держит, но знай, что награды, в случае победы, ты не получишь!<br/>
            <small>* нападение будет засчитано и напасть снова на данного босса можно будет только через 6 часов.</small>
          </p>
          <div class="grid fights-link">
            <div class="six columns ln">
              <a href="?leave&ok">Сбежать</a>
            </div>
            <div class="six columns">
              <a href="/fights/battle/<?php echo $battle['id'];?>">Остаться</a>
            </div>
          </div>
        </div>
        <?php
      }
    }
    ?>
    <div class="col margin-left-right">
      <strong><?php echo downcounter(date('Y-m-j H:i:s', $battle['date_fight']));?></strong><br/>
      <small>Времени до окончания битвы</small>
    </div>
    <div class="background-block" style="background: url(/files/boss/<?php echo $battle['id_boss'];?>.png) center no-repeat;">
    </div>
    <?php
    if (isset($_GET['attack']))
    {
      if (empty($_GET['attack'])) show_error('Выберите тип атаки.');
      elseif ($_GET['attack'] != 'boot' && $_GET['attack'] != 'hand' && $_GET['attack'] != 'head' && $_GET['attack'] != 'knife' && $_GET['attack'] != 'pistol' && $_GET['attack'] != 'gun') show_error('Такого типа атаки не существует.');
      elseif ($boss['min_damage'] >= $u['hp']) show_error('У вас мало здоровья, чтобы атаковать босса.');
      elseif ($u[$_GET['attack']] < 1) show_error('Вы не можете бить данным типом атаки.');
      else {
        $attack = [
          'boot' => 'ударил с ноги',
          'hand' => 'ударил с руки',
          'head' => 'ударил с головы',
          'knife' => 'ударил ножом',
          'pistol' => 'выстрелил из пистолета',
          'gun' => 'выстрелил из автомата'
        ];

        $damage = $wpn -> getAtrb($uid, $_GET['attack']);
        $log = $attack[$_GET['attack']].' (урон: '.$damage.')';

        $sql = 'UPDATE `users` SET `'.$_GET['attack'].'` = `'.$_GET['attack'].'` - ? WHERE `id` = ?';
        $stmt = $go -> prepare($sql);
        $stmt -> execute([1, $uid]); // Отнимаем заряд

        $stmt = $go -> prepare('INSERT INTO `fights_logs` (`id_user`, `id_fight`, `log`, `time`) VALUES (?, ?, ?, ?)');
        $stmt -> execute([$uid, $battle['id'], $log, time()]); // Пишем лог

        $stmt = $go -> prepare('UPDATE `fights_members` SET `damage` = `damage` + ? WHERE `id_user` = ? and `id_fight` = ?');
        $stmt -> execute([$damage, $uid, $battle['id']]); // Обновляем урон

        $attackBoss = mt_rand($boss['min_damage'],$boss['max_damage']);
        if ($attackBoss > $u['hp'])
        {
          $stmt = $go -> prepare('UPDATE `users` SET `hp` = ? WHERE `id` = ?');
          $stmt -> execute([0, $u['id']]); // Обновляем хп игрока
        }
        else
        {
          $stmt = $go -> prepare('UPDATE `users` SET `hp` = `hp` - ? WHERE `id` = ?');
          $stmt -> execute([$attackBoss, $u['id']]); // Обновляем хп игрока
        }

        $stmt = $go -> prepare('UPDATE `fights` SET `hp_boss` = `hp_boss` - ? WHERE `id` = ?');
        $stmt -> execute([$damage, $battle['id']]); // Обновляем хп босса

        $_SESSION['success'] = 'Босс ударил по вам в ответ на <b>'.$attackBoss.'</b> ед. здоровья.';
        die(header('Location: /fights/battle/'.$battle['id']));
      }
    }
    // Проценты
    $percent['boss'] = 100 * $battle['hp_boss']/$boss['health'];
    $percent['user'] = 100 * $u['hp']/$u['max_hp'];
    ?>
    <div class="zone">
      <div class="fights-about">
        <?php echo $boss['name'];?> <span class="pull-right"><?php echo $battle['hp_boss'];?>/<?php echo $boss['health'];?></span>
        <div class="exp"><div style="width: <?php echo $percent['boss'];?>%;" class="exp-line"></div></div><hr/>
        Ваше здоровье <span class="pull-right"><?php echo $u['hp'];?>/<?php echo $u['max_hp'];?></span>
        <div class="exp"><div style="width: <?php echo $percent['user'];?>%;" class="exp-line"></div></div>
        <?php if (ceil($percent['user']) < 20):?>
        <div style="margin: 5px -2px;">
          <div class="grid">
            <div class="six columns">
              <div class="cl-foot">
                <a href="?medic=1" class="weapon">
                  <table width="100%">
                    <tr>
                      <td class="attack-text">
                        +<?php echo ceil($u['max_hp']/4)?> здоровья<br/>
                        <small>
                          Бинты [3 шт.]<br/>
                        </small>
                      </td>
                    </tr>
                  </table>
                </a>
              </div>
            </div>
            <div class="six columns">
              <div class="cl-foot">
                <a href="?medic=2" class="weapon">
                  <table width="100%">
                    <tr>
                      <td class="attack-text">
                        Все здоровье<br/>
                        <small>Аптечка за 1 <img src="/imgs/bolts.png" width="12px" /></small>
                      </td>
                    </tr>
                  </table>
                </a>
              </div>
            </div>
          </div>
        </div>
        <?php endif;?>
        <hr/>
      </div>
      <div style="margin: 2px 0;">
        <a href="?attack=boot" class="weapon">
          <table width="100%">
            <tr>
              <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/files/boot/default.png"></td>
              <td class="attack-text">
                Ударить ногой<br/>
                <small>
                  В наличии: <?php echo $u['boot'];?>
                </small>
              </td>
            </tr>
          </table>
        </a>
        <a href="?attack=hand" class="weapon">
          <table width="100%">
            <tr>
              <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/files/hand/default.png"></td>
              <td class="attack-text">
                Ударить рукой<br/>
                <small>
                  В наличии: <?php echo $u['hand'];?>
                </small>
              </td>
            </tr>
          </table>
        </a>
        <a href="?attack=head" class="weapon">
          <table width="100%">
            <tr>
              <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/files/head/default.png"></td>
              <td class="attack-text">
                Ударить с головы<br/>
                <small>
                  В наличии: <?php echo $u['head'];?>
                </small>
              </td>
            </tr>
          </table>
        </a>
        <a href="?attack=knife" class="weapon">
          <table width="100%">
            <tr>
              <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/files/knife/default.png"></td>
              <td class="attack-text">
                Ударить ножом<br/>
                <small>
                  В наличии: <?php echo $u['knife'];?>
                </small>
              </td>
            </tr>
          </table>
        </a>
        <a href="?attack=pistol" class="weapon">
          <table width="100%">
            <tr>
              <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/files/pistol/default.png"></td>
              <td class="attack-text">
                Выстрелить из пистолета<br/>
                <small>
                  В наличии: <?php echo $u['pistol'];?>
                </small>
              </td>
            </tr>
          </table>
        </a>
        <a href="?attack=gun" class="weapon">
          <table width="100%">
            <tr>
              <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/files/gun/default.png"></td>
              <td class="attack-text">
                Выстрелить из автомата<br/>
                <small>
                  В наличии: <?php echo $u['gun'];?>
                </small>
              </td>
            </tr>
          </table>
        </a>
      </div>
      <div class="grid fights-list fights-about" style="margin: 2px 0;padding: 2px;">
        <strong>Топ рейтинг по урону</strong><br/>
        <?php
        $stmt = $go -> prepare('SELECT * FROM `fights_members` WHERE `id_fight` = ? ORDER BY `damage` DESC LIMIT 3');
        $stmt -> execute([$battle['id']]);
        $top = $stmt -> fetchAll();

        $place = 0;
        foreach ($top as $t)
        {
          $place += 1;
          ?>
          <div class="four columns">
            <div class="cl">
              #<?php echo $place; ?><br/>
              <?php echo show_user($t['id_user']); ?><br/>
              <?php echo numb($t['damage']); ?>
            </div>
          </div>
          <?php
        }
        ?>
        <div class="twelve columns fights-link">
          <a href="/fights/battle/members/<?php echo $battle['id'];?>">Полный список</a>
        </div>
      </div>
      <div class="fights-list fights-about no-margin">
        <strong>Журнал боя</strong><br/>
        <?php
        $stmt = $go -> prepare('SELECT * FROM `fights_logs` WHERE `id_fight` = ? ORDER BY `time` DESC LIMIT 7');
        $stmt -> execute([$battle['id']]);
        $check_log = $stmt -> rowCount();
        $logs = $stmt -> fetchAll();

        if ($check_log == 0) echo '<div class="about">Еще никто не бил. Стань первым!</div>';
        else
        {
          foreach ($logs as $log)
          {
            echo '<small>['.date('H:i', $log['time']).']</small> '.show_user($log['id_user']).' › '.$log['log'].'<br/>';
          }
        }
        ?>
        <a href="/fights/battle/logs/<?php echo $battle['id'];?>" class="fights-a">Полный список</a>
      </div>
      <div class="grid fights-link">
        <div class="six columns ln">
          <a href="/fights/battle/<?php echo $battle['id'];?>?refresh=<?php echo rand(111111,999999);?>">Обновить</a>
        </div>
        <div class="six columns">
          <a href="/fights/battle/<?php echo $battle['id'];?>?leave">Сбежать</a>
        </div>
      </div>
    </div>
    <?php
  }
}
break;

}
include 'data/foot.php';