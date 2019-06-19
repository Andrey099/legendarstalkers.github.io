<?php
if (empty($title)) $title = 'Главная :: X-RAY.FUN';
else $title = $title.' :: X-RAY.FUN';
$start_time = microtime();
$start_array = explode(" ",$start_time, 5);
$start_time = $start_array[1] + $start_array[0];
?>
<!DOCTYPE html5>
<html>
  <head>
    <meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8"/>
    <meta http-equiv="Content-Style-Type" content="text/css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/imgs/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="/style/roboto.css">
    <link rel="stylesheet" type="text/css" href="/style/start.css">
    <link rel="stylesheet" type="text/css" href="/style/grid.css">
    <title><?php echo $title;?></title>
  </head>
  <body>
  <div class="center margin"><a href="/"><img src="/imgs/minilogo.png" width="94px" /></a></div>
  <div class="content">
  <?php
  if (isset($uid))
  {
    $stmt = $go -> prepare('SELECT * FROM `banned` WHERE `id_user` = ? LIMIT 1');
    $stmt -> execute([$uid]);
    $ban = $stmt -> fetch();
    if (isset($ban))
    {
      if ($ban['method'] == 1)
      {
        ?>
        <div style="margin: 2px 5px;" class="col">
          <strong>Аккаунт заблокирован</strong><br/>
          <small>Навсегда / разбан невозможен</small>
        </div>
        <div class="dialog">
          <h1 class="chat">Выдал <?php echo show_user($ban['id_who']);?></h1>
          <p>
            Причина: <?php echo $ban['reason'];?><br/>
          </p><hr/>
          <small>Время › <?php echo date('j.m.Y в H:i', $ban['time']);?></small>
        </div>
        <?php
        include 'foot.php';
        die();
      }
      elseif ($ban['method'] == 0 and $ban['time_ban'] > time())
      {
        ?>
        <div style="margin: 2px 5px;" class="col">
          <strong>Аккаунт временно заблокирован</strong><br/>
          <small>еще <?php echo downcounter(date('Y-m-j H:i:s', $ban['time_ban']))?></small>
        </div>
        <div class="dialog">
          <h1 class="chat">Выдал <?php echo show_user($ban['id_who']);?></h1>
          <p>
            Причина: <?php echo $ban['reason'];?><br/>
          </p><hr/>
          <small>Время › <?php echo date('j.m.Y в H:i', $ban['time']);?></small>
        </div>
        <?php
        include 'foot.php';
        die();
      }
    }

    if ($_SERVER['PHP_SELF'] != '/start.php' and ($u['start'] == 0 or $u['start'] == 1)) header('Location: /start/step/1');
    if ($u['save'] == 0 and $u['start'] == 2 and $_SERVER['PHP_SELF'] != '/save.php') echo '<div class="callout"><a href="/save/">Сохраните свой аккаунт</a> и получите 10 РУБ.</div>';

    if (time() > ($u['updDate'] + 60)) // Раз в минуту обновляем активность (лишний запрос при каждом переходе не гуд, пусть будет погрешность)
    {
      $stmt = $go -> prepare('UPDATE `users` SET `updDate` = ? WHERE `id` = ?');
      $stmt -> execute([time(), $uid]);
    }

    if ($level[$u['level']+1] <= $u['exp'])
    {
      $rubles = ($u['level']+1);
      $bolts = ($u['level']+1)*5;

      $aw[] = 'Вы перешли на уровень '.($u['level']+1);
      $aw[] = 'Рубли: + '.$rubles;
      $aw[] = 'Болты: + '.$bolts;
      $aw[] = 'Энергия и здоровье восстановлены.';

      echo show_error($aw);
      $stmt = $go -> prepare('UPDATE `users` SET `level` = `level` + ?, `rubles` = `rubles` + ?, `bolts` = `bolts` + ?, `energy` = ?, `hp` = ?, `exp` = ? WHERE `id` = ?');
      $stmt -> execute([1, $rubles, $bolts, $u['max_energy'], $u['max_hp'], 0, $uid]);
    }

    $g['user'] = $grp -> inGroups($uid);
    if ($g['user'] == 0)
    {
      $stmt = $go -> prepare('SELECT `id`, `id_group`, `invite` FROM `groups_users` WHERE `accept` = ? ORDER BY `id` DESC LIMIT 1');
      $stmt -> execute(['0']);
      $invited = $stmt -> fetch();
      if ($invited != FALSE)
      {
        if (isset($_GET['groupInviteAccept']))
        {
          $inviteInfo = $grp -> infoGroups($invited['id_group']);
          if ($inviteInfo['group']['total'] >= $inviteInfo['group']['max_users'])
          {
            $stmt = $go -> prepare('DELETE FROM `groups_users` WHERE `id_group` = ? and `id_user` = ? and `accept` = ?');
            $stmt -> execute([$invited['id_group'], $uid, '0']);
            $_SESSION['error'] = 'В группировке уже максимально число участников. Заявка автоматически удалена.';
            die(header('Location: ?'));
          }
          else
          {
            $stmt = $go -> prepare('UPDATE `groups_users` SET `accept` = ?, `dateAdd` = ? WHERE `id_group` = ? and `id_user` = ?');
            $stmt -> execute([1, time(), $invited['id_group'], $uid]);

            $stmt = $go -> prepare('DELETE FROM `groups_users` WHERE `id_user` = ? and `accept` = ?');
            $stmt -> execute([$uid, '0']);
            $_SESSION['success'] = 'Вы успешно присоединились к группировке.';
            die(header('Location: ?'));
          }
        }
        elseif (isset($_GET['groupInviteNotAccept']))
        {
          $stmt = $go -> prepare('DELETE FROM `groups_users` WHERE `id_group` = ? and `id_user` = ? and `accept` = ?');
          $stmt -> execute([$invited['id_group'], $uid, '0']);
          $_SESSION['success'] = 'Вы успешно отклонили приглашение.';
          die(header('Location: ?'));
        }
        ?>
        <div class="fights fights-about center">
          <?php echo show_user($invited['invite']);?> приглашает Вас в группировку <?php echo $grp -> show_group($invited['id_group']);?>
          <div style="margin: 5px 0 0 0;">
            <div class="grid fights-link">
              <div class="six columns ln">
                <a href="?groupInviteAccept">Принять</a>
              </div>
              <div class="six columns">
                <a href="?groupInviteNotAccept">Отклонить</a>
              </div>
            </div>
          </div>
        </div>
        <?php
      }
    }

    $stmt = $go -> prepare('SELECT * FROM `friends` WHERE `id_friend` = ? and `request` = ?');
    $stmt -> execute([$uid, '0']);
    $req = $stmt -> rowCount();

    if ($req > 0 and $_SERVER['REQUEST_URI'] != '/pda/friends/request')
    {
      ?>
      <div style="margin: 5px;">
        <a href="/pda/friends/request" class="weapon">
          <table width="100%">
            <tr>
              <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/friends.png"></td>
              <td class="attack-text">
                Добавление в друзья<br/>
                <small>
                  <?php echo declension($req, ['не принятая заявка','не принятые заявки','не принятых заявок']);?>
                </small>
              </td>
            </tr>
          </table>
        </a>
      </div>
      <?php
    }

    $stmt = $go -> prepare('SELECT * FROM `notify` WHERE `id_user` = ? and `view` = ?');
    $stmt -> execute([$uid, '0']);
    $note = $stmt -> rowCount();

    if ($note > 0 and $_SERVER['REQUEST_URI'] != '/pda/notify')
    {
      ?>
      <div style="margin: 5px;">
        <a href="/pda/notify" class="weapon">
          <table width="100%">
            <tr>
              <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/notify.png"></td>
              <td class="attack-text">
                Уведомления<br/>
                <small>
                  <?php echo declension($note, ['новое уведомление','новых уведомлений','новых уведомлений']);?>
                </small>
              </td>
            </tr>
          </table>
        </a>
      </div>
      <?php
    }

    $stmt = $go -> prepare('SELECT `fights`.`id`,`fights`.`date_start`,`fights`.`date_end`, `fights_members`.`end` FROM `fights` JOIN `fights_members` ON (`fights`.`id` = `fights_members`.`id_fight`) WHERE `fights_members`.`id_user` = ? and `fights_members`.`banned` = ?');
    $stmt -> execute([$uid, '0']);
    $fight = $stmt -> fetch();

    if (isset($fight['id']) and $fight['date_start'] != NULL and $_SERVER['PHP_SELF'] != '/fights.php' and $fight['end'] == 0)
    {
      if ($fight['end'] == 0 and !empty($fight['date_end'])) $text = 'Сражение окончено';
      elseif (!empty($fight['date_start'])) $text = 'Прямо сейчас идет сражение';
      ?>
      <div style="margin: 5px;">
        <a href="/fights/battle/<?php echo $fight['id'];?>" class="weapon">
          <table width="100%">
            <tr>
              <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/files/gun/default.png"></td>
              <td class="attack-text">
                Сражение с боссом<br/>
                <small>
                  <?php echo $text;?>
                </small>
              </td>
            </tr>
          </table>
        </a>
      </div>
      <?php
    }
    if ($u['hp'] < 0)
    {
      $stmt = $go -> prepare('UPDATE `users` SET `hp` = ? WHERE `id` = ?');
      $stmt -> execute([0, $uid]);
    }
    if ($u['hp'] > $u['max_hp'])
    {
      $stmt = $go -> prepare('UPDATE `users` SET `hp` = ? WHERE `id` = ?');
      $stmt -> execute([$u['max_hp'], $uid]);
    }
    if ($u['energy'] > $u['max_energy'])
    {
      $stmt = $go -> prepare('UPDATE `users` SET `energy` = ? WHERE `id` = ?');
      $stmt -> execute([$u['max_energy'], $uid]);
    }
    $stmt = $go -> prepare('SELECT `bolts`,`rubles`,`repute`,`energy`,`max_energy`,`hp`,`max_hp`, `exp`, `level` FROM `users` WHERE `id` = ? LIMIT 1');
    $stmt -> execute([$uid]);
    $top = $stmt -> fetch();
    ?>
    <div class="header">
      <span class="pull-right" style="text-align: right;">
        <img src="/imgs/hp.png" width="12px" /> <?php echo $top['hp'];?>/<?php echo $top['max_hp'];?><br/>
        <img src="/imgs/energy.png" width="12px" /> <?php echo $top['energy'];?>/<?php echo $top['max_energy'];?>
      </span>
      <img src="/imgs/repute.png" width="12px" /> <?php echo numb($top['repute']);?><br/>
      <img src="/imgs/bolts.png" width="12px" /> <?php echo numb($top['bolts']);?> 
      <img src="/imgs/ruble.png" width="10px" /> <?php echo numb($top['rubles']);?>
    </div>
    <div class="exp-block-mini">
      <div class="exp-mini"><div style="width: <?php echo 100 * $top['exp']/$level[$top['level']+1];?>%;" class="exp-line-mini"></div></div>
    </div>
    <?php
    if (isset($_SESSION['success']))
    {
      show_error($_SESSION['success']);
      unset($_SESSION['success']);
    }
    if (isset($_SESSION['error']))
    {
      show_error($_SESSION['error']);
      unset($_SESSION['error']);
    }
  }
