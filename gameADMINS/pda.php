<?php
include 'data/base.php';
user();
switch ($act)
{
default:
  $title = 'PDA';
  include 'data/head.php';
  ?>
  <div style="margin: 5px;">
    <a href="/pda/friends" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/friends.png"></td>
          <td class="attack-text">
            Друзья<br/>
            <small>
              Список ваших друзей
            </small>
          </td>
        </tr>
      </table>
    </a>
    <a href="/pda/sms" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/mail.png"></td>
          <td class="attack-text">
            Сообщения<br/>
            <small>
              Общение с другими сталкерами
            </small>
          </td>
        </tr>
      </table>
    </a>
    <a href="/pda/notify" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/notify.png"></td>
          <td class="attack-text">
            Уведомления<br/>
            <small>
              Игровые уведовления
            </small>
          </td>
        </tr>
      </table>
    </a>
    <a href="/pda/settings" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
          <td class="attack-text">
            Настройки<br/>
            <small>
              Ваши настройки
            </small>
          </td>
        </tr>
      </table>
    </a>
  </div>
  <?php
break;

case 'notify':
  $title = 'Уведомления';
  include 'data/head.php';
  $pages = new Paginator(10, 'page');
  $stmt = $go -> prepare('SELECT * FROM `notify` WHERE `id_user` = ?');
  $stmt -> execute([$uid]);
  $total = $stmt -> rowCount();
  $pages -> set_total($total);

  if ($total > 0)
  {
    $stmt = $go -> prepare('SELECT * FROM `notify` WHERE `id_user` = ? ORDER BY `time` DESC '.$pages -> get_limit());
    $stmt -> execute([$uid]);
    $get = $stmt -> fetchAll();

    $stmt = $go -> prepare('UPDATE `notify` SET `view` = ? WHERE `id_user` = ?');
    $stmt -> execute([1, $uid]);

    foreach ($get as $notify)
    {
      echo '<div class="fights fights-about '.($notify['view'] == 0 ? 'fights-orange-link':null).'">['.date('H:i', $notify['time']).'] '.$notify['note'].' </div>';
    }
    if ($total > 10) echo $pages -> page_links();
  }
  else
  {
    show_error('Уведомления отсутствуют.');
  }
  echo '<div class="fights-link" style="margin: 5px;"><a href="/pda/">Вернуться в PDA</a></div>';
break;

case 'friends':
  $title = 'Друзья';
  include 'data/head.php';
  user();
  ?>
  <div style="margin: 5px;">
    <div class="grid fights-link">
      <div class="six columns ln">
        <a href="/pda/friends">Друзья</a>
      </div>
      <div class="six columns">
        <a href="/pda/friends/request">Заявки</a>
      </div>
    </div>
  </div>
  <?
  if (isset($_GET['request']))
  {
    if (isset($_GET['add']))
    {
      if (empty($_GET['add'])) show_error('Выберите игрока, которого хотите добавить.');
      elseif (!is_numeric($_GET['add'])) show_error('Ошибка в выборе игрока.');
      else
      {
        $stmt = $go -> prepare('SELECT * FROM `friends` WHERE `id_friend` = ? and `id_user` = ? LIMIT 1');
        $stmt -> execute([$uid, abs(intval($_GET['add']))]);
        $fetch = $stmt -> fetch();

        if (!isset($fetch['id'])) show_error('Этот игрок не отправлял вам заявку.');
        elseif ($fetch['id_user'] == $uid) show_error('Ошибка.');
        elseif ($fetch['request'] == '1') show_error('Этот игрок уже есть у вас в друзьях.');
        else
        {
          $stmt = $go -> prepare('UPDATE `friends` SET `request` = ? WHERE `id` = ?');
          $stmt -> execute(['1', $fetch['id']]);

          $note = '<a href="/id/'.$uid.'">'.$u['login'].'</a> принял вашу заявку в друзья.';
          $stmt = $go -> prepare('INSERT INTO `notify` (`note`, `time`, `id_user`) VALUES (?, ?, ?)');
          $stmt -> execute([$note, time(), $fetch['id_user']]);

          $_SESSION['success'] = 'Заявка была принята.';
          die(header('Location: /pda/friends/request'));
        }
      }
    }
    elseif (isset($_GET['remove']))
    {
      if (empty($_GET['remove'])) show_error('Выберите игрока, которого хотите добавить.');
      elseif (!is_numeric($_GET['remove'])) show_error('Ошибка в выборе игрока.');
      else
      {
        $stmt = $go -> prepare('SELECT * FROM `friends` WHERE (`id_user` = ? or `id_friend` = ?) and (`id_user` = ? or `id_friend` = ?) LIMIT 1');
        $stmt -> execute([$uid, $uid, abs(intval($_GET['remove'])), abs(intval($_GET['remove']))]);
        $fetch = $stmt -> fetch();

        if (!isset($fetch['id'])) show_error('Этот игрок не отправлял вам заявку.');
        elseif ($fetch['request'] == '1') show_error('Этот игрок уже есть у вас в друзьях.');
        else
        {
          $stmt = $go -> prepare('DELETE FROM `friends` WHERE `id` = ?');
          $stmt -> execute([$fetch['id']]);

          $note = '<a href="/id/'.$uid.'">'.$u['login'].'</a> отклонил вашу заявку в друзья.';
          $stmt = $go -> prepare('INSERT INTO `notify` (`note`, `time`, `id_user`) VALUES (?, ?, ?)');
          $stmt -> execute([$note, time(), $fetch['id_user']]);

          $_SESSION['success'] = 'Заявка была отклонена.';
          die(header('Location: /pda/friends/request'));
        }
      }
    }
    $pages = new Paginator(10, 'page');
    $stmt = $go -> prepare('SELECT * FROM `friends` WHERE `id_friend` = ? and `request` = ?');
    $stmt -> execute([$uid,'0']);
    $total = $stmt -> rowCount();
    $pages -> set_total($total);

    if ($total > 0)
    {
      $stmt = $go -> prepare('SELECT * FROM `friends` WHERE `id_friend` = ? and `request` = ? ORDER BY `id` DESC '.$pages -> get_limit());
      $stmt -> execute([$uid, '0']);
      $get = $stmt -> fetchAll();

      foreach ($get as $friend)
      {
        if ($friend['id_user'] == $uid) $show = 'id_friend';
          else $show = 'id_user';
        $stmt = $go -> prepare('SELECT * FROM `users` WHERE `id` = ?');
        $stmt -> execute([$friend[$show]]);
        $us = $stmt -> fetch();
        ?>
        <div class="fights fights-about">
          › <?php echo show_user($friend[$show]);?> [ <a href="?add=<?php echo $friend[$show];?>">Принять</a> / <a href="?remove=<?php echo $friend[$show];?>">Отклонить</a> ]<br/>
          <?php echo $us['level'];?> ур. / <?php echo numb($us['repute']);?> репутации
        </div>
        <?php
      }
      if ($total > 10) echo $pages -> page_links();
    }
    else
    {
      show_error('Заявки отсутствуют.');
    }
    echo '<div class="fights-link" style="margin: 5px;"><a href="/pda/">Вернуться в PDA</a></div>';
  }
  else
  {
    $pages = new Paginator(10, 'page');
    $stmt = $go -> prepare('SELECT * FROM `friends` WHERE (`id_user` = ? or `id_friend` = ?) and `request` = ?');
    $stmt -> execute([$uid,$uid,'1']);
    $total = $stmt -> rowCount();
    $pages -> set_total($total);

    if ($total > 0)
    {
      $stmt = $go -> prepare('SELECT * FROM `friends` WHERE (`id_user` = ? or `id_friend` = ?) and `request` = ? ORDER BY `id` DESC '.$pages -> get_limit());
      $stmt -> execute([$uid, $uid, '1']);
      $get = $stmt -> fetchAll();

      echo '<div style="margin: 5px;">';
      foreach ($get as $friend)
      {
        if ($friend['id_user'] == $uid) $show = 'id_friend';
          else $show = 'id_user';
        $stmt = $go -> prepare('SELECT * FROM `users` WHERE `id` = ?');
        $stmt -> execute([$friend[$show]]);
        $us = $stmt -> fetch();
        ?>
        <a href="/id/<?php echo $us['id'];?>" class="weapon">
          <table width="100%">
            <tr>
              <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/friends.png"></td>
              <td class="attack-text">
                <?php echo $us['login'];?><br/>
                <small>
                  <?php echo $us['level'];?> ур. / <?php echo numb($us['repute']);?> репутации
                </small>
              </td>
            </tr>
          </table>
        </a>
        <?php
      }
      echo '</div>';
      if ($total > 10) echo $pages -> page_links();
    }
    else
    {
      show_error('У вас еще нет друзей.');
    }
    echo '<div class="fights-link" style="margin: 5px;"><a href="/pda/">Вернуться в PDA</a></div>';
  }
break;
/*
* TODO: Messages
*/
case 'sms':
  $title = 'Диалоги';
  include 'data/head.php';

  $pages = new Paginator('10', 'page');
  $stmt = $go -> prepare('SELECT `id` FROM `dialogs` WHERE `id_user` = ?');
  $stmt -> execute([$uid]);
  $total = $stmt -> rowCount();

  if ($total > 0)
  {
    $pages -> set_total($total);
    $stmt = $go -> prepare('SELECT * FROM `dialogs` WHERE `id_user` = ? ORDER BY `id` DESC '.$pages -> get_limit());
    $stmt -> execute([$uid]);
    $data = $stmt -> fetchAll();

    echo '<div style="margin: 5px;">';
    foreach ($data as $messages)
    {
      $stmt = $go -> prepare('SELECT `id`, `text`,`read`, `user_id` FROM `messages` WHERE `id_user` = ?  AND `user_id` = ? OR `user_id` = ? AND `id_user` = ?  ORDER BY `time` DESC LIMIT 1');
      $stmt -> execute([$uid, $messages['user_id'], $uid, $messages['user_id']]);
      $message = $stmt -> fetch();
      ?>
      <a href="/pda/sms/im/<?php echo $messages['user_id'];?>" class="sms">
        <div class="sms-name"><?php echo show_user_information($messages['user_id'], 'login');?></div>
          <?php
          if (!isset($message['id'])) echo '<div class="sms-message">Нет сообщений</div>';
          elseif ($message['user_id'] != $uid) echo '<div class="sms-message '.($message['read'] == 1 ? 'new':NULL).'">Вы: '.($message['text']).'</div>';
          elseif ($message['user_id'] == $uid) echo '<div class="sms-message '.($message['read'] == 1 ? 'new':NULL).'">'.($message['text']).'</div>';
          else echo '<div class="sms-message">'.text($message['text']).'</div>';
          ?>
      </a>
      <?
    }
    echo '</div>';
    if ($total > 10) echo $pages -> page_links();
  }
  else
  {
    echo show_error('У вас нет диалогов с другими сталкерами.');;
  }
  echo '<div class="fights-link" style="margin: 2px 5px"><a href="/pda">Назад в PDA</a></div>';
break;

case 'dialog';
  $stmt = $go -> prepare('SELECT * FROM `users` WHERE `id` = ? LIMIT 1');
  $stmt -> execute([$ids]);
  $dialog = $stmt -> fetch();

  if (!isset($dialog['id']) and $dialog['id'] == $uid) die(header('Location: /pda/sms/'));
  else
  {
    $title = 'Диалог с '.$dialog['login'];
    include 'data/head.php';

    $stmt = $go -> prepare('SELECT `id` FROM `dialogs` WHERE `user_id` = ? AND `id_user` = ?');
    $stmt -> execute([$dialog['id'], $uid]);
    $count['dialog'] = $stmt -> rowCount();

    $stmt = $go -> prepare('SELECT `id` FROM `messages` WHERE `id_user` = ? AND `user_id` = ? AND `read` = ?');
    $stmt -> execute([$dialog['id'], $uid, 1]);
    $count['read'] = $stmt -> rowCount();

    if ($count['dialog'] == 0)
    {
      $stmt = $go -> prepare('INSERT INTO `dialogs` (`id_user`, `user_id`, `time`, `last_time`) VALUES (?, ?, ?, ?)');
      $stmt -> execute([$uid, $dialog['id'], time(), time()]);
      $stmt = $go -> prepare('INSERT INTO `dialogs` (`id_user`, `user_id`, `time`, `last_time`) VALUES (?, ?, ?, ?)');
      $stmt -> execute([$dialog['id'], $uid, time(), time()]);
    }
    if ($count['read'] > 0)
    {
      $stmt = $go -> prepare('UPDATE `messages` SET `read` = ? WHERE `id_user` = ? AND `user_id` = ?');
      $stmt -> execute([0, $dialog['id'], $uid]);
    }
    if (isset($_REQUEST['next']))
    {
      if ($u['level'] < 5) $error[] = 'Писать сообщения возможно только с 5 уровня.';
      elseif (show_user_information($dialog['id'], 'level') < 5) $error[] = 'Писать сообщения возможно только сталкерам, которые достигли 5 уровня.';
      elseif (empty($_POST['message'])) $error[] = 'Введите текст сообщения';
      elseif (strlen($_POST['message']) < 2) $error[] = 'Сообщение не может быть короче 2 символов.';
      elseif (strlen($_POST['message']) > 320) $error[] = 'Сообщение не может быть длинее 320 символов.';

      if (empty($error))
      {
        $stmt = $go -> prepare('INSERT INTO `messages` (`text`, `id_user`, `user_id`, `time`, `read`) VALUES (?, ?, ?, ?, ?)');
        $stmt -> execute([$_POST['message'], $uid, $dialog['id'], time(), 1]);
        die(header('Location: /pda/sms/im/'.$dialog['id']));
      }
      else
      {
        echo show_error($error);
      }
    }
    ?>
    <div class="dialog">
      <h1 class="human">Вы</h1>
      <p>
        <form method="post">
          Введите сообщение <small>[Мин.:2/Макс.:320]</small>:<br>
          <textarea name="message" value="Введите текст..."></textarea>
          <input type="submit" name="next" value="Отправить сообщение">
        </form>
      </p>
    </div>
    <?php
    $pages = new Paginator('10', 'page');
    $stmt = $go -> prepare('SELECT `id` FROM `messages` WHERE `id_user` = ? AND `user_id` = ? OR `id_user` = ? AND `user_id` = ?');
    $stmt -> execute([$uid, $dialog['id'], $dialog['id'], $uid]);
    $total = $stmt -> rowCount();

    if ($total > 0)
    {
      $pages -> set_total($total);
      $stmt = $go -> prepare('SELECT * FROM `messages` WHERE `id_user` = ? AND `user_id` = ? OR `id_user` = ? AND `user_id` = ?  ORDER BY `time` DESC '.$pages -> get_limit());
      $stmt -> execute([$uid, $dialog['id'], $dialog['id'], $uid]);
      $data = $stmt -> fetchAll();

      foreach ($data as $sms)
      {
        ?>
        <div class="dialog">
          <h1 class="chat"><?php echo show_user($sms['id_user']);?></h1>
          <p>
            › <?php echo nl2br($sms['text']);?>
          </p>
          <div class="small">Время › <?php echo date('j.m.Y в H:i:s', $sms['time']);?></div>
        </div>
        <?
      }
      if ($total > 10) echo $pages -> page_links();
    }
    else
    {
      echo show_error('Сообщений нет. Напишите первым!');
    }
  }
break;

case 'settings':
  $title = 'Настройки';
  include 'data/head.php';
  ?>
  <div style="margin: 5px;">
    <a href="/pda/settings/avatar" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/friends.png"></td>
          <td class="attack-text">
            Аватар<br/>
            <small>
              Изображение персонажа
            </small>
          </td>
        </tr>
      </table>
    </a>
  </div>
  <div class="fights-link" style="margin: 5px"><a href="/pda">Назад в PDA</a></div>
  <?php
break;
case 'avatar':
  $title = 'Изображение персонажа';
  include 'data/head.php';
  user();

  $stmt = $go -> prepare('SELECT * FROM `users_data` WHERE `id_user` = ?');
  $stmt -> execute([$uid]);
  $fetch = $stmt -> fetch();

  if ($fetch['head'] != NULL OR $fetch['eyes'] OR $fetch['color'] OR $fetch['beard'] OR $fetch['hair'])
  {
    ?>
    <div class="dialog">
      <h1 class="human">Шрам</h1>
      <p>
        › Тут не клиника пластической хирургии, 2 раза внешность не выбирается.
      </p>
    </div>
    <div class="fights-link" style="margin: 2px 5px"><a href="/pda/settings">Назад в настройки</a></div>
    <?php
    include 'data/foot.php';
    die();
  }

  if (empty($_SESSION['head'])) $_SESSION['head'] = 0;
  if (empty($_SESSION['eyes'])) $_SESSION['eyes'] = 0;
  if (empty($_SESSION['color'])) $_SESSION['color'] = 0;
  if (empty($_SESSION['beard'])) $_SESSION['beard'] = 0;
  if (empty($_SESSION['hair'])) $_SESSION['hair'] = 0;

  $ch = ['0','1','2','3','4','5'];
  $ce = ['0','1','2','3','4'];
  $tb = ['0','1','2'];
  $cha = ['0','1','2'];
  $tha = ['0','1','2','3','4','5','6','7','8'];

  if (isset($_GET['head']) and array_key_exists($_GET['head'], $ch))
  {
    $_SESSION['head'] = $_GET['head'];
  }
  if (isset($_GET['eyes']) and array_key_exists($_GET['eyes'], $ce))
  {
    $_SESSION['eyes'] = $_GET['eyes'];
  }
  if (isset($_GET['color']) and array_key_exists($_GET['color'], $cha))
  {
    $_SESSION['color'] = $_GET['color'];
  }
  if (isset($_GET['beard']) and array_key_exists($_GET['beard'], $tb))
  {
    $_SESSION['beard'] = $_GET['beard'];
  }
  if (isset($_GET['hair']) and array_key_exists($_GET['hair'], $tha))
  {
    $_SESSION['hair'] = $_GET['hair'];
  }

  if (isset($_GET['apply']))
  {
    if (!array_key_exists($_SESSION['head'], $ch)) $error[] = 'Неправильно выбраны - "Голова".';
    if (!array_key_exists($_SESSION['eyes'], $ce)) $error[] = 'Неправильно выбраны - "Глаза".';
    if (!array_key_exists($_SESSION['color'], $cha)) $error[] = 'Неправильно выбраны - "Цвет волос".';
    if (!array_key_exists($_SESSION['beard'], $tb)) $error[] = 'Неправильно выбраны - "Борода".';
    if (!array_key_exists($_SESSION['hair'], $tha)) $error[] = 'Неправильно выбраны - "Волосы".';

    if (empty($error))
    {
      if (!isset($fetch['id_user']))
      {
        $stmt = $go -> prepare('INSERT INTO `users_data` (`id_user`, `head`, `eyes`, `color`, `beard`, `hair`) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt -> execute([$uid, $_SESSION['head'], $_SESSION['eyes'], $_SESSION['color'], $_SESSION['beard'], $_SESSION['hair']]);
      }
      else
      {
        $stmt = $go -> prepare('UPDATE `users_data` SET `head` = ?, `eyes` = ?, `color` = ?, `beard` = ?, `hair` = ? WHERE `id_user` = ?');
        $stmt -> execute([$_SESSION['head'], $_SESSION['eyes'], $_SESSION['color'], $_SESSION['beard'], $_SESSION['hair'], $uid]);
      }
      unset($_SESSION['head']); unset($_SESSION['eyes']); unset($_SESSION['color']); unset($_SESSION['beard']); unset($_SESSION['hair']);
      $_SESSION['success'] = 'Внешность успешно изменена.';
      die(header('Location: /id/'.$uid));
    }
    else
    {
      echo show_error($error);
    }
  }
  ?>
  <div class="cl center">
    <strong>Выберите внешность</strong><br/>
    <small>Переключайте элементы своего лица</small>
  </div>
  <div class="fights fights-about center">
    <div class="grid">
      <div class="six columns">
        <div class="fights">
          <img src="/ava/<?php echo $_SESSION['head']?>/<?php echo $_SESSION['eyes']?>/<?php echo $_SESSION['beard']?>/<?php echo $_SESSION['color']?>/<?php echo $_SESSION['hair']?>/">
        </div>
      </div>
      <div class="six columns">
        <div class="fights fights-about">
          <strong>Цвет кожи</strong><br/>
          <a href="?head=<?php echo (array_key_exists($_SESSION['head']-1,$ch) ? $_SESSION['head']-1:end($ch));?>">[ < ]</a> <strong style="color: #ffa200;">[ <?php echo $_SESSION['head'];?> ]</strong> <a href="?head=<?php echo (array_key_exists($_SESSION['head']+1,$ch) ? $_SESSION['head']+1:reset($ch));?>">[ > ]</a><hr/>
          <strong>Цвет глаз</strong><br/>
          <a href="?eyes=<?php echo (array_key_exists($_SESSION['eyes']-1,$ce) ? $_SESSION['eyes']-1:end($ce));?>">[ < ]</a> <strong style="color: #ffa200;">[ <?php echo $_SESSION['eyes'];?> ]</strong> <a href="?eyes=<?php echo (array_key_exists($_SESSION['eyes']+1,$ce) ? $_SESSION['eyes']+1:reset($ce));?>">[ > ]</a><hr/>
          <strong>Цвет волос</strong><br/>
          <a href="?color=<?php echo (array_key_exists($_SESSION['color']-1,$cha) ? $_SESSION['color']-1:end($cha));?>">[ < ]</a> <strong style="color: #ffa200;">[ <?php echo $_SESSION['color'];?> ]</strong> <a href="?color=<?php echo (array_key_exists($_SESSION['color']+1,$cha) ? $_SESSION['color']+1:reset($cha));?>">[ > ]</a><hr/>
          <strong>Борода</strong><br/>
          <a href="?beard=<?php echo (array_key_exists($_SESSION['beard']-1,$tb) ? $_SESSION['beard']-1:end($tb));?>">[ < ]</a> <strong style="color: #ffa200;">[ <?php echo $_SESSION['beard'];?> ]</strong> <a href="?beard=<?php echo (array_key_exists($_SESSION['beard']+1,$tb) ? $_SESSION['beard']+1:reset($tb));?>">[ > ]</a><hr/>
          <strong>Волосы</strong><br/>
          <a href="?hair=<?php echo (array_key_exists($_SESSION['hair']-1,$tha) ? $_SESSION['hair']-1:end($tha));?>">[ < ]</a> <strong style="color: #ffa200;">[ <?php echo $_SESSION['hair'];?> ]</strong> <a href="?hair=<?php echo (array_key_exists($_SESSION['hair']+1,$tha) ? $_SESSION['hair']+1:reset($tha));?>">[ > ]</a>
        </div>
      </div>
    </div>
    <div class="fights-link fights-orange-link" style="margin: 5px"><a href="?apply">Выбрать эту внешность</a></div>
  </div>
  <div class="fights-link" style="margin: 5px"><a href="/pda/settings">Назад в настройки</a></div>
  <?php
break;
}
include 'data/foot.php';
?>