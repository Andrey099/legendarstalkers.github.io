<?php
include 'data/base.php';
user();

switch ($act)
{

default:
$title = 'Группировки';
include 'data/head.php';
$gp = $grp -> inGroups($uid);
?>
<div class="col margin-left-right">Рейтинг группировок</div>
<div class="fights-link" style="margin: 5px;">
  <?php echo ($gp == 0 ? '<a href="/groups/create">Основать группировку</a>':'<a href="/groups/'.$gp['group']['id'].'">Моя группировка</a>');?>
</div>
<?php

$pages = new Paginator(10, 'page');
$stmt = $go -> prepare('SELECT `id` FROM `groups`');
$stmt -> execute([]);
$total = $stmt -> rowCount();
$pages -> set_total($total);
if ($total > 0)
{
  $stmt = $go -> prepare('SELECT `id`, `name`, `level`, (SELECT COUNT(*) FROM `groups_users` WHERE `id_group` = `groups`.`id` and `accept` = ?) AS `total` FROM `groups` ORDER BY `level` DESC '.$pages -> get_limit());
  $stmt -> execute(['1']);
  $get = $stmt -> fetchAll();
  foreach ($get as $groups)
  {
    ?>
    <div class="fights fights-about">
      <strong><a href="/groups/<?php echo $groups['id'];?>"><?php echo $groups['name'];?></a></strong> <span class="pull-right label"><?php echo $groups['level'];?> ур.</span><br/>
      Участников: <?php echo $groups['total'];?> из 50
    </div>
    <?php
  }
  if ($total > 10) echo $pages -> page_links();
}
else
{
  show_error('Группировки не найдены.');
}
break;

case 'view':
$stmt = $go -> prepare('SELECT * FROM `groups` WHERE `id` = ? LIMIT 1');
$stmt -> execute([$ids]);
$g = $stmt -> fetch();
if (!isset($g['id'])) die(header('Location: /groups'));
$title = 'Просмотр группировки';
include 'data/head.php';
$gp = $grp -> inGroups($uid);
?>
<div class="fights fights-about center" style="padding: 5px;">
  <div class="col" style="margin-bottom: 5px;">
    <strong><?php echo $g['name'];?></strong>
  </div>
  <?php echo $g['about'];?>
</div>
<div class="fights fights-about">
  › <img src="/imgs/level.png" width="12px" alt="*"> <?php echo $g['level'];?> уровень<br/>
  › <img src="/imgs/repute.png" width="12px" alt="*"> <?php echo declension($g['exp'], ['репутация', 'репутации', 'репутации']);?><hr/>
  Лидер › <?php echo show_user($g['id_lider']);?><br/>
  Участников › <?php echo $grp -> usersGroups($ids);?> из <?php echo $g['max_users'];?><br/>
  Дата создания › <?php echo date('j.m.Y в H:i:s', $g['dateCreate']);?><br/>
  <?php if ($gp != 0 and $gp['group']['id'] == $ids): ?>
  Болтов в схроне › <img src="/imgs/bolts.png" width="12px" alt="*"> <?php echo numb($gp['group']['bolts']);?> (<?php echo numb($gp['user']['donate_bolts']);?>)<br/>
  Рублей в схроне › <img src="/imgs/ruble.png" width="10px" alt="*"> <?php echo numb($gp['group']['rubles']);?> (<?php echo numb($gp['user']['donate_rubles']);?>)<br/>
  <?php endif;?>
</div>
<div style="margin: 5 4px;">
  <div class="grid fights-link" style="border: none;">
    <div class="six columns">
      <a style="margin: 1px;border: 1px solid #31333a;" href="/groups/<?php echo $g['id'];?>/list">Участники</a>
    </div>
    <div class="six columns">
      <a style="margin: 1px;border: 1px solid #31333a;" href="/groups/<?php echo $g['id'];?>/builds">Строения</a>
    </div>
    <?php
    if ($gp != 0 and $gp['group']['id'] == $ids)
    {
    ?>
    <div class="six columns">
      <a style="margin: 1px;border: 1px solid #31333a;" href="/groups/stash">Схрон</a>
    </div>
    <?php if ($gp['build']['fire'] == 1): ?>
    <div class="six columns">
      <a style="margin: 1px;border: 1px solid #31333a;" href="/groups/fire">Костер</a>
    </div>
    <?php endif;?>
    <?php if ($gp['user']['rank'] > 2): ?>
    <div class="six columns">
      <a style="margin: 1px;border: 1px solid #31333a;" href="/groups/logs">Логи</a>
    </div>
    <?php endif;?>
    <?php if ($gp['user']['rank'] == 4): ?>
    <div class="six columns">
      <a style="margin: 1px;border: 1px solid #31333a;" href="/groups/settings">Управление</a>
    </div>
    <?php endif;
    }
    ?>
  </div>
</div>
<?php
break;

case 'list':
$title = 'Список участников';
include 'data/head.php';
$stmt = $go -> prepare('SELECT `id`, `name` FROM `groups` WHERE `id` = ? LIMIT 1');
$stmt -> execute([$ids]);
$g = $stmt -> fetch();
if (!isset($g['id'])) die(header('Location: /groups'));

echo '<div class="col margin-left-right">'.$g['name'].'</div>';

$pages = new Paginator(10, 'page');
$stmt = $go -> prepare('SELECT `id` FROM `groups_users` WHERE `id_group` = ? and `accept` = ?');
$stmt -> execute([$ids, 1]);
$total = $stmt -> rowCount();
$pages -> set_total($total);
if ($total > 0)
{
  $gp = $grp -> inGroups($uid);
  // Commands
  if (isset($_GET['kick']))
  {
    if (empty($_GET['kick'])) show_error('Выберите игрока, которого желаете исключить.');
    elseif (!is_numeric($_GET['kick'])) show_error('Ошибка в запросе.');
    elseif ($gp == 0 or $gp['group']['id'] != $ids) show_error('Это не ваша группировка.');
    elseif ($gp['user']['rank'] < 3) show_error('У вас недостаточно прав для совершения этого действия.');
    else
    {
      $stmt = $go -> prepare('SELECT * FROM `groups_users` WHERE `id_user` = ? and `id_group` = ? and `accept` = ?');
      $stmt -> execute([$_GET['kick'], $ids, '1']);
      $kicked = $stmt -> fetch();

      if ($kicked == FALSE) show_error('Информация о таком игроке не найдена.');
      elseif ($kicked['id_user'] == $uid) show_error('Нельзя исключить самого себя.');
      elseif ($kicked['rank'] >= $gp['user']['rank']) show_error('Нельзя исключить игрока с вашим званием или выше.');
      else
      {
        if (isset($_GET['kick']) and isset($_GET['ok']))
        {
          $log = '"'.$u['login'].'" исключил вас из группировки "'.$gp['group']['name'].'".';
          $stmt = $go -> prepare('INSERT INTO `notify` (`id_user`, `note`, `time`) VALUES (?, ?, ?)');
          $stmt -> execute([$kicked['id_user'], $log, time()]);

          $logs = 'исключил из группировки';
          $stmt = $go -> prepare('INSERT INTO `groups_logs` (`id_user`, `id_group`, `id_other`, `text`, `time`, `types`) VALUES (?, ?, ?, ?, ?, ?)');
          $stmt -> execute([$uid, $gp['group']['id'], $kicked['id_user'], $logs, time(), 'user']);

          $stmt = $go -> prepare('DELETE FROM `groups_users` WHERE `id_user` = ? and `accept` = ?');
          $stmt -> execute([$kicked['id_user'], '1']);
          show_error('Игрок успешно исключен из группировки.');
        }
        else
        {
          ?>
          <div class="fights fights-about center">
            Вы действительно хотите исключить игрока <?php echo show_user($kicked['id_user']);?>?
            <div style="margin: 5px 0 0 0;">
              <div class="grid fights-link">
                <div class="six columns ln">
                  <a href="/groups/<?php echo $ids;?>/list?kick=<?php echo $kicked['id_user'];?>&ok">Исключить</a>
                </div>
                <div class="six columns">
                  <a href="/groups/<?php echo $ids;?>/list">Оставить</a>
                </div>
              </div>
            </div>
          </div>
          <?php
        }
      }
    }
  }
  elseif (isset($_GET['down']))
  {
    if (empty($_GET['down'])) show_error('Выберите игрока, которого желаете понизить.');
    elseif (!is_numeric($_GET['down'])) show_error('Ошибка в запросе.');
    elseif ($gp == 0 or $gp['group']['id'] != $ids) show_error('Это не ваша группировка.');
    elseif ($gp['user']['rank'] < 4) show_error('У вас недостаточно прав для совершения этого действия.');
    else
    {
      $stmt = $go -> prepare('SELECT * FROM `groups_users` WHERE `id_user` = ? and `id_group` = ? and `accept` = ?');
      $stmt -> execute([$_GET['down'], $ids, '1']);
      $down = $stmt -> fetch();

      if ($down == FALSE) show_error('Информация о таком игроке не найдена.');
      elseif ($down['id_user'] == $uid) show_error('Нельзя понизить самого себя.');
      elseif ($down['rank'] == 0) show_error('У этого игрока уже самое низкое звание.');
      else
      {
        if (isset($_GET['down']) and isset($_GET['ok']))
        {
          $stmt = $go -> prepare('UPDATE `groups_users` SET `rank` = `rank` - ? WHERE `id_user` = ? and `accept` = ?');
          $stmt -> execute([1, $down['id_user'], '1']);

          show_error('Игрок успешно понижен в звании.');
        }
        else
        {
          ?>
          <div class="fights fights-about center">
            Вы действительно хотите понизить игрока <?php echo show_user($down['id_user']);?>?
            <div style="margin: 5px 0 0 0;">
              <div class="grid fights-link">
                <div class="six columns ln">
                  <a href="/groups/<?php echo $ids;?>/list?down=<?php echo $down['id_user'];?>&ok">Понизить</a>
                </div>
                <div class="six columns">
                  <a href="/groups/<?php echo $ids;?>/list">Оставить</a>
                </div>
              </div>
            </div>
          </div>
          <?php
        }
      }
    }
  }
  elseif (isset($_GET['up']))
  {
    if (empty($_GET['up'])) show_error('Выберите игрока, которого желаете повысить.');
    elseif (!is_numeric($_GET['up'])) show_error('Ошибка в запросе.');
    elseif ($gp == 0 or $gp['group']['id'] != $ids) show_error('Это не ваша группировка.');
    elseif ($gp['user']['rank'] < 4) show_error('У вас недостаточно прав для совершения этого действия.');
    else
    {
      $stmt = $go -> prepare('SELECT * FROM `groups_users` WHERE `id_user` = ? and `id_group` = ? and `accept` = ?');
      $stmt -> execute([$_GET['up'], $ids, '1']);
      $up = $stmt -> fetch();

      if ($up == FALSE) show_error('Информация о таком игроке не найдена.');
      elseif ($up['id_user'] == $uid) show_error('Нельзя повысить самого себя.');
      elseif ($up['rank'] == 3) show_error('У этого игрока уже самое высокое звание.');
      else
      {
        if (isset($_GET['up']) and isset($_GET['ok']))
        {
          $stmt = $go -> prepare('UPDATE `groups_users` SET `rank` = `rank` + ? WHERE `id_user` = ? and `accept` = ?');
          $stmt -> execute([1, $up['id_user'], '1']);

          show_error('Игрок успешно повышен в звании.');
        }
        else
        {
          ?>
          <div class="fights fights-about center">
            Вы действительно хотите повысить игрока <?php echo show_user($up['id_user']);?>?
            <div style="margin: 5px 0 0 0;">
              <div class="grid fights-link">
                <div class="six columns ln">
                  <a href="/groups/<?php echo $ids;?>/list?up=<?php echo $up['id_user'];?>&ok">Повысить</a>
                </div>
                <div class="six columns">
                  <a href="/groups/<?php echo $ids;?>/list">Оставить</a>
                </div>
              </div>
            </div>
          </div>
          <?php
        }
      }
    }
  }
  $stmt = $go -> prepare('SELECT * FROM `groups_users` WHERE `id_group` = ? and `accept` = ? ORDER BY `rank` DESC '.$pages -> get_limit());
  $stmt -> execute([$ids, 1]);
  $get = $stmt -> fetchAll();

  foreach($get as $rating)
  {
    ?>
    <div class="fights fights-about">
      <?php echo show_user($rating['id_user']);?> / <?php echo $rank[$rating['rank']];?><br/>
      Репутация: <?php echo numb($rating['exp_all']);?> (сегодня: <?php echo numb($rating['exp_today']);?>)<br/>
      Вклад: <img src="/imgs/bolts.png" width="12px" alt="*"> <?php echo numb($rating['donate_bolts']);?> / <img src="/imgs/ruble.png" width="10px" alt="*"> <?php echo numb($rating['donate_rubles']);?><br/>
      Вступил: <?php echo date('j.m.Y в H:i:s', $rating['dateAdd']);?> <?php echo ($rating['invite'] != NULL ? '('.show_user($rating['invite']).')':NULL)?><br/>
      <?php if ($gp != 0 and $gp['group']['id'] == $ids and $gp['user']['rank'] > 2 and $rating['id_user'] != $uid and $rating['rank'] < $gp['user']['rank']):?>
        <a href="?kick=<?php echo $rating['id_user'];?>">Исключить</a> <?php echo ($gp['user']['rank'] == 4 && $rating['rank'] > 0 ? '/ <a href="?down='.$rating['id_user'].'">Понизить</a>':NULL);?> <?php echo ($gp['user']['rank'] == 4 && $rating['rank'] < 3 ? ' / <a href="?up='.$rating['id_user'].'">Повысить</a>':NULL);?>
      <?php endif;?>
    </div>
    <?php
  }
  if ($total > 10) echo $pages -> page_links();
}
else
{
  show_error('Нет участников.');
}
echo '<div class="fights-link" style="margin: 5px;"><a href="/groups/'.$g['id'].'">Назад к группировке</a></div>';
break;
case 'build':
$stmt = $go -> prepare('SELECT * FROM `groups` WHERE `id` = ? LIMIT 1');
$stmt -> execute([$ids]);
$g = $stmt -> fetch();
if (!isset($g['id'])) die(header('Location: /groups'));
$gp = $grp -> inGroups($uid);
$title = 'Строения группировки';
include 'data/head.php';
$barracks = [
  '1' => [
    'price' => 'bolts',
    'amount' => 1000,
    'members' => 1
  ],
  '2' => [
    'price' => 'rubles',
    'amount' => 30,
    'members' => 2
  ],
  '3' => [
    'price' => 'bolts',
    'amount' => 5000,
    'members' => 1
  ],
  '4' => [
    'price' => 'rubles',
    'amount' => 90,
    'members' => 2
  ],
  '5' => [
    'price' => 'bolts',
    'amount' => 15000,
    'members' => 1
  ],
  '6' => [
    'price' => 'rubles',
    'amount' => 180,
    'members' => 2
  ],
  '7' => [
    'price' => 'bolts',
    'amount' => 40000,
    'members' => 1
  ]
];
if (isset($_GET['fire']) and $gp != 0 and $gp['group']['id'] == $ids and $gp['user']['rank'] == 4)
{
  if ($g['fire'] == 1) show_error('Костер уже построен.');
  else
  {
    if (isset($_GET['fire']) and isset($_GET['ok']))
    {
      if ($g['rubles'] < 100) show_error('В схроне группировки недостаточно рублей для постройки.');
      else
      {
        $stmt = $go -> prepare('UPDATE `groups` SET `fire` = ?, `rubles` = `rubles` - ? WHERE `id` = ?');
        $stmt -> execute([1, 100, $ids]);

        $logs = 'построил костер';
        $stmt = $go -> prepare('INSERT INTO `groups_logs` (`id_user`, `id_group`, `text`, `time`, `types`) VALUES (?, ?, ?, ?, ?)');
        $stmt -> execute([$uid, $gp['group']['id'], $logs, time(), 'build']);

        $_SESSION['success'] = 'Костер успешно построен.';
        die(header('Location: ?'));
      }
    }
    else
    {
      ?>
      <div class="fights fights-about center">
        Вы действительно хотите построить костер за <img src="/imgs/ruble.png" width="12px" alt="P"> 100 рублей?
        <div style="margin: 5px 0 0 0;">
          <div class="grid fights-link">
            <div class="six columns ln">
              <a href="?fire&ok">Построить</a>
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
elseif (isset($_GET['barracks']) and $gp != 0 and $gp['group']['id'] == $ids and $gp['user']['rank'] == 4)
{
  if (!array_key_exists($g['barracks']+1, $barracks)) show_error('Вы достигли максимального уровня казарм.');
  else
  {
    $br = $barracks[$g['barracks']+1];
    if (isset($_GET['barracks']) and isset($_GET['ok']))
    {
      if ($br['price'] == 'bolts' and $g['bolts'] < $br['amount']) show_error('В схроне группировки недостаточно болтов для улучшения');
      elseif ($br['price'] == 'rubles' and $g['rubles'] < $br['amount']) show_error('В схроне группировки недостаточно рублей для улучшения');
      else
      {
        $sql = 'UPDATE `groups` SET `'.$br['price'].'` = `'.$br['price'].'` - ?, `max_users` = `max_users` + ?, `barracks` = `barracks` + ? WHERE `id` = ?';
        $stmt = $go -> prepare($sql);
        $stmt -> execute([$br['amount'], $br['members'], 1, $ids]);

        $logs = 'улучшил казарму';
        $stmt = $go -> prepare('INSERT INTO `groups_logs` (`id_user`, `id_group`, `text`, `time`, `types`) VALUES (?, ?, ?, ?, ?)');
        $stmt -> execute([$uid, $gp['group']['id'], $logs, time(), 'build']);

        $_SESSION['success'] = 'Казарма успешно улучшена.';
        die(header('Location: ?'));
      }
    }
    else
    {
      ?>
      <div class="fights fights-about center">
        Вы действительно хотите улучшить казарму группировки за <?php echo ($br['price'] == 'bolts' ? '<img src="/imgs/bolts.png" width="12px" alt="Б"> '.$br['amount'].' болтов':'<img src="/imgs/ruble.png" width="12px" alt="Р"> '.$br['amount'].' рублей');?>?
        <div style="margin: 5px 0 0 0;">
          <div class="grid fights-link">
            <div class="six columns ln">
              <a href="?barracks&ok">Улучшить</a>
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
?>
<table class="fights">
  <tr>
    <td width="64px" valign="top">
      <img src="/files/objects/default.png" />
    </td>
    <td valign="top">
      <div class="attack-text">
        Костер <span class="label pull-right"><?php echo ($g['fire'] == 0 ? 'нет':'есть')?></span><hr/>
        <small>
          Личный костер для общения между членами группировки.<br/>
        </small>
      </div>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <?php if($gp != 0 && $gp['group']['id'] == $ids && $gp['user']['rank'] == 4 && $gp['build']['fire'] == 0): ?>
      <div class="fights-link fights-orange-link center"><a href="?fire">Построить</a></div>
      <?php endif;?>
    </td>
  </tr>
</table>
<table class="fights">
  <tr>
    <td width="64px" valign="top">
      <img src="/files/objects/default.png" />
    </td>
    <td valign="top">
      <div class="attack-text">
        Казарма <span class="label pull-right"><?php echo $g['barracks'];?>/<?php echo count($barracks);?></span><hr/>
        <small>
          Увеличивает число сталкеров в составе группировки.<br/>
          Максимально сейчас: <?php echo $g['max_users'];?> чел.
        </small>
      </div>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <?php if($gp != 0 && $gp['group']['id'] == $ids && $gp['user']['rank'] == 4 && array_key_exists($g['barracks'] + 1, $barracks)): ?>
      <div class="fights-link fights-orange-link center"><a href="?barracks">Улучшение</a></div>
      <?php endif;?>
    </td>
  </tr>
</table>
<div class="fights-link" style="margin: 5px;"><a href="/groups/<?php echo $g['id'];?>">Назад к группировке</a></div>
<?php
break;
case 'stash':
$gp = $grp -> inGroups($uid);
if ($gp == 0)
{
  $_SESSION['error'] = 'Вы не состоите в группировке';
  die(header('Location: /groups'));
}

$title = 'Схрон группировки';
include 'data/head.php';
?>
  <div class="callout">Схрон</div>
  <div style="margin: 2px 3px;">
    <div class="grid">
      <div class="six columns">
        <div class="col">
          <strong><?php echo numb($gp['group']['bolts']);?></strong><br/>
          <small>Болты</small>
        </div>
      </div>
      <div class="six columns">
        <div class="col">
          <strong><?php echo numb($gp['group']['rubles']);?></strong><br/>
          <small>Рубли</small>
        </div>
      </div>
    </div>
  </div>
  <div class="callout">Ваш вклад</div>
  <div style="margin: 2px 3px;">
    <div class="grid">
      <div class="six columns">
        <div class="col">
          <strong><?php echo numb($gp['user']['donate_bolts']);?></strong><br/>
          <small>Болты</small>
        </div>
      </div>
      <div class="six columns">
        <div class="col">
          <strong><?php echo numb($gp['user']['donate_rubles']);?></strong><br/>
          <small>Рубли</small>
        </div>
      </div>
    </div>
  </div>
  <?php
  if (isset($_REQUEST['pay']))
  {
    $post = [
      'type' => (int)$_POST['type'],
      'amount' => round(abs(intval($_POST['amount'])))
    ];
    if ($post['type'] != 0 and $post['type'] != 1) show_error('Неправильный тип взноса.');
    elseif ($post['amount'] <= 0 ) show_error('Ваш взнос должен быть больше нуля.');
    elseif ($post['type'] == 0 and $post['amount'] > $u['bolts']) show_error('У вас недостаточно болтов для взноса.');
    elseif ($post['type'] == 1 and $post['amount'] > $u['rubles']) show_error('У вас недостаточно рублей для взноса.');
    else
    {
      if ($post['type'] == 0)
      {
        $stmt = $go -> prepare('UPDATE `groups` SET `bolts` = `bolts` + ? WHERE `id` = ?');
        $stmt -> execute([$post['amount'], $gp['group']['id']]);
        $stmt = $go -> prepare('UPDATE `groups_users` SET `donate_bolts` = `donate_bolts` + ? WHERE `id_user` = ? and `id_group` = ? and `accept` = ?');
        $stmt -> execute([$post['amount'], $uid, $gp['group']['id'], '1']);
        $stmt = $go -> prepare('UPDATE `users` SET `bolts` = `bolts` - ? WHERE `id` = ?');
        $stmt -> execute([$post['amount'], $uid]);
        $log = 'внес в схрон '.declension($post['amount'], ['болт', 'болта', 'болтов']);
        $stmt = $go -> prepare('INSERT INTO `groups_logs` (`id_user`, `id_group`, `text`, `time`, `types`) VALUES (?, ?, ?, ?, ?)');
        $stmt -> execute([$uid, $gp['group']['id'], $log, time(), 'stash']);
        $_SESSION['success'] = 'Вы успешно внесли в схрон '.declension($post['amount'], ['болт', 'болта', 'болтов']);
        die(header('Location: /groups/stash'));
      }
      else
      {
        $stmt = $go -> prepare('UPDATE `groups` SET `rubles` = `rubles` + ? WHERE `id` = ?');
        $stmt -> execute([$post['amount'], $gp['group']['id']]);
        $stmt = $go -> prepare('UPDATE `groups_users` SET `donate_rubles` = `donate_rubles` + ? WHERE `id_user` = ? and `id_group` = ? and `accept` = ?');
        $stmt -> execute([$post['amount'], $uid, $gp['group']['id'], '1']);
        $stmt = $go -> prepare('UPDATE `users` SET `rubles` = `rubles` - ? WHERE `id` = ?');
        $stmt -> execute([$post['amount'], $uid]);
        $log = 'внес в схрон '.declension($post['amount'], ['рубль', 'рубля', 'рублей']);
        $stmt = $go -> prepare('INSERT INTO `groups_logs` (`id_user`, `id_group`, `text`, `time`, `types`) VALUES (?, ?, ?, ?, ?)');
        $stmt -> execute([$uid, $gp['group']['id'], $log, time(), 'stash']);
        $_SESSION['success'] = 'Вы успешно внесли в схрон '.declension($post['amount'], ['рубль', 'рубля', 'рублей']);
        die(header('Location: /groups/stash'));
      }
    }
  }
  ?>
  <div class="dialog">
    <h1 class="pda">Новый взнос</h1>
    <div class="dialog-p">
      <form method="POST">
        Что вносим?<br/>
        <select name="type">
          <option value="0">Болты</option>
          <option value="1">Рубли</option>
        </select><br/>
        Сколько вносим?<br/>
        <input type="number" name="amount" min="1" placeholder="Введите число..." required>
        <input type="submit" name="pay" value="Сделать взнос" />
      </form>
    </div>
  </div>
  <div class="fights-link" style="margin: 5px;"><a href="/groups/<?php echo $gp['group']['id'];?>">Назад к группировке</div>
<?php
break;
case 'settings':
$gp = $grp -> inGroups($uid);
if ($gp == 0)
{
  $_SESSION['error'] = 'Вы не состоите в группировке';
  die(header('Location: /groups'));
}
elseif ($gp['user']['rank'] < 4)
{
  $_SESSION['error'] = 'Управление группировкой доступно только лидеру.';
  die(header('Location: /groups/'.$gp['group']['id']));
}
$title = 'Управление группировкой';
include 'data/head.php';
?>
<div style="margin: 5px;">
  <a href="/groups/settings/edit" class="weapon">
    <table width="100%">
      <tr>
        <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
        <td class="attack-text">
          Группировка<br/>
          <small>
            Изменение информации
          </small>
        </td>
      </tr>
    </table>
  </a>
</div>
<div class="fights-link" style="margin: 5px;"><a href="/groups/<?php echo $gp['group']['id'];?>">Назад к группировке</div>
<?php
break;
case 'logs':
$gp = $grp -> inGroups($uid);
if ($gp == 0)
{
  $_SESSION['error'] = 'Вы не состоите в группировке';
  die(header('Location: /groups'));
}
elseif ($gp['user']['rank'] < 3)
{
  $_SESSION['error'] = 'Ваше звание не позволяет просматривать логи.';
  die(header('Location: /groups/'.$gp['group']['id']));
}
$title = 'Просмотр логов';
include 'data/head.php';
$pages = new Paginator(10, 'page');
$stmt = $go -> prepare('SELECT * FROM `groups_logs` WHERE `id_group` = ?');
$stmt -> execute([$gp['group']['id']]);
$total = $stmt -> rowCount();
$pages -> set_total($total);

if ($total > 0)
{
  $stmt = $go -> prepare('SELECT * FROM `groups_logs` WHERE `id_group` = ? ORDER BY `id` DESC '.$pages -> get_limit());
  $stmt -> execute([$gp['group']['id']]);
  $get = $stmt -> fetchAll();

  foreach ($get as $log)
  {
    ?>
    <div class="fights fights-about">
      <?php echo show_user($log['id_user']);?> <?php echo $log['text'];?> <?php echo ($log['id_other'] != NULL ? show_user($log['id_other']):NULL);?><br/>
      Дата: <?php echo date('j.m.Y в H:i:s', $log['time']);?>
    </div>
    <?php
  }
  if ($total > 10) echo $pages -> page_links();
}
else
{
  show_error('Логи не найдены.');
}
echo '<div class="fights-link" style="margin: 5px;"><a href="/groups/'.$gp['group']['id'].'">Назад к группировке</div>';
break;

}
include 'data/foot.php';