<?php
include '../data/base.php';
access(1);

switch ($act)
{
default:
  $title = 'Управление блокировками';
  include '../data/head.php';
  ?>
  <div style="margin: 5px;">
    <a href="/access/ban/create" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
          <td class="attack-text">
            Заблокировать<br/>
            <small>
              Новая блокировка
            </small>
          </td>
        </tr>
      </table>
    </a>
    <a href="/access/ban/list" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
          <td class="attack-text">
            Действующие<br/>
            <small>
              Все блокировки
            </small>
          </td>
        </tr>
      </table>
    </a>
  </div>
  <div class="fights-link" style="margin: 5px"><a href="/access/">Панель управления</a></div>
  <?php
break;
case 'create':
  $title = 'Новая блокировка';
  include '../data/head.php';
  if (isset($_REQUEST['banned']))
  {
    $time = [3600,86400,(86400*7),(86400*7*4),(86400*7*4*6),(86400*7*4*12)];
    $post = [
      'id' => abs(intval($_POST['id_user'])),
      'reason' => trim($_POST['reason']),
      'method' => abs(intval($_POST['method'])),
      'time' => abs(intval($_POST['time']))
    ];

    $stmt = $go -> prepare('SELECT `id`, `access` FROM `users` WHERE `id` = ? LIMIT 1');
    $stmt -> execute([$post['id']]);
    $banned = $stmt -> fetch();

    if (!isset($banned['id'])) $error[] = 'Игрока с таким ID не существует.';
    elseif ($banned['access'] >= $u['access']) $error[] = 'Нельзя заблокировать сотрудника администрации высшей или вашей должности.';
    else
    {
      $stmt = $go -> prepare('SELECT * FROM `banned` WHERE `id_user` = ? LIMIT 1');
      $stmt -> execute([$post['id']]);
      $ban = $stmt -> fetch();

      if (isset($ban['id']) and ($ban['method'] == 1 or $ban['time_ban'] > time())) $error[] = 'Этот игрок уже заблокирован';
      else
      {
        if ($post['method'] != 0 and $post['method'] != 1) $error[] = 'Ошибка в выборе типа блокировки';
        elseif (!array_key_exists($post['time'], $time) and $post['method'] == 0) $error[] = 'Ошибка в выборе времени блокировки';
      }
    }

    if (empty($error))
    {
      if ($post['method'] == 0) $timeban = time() + $time[$post['time']];
      else $timeban = NULL;
      $stmt = $go -> prepare('INSERT INTO `banned` (`id_user`, `id_who`, `reason`, `method`, `time_ban`, `time`) VALUES (?, ?, ?, ?, ?, ?)');
      $stmt -> execute([$post['id'], $uid, $post['reason'], $post['method'], $timeban, time()]);
      $_SESSION['success'] = 'Пользователь успешно заблокирован.';
      die(header('Location: /access/ban/create'));
    }
    else
    {
      echo show_error($error);
    }
  }
  ?>
  <div class="fights fights-about">
    <form method="POST">
      ID игрока:<br/>
      <input type="text" name="id_user" value="<?php echo (isset($_GET['id']) ? intval($_GET['id']):NULL);?>" placeholder="Введите ID..." required/>
      Причина блокировки:<br/>
      <textarea name="reason" placeholder="Введите причину блокировки" required></textarea>
      Тип блокировки:<br/>
      <select name="method">
        <option value="0">Временная блокировка</option>
        <option value="1">Вечная блокировка</option>
      </select>
      Срок (если блокировка на время):
      <select name="time">
        <option value="0">Час</option>
        <option value="1">Сутки</option>
        <option value="2">Неделя</option>
        <option value="3">Месяц</option>
        <option value="4">Полгода</option>
        <option value="5">Год</option>
      </select>
      <input type="submit" name="banned" value="Заблокировать">
    </form>
  </div>
  <div class="fights-link" style="margin: 5px;"><a href="/access/ban">Вернуться к блокировкам</a></div>
  <?php
break;
case 'list':
  $title = 'Список заблокированных';
  include '../data/head.php';

  $pages = new Paginator(10, 'page');
  $stmt = $go -> prepare('SELECT `id` FROM `banned` WHERE `method` = ? or (`method` = ? and `time_ban` > ?)');
  $stmt -> execute(['1', '0', time()]);
  $total = $stmt -> rowCount();
  $pages -> set_total($total);
  if ($total > 0)
  {
    $stmt = $go -> prepare('SELECT `id`, `id_user`, `method` FROM `banned` WHERE `method` = ? or (`method` = ? and `time_ban` > ?) ORDER BY `time` DESC '.$pages -> get_limit());
    $stmt -> execute(['1', '0', time()]);
    $get = $stmt -> fetchAll();

    if ($pages->_page == 1) $place = 0;
      else $place = (10 * $pages->_page) - 10;
    echo '<div style="margin: 5px;">';
    foreach($get as $ban)
    {
      $place += 1;
      ?>
      <a href="/access/ban/view/<?php echo $ban['id'];?>" class="weapon">
        <table width="100%">
          <tr>
            <td style="white-space:nowrap;width: 100%;" class="attack-text">
              <?php echo show_user_information($ban['id_user'],'login');?><br/>
              <small>
                <?php echo ($ban['method'] == 1 ? 'Вечная блокировка':'Временная блокировка');?>
              </small>
            </td>
            <td class="attack-icon" valign="top"><?php echo ($place < 10 ? '0':NULL).$place;?></td>
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
    echo show_error('Игроки не найдены.');
  }
  echo '<div class="fights-link" style="margin: 5px;"><a href="/access/ban">Вернуться к блокировкам</a></div>';
break;
case 'view':
  $title = 'Просмотр блокировки';
  include '../data/head.php';
  $stmt = $go -> prepare('SELECT * FROM `banned` WHERE `id` = ? LIMIT 1');
  $stmt -> execute([$ids]);
  $ban = $stmt -> fetch();

  if (!isset($ban['id']))
  {
    $_SESSION['error'] = 'Такой блокировки не существует.';
    die(header('Location: /access/ban/list'));
  }
  else
  {
    if (isset($_GET['unban']) and $u['access'] == 3)
    {
      $stmt = $go -> prepare('DELETE FROM `banned` WHERE `id` = ?');
      $stmt -> execute([$ids]);
      $_SESSION['success'] = 'Пользователь ['.show_user_information($ban['id_user'], 'login').'] был разбанен.';
      die(header('Location: /access/ban/list'));
    }
    ?>
    <div class="dialog">
      <h1 class="chat"><?php echo show_user($ban['id_user']);?> был забанен <?php echo show_user($ban['id_who']);?></h1>
      <div class="dialog-p" style="margin: 5px 0;">
        Причина › <?php echo $ban['reason'];?><br/>
        Тип › <?php echo ($ban['method'] == 1 ? 'вечная блокировка':'временная блокировка');?><br/>
        <div class="list">
          Дата окончания<br/>
          <?php echo ($ban['method'] == 0 ? ($ban['time_ban'] > time() ? 'через '.downcounter(date('Y-m-j H:i:s', $ban['time_ban'])):date('j.m.Y в H:i:s', $ban['time_ban'])):'Никогда');?>
        </div><hr/>
        Дата блокировки › <?php echo date('j.m.Y в H:i', $ban['time']);?>
      </div>
    </div>
    <?php echo ($u['access'] == 3 ? '<div class="fights-link" style="margin: 2px 5px;"><a href="?unban">Разбанить</a></div>':NULL);?>
    <div class="fights-link" style="margin: 2px 5px;"><a href="/access/ban/list">Вернуться к блокировкам</a></div>
    <?php
  }
break;
}
include '../data/foot.php';