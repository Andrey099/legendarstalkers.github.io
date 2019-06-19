<?php
include 'data/base.php';
user();

switch ($act)
{

default:
  $title = 'Форум';
  include 'data/head.php';

  $stmt = $go -> prepare('SELECT * FROM `forum` ORDER BY `id`');
  $stmt -> execute([]);
  $total = $stmt -> rowCount();
  $get = $stmt -> fetchAll();

  if ($total > 0)
  {
    echo '<div style="margin: 5px;">';
    foreach ($get as $forum)
    {
      $stmt = $go -> prepare('SELECT `id` FROM `forum_topics` WHERE `id_forum` = ?');
      $stmt -> execute([$forum['id']]);
      $count['top'] = $stmt -> rowCount();

      $stmt = $go -> prepare('SELECT * FROM `forum_posts` AS `p` JOIN `forum_topics` AS `t` ON (`p`.`id_topic` = `t`.`id`) JOIN `forum` AS `f` ON (`f`.`id` = `t`.`id_forum`) WHERE `f`.`id` = ?');
      $stmt -> execute([$forum['id']]);
      $count['sms'] = $stmt -> rowCount();
      ?>
      <a href="/forum/<?php echo $forum['id'];?>" class="weapon">
        <table width="100%">
          <tr>
            <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
            <td class="attack-text">
              <?php echo $forum['name'];?><br/>
              <small>
                Обсуждений › <?php echo $count['top'];?>, сообщений › <?php echo $count['sms'];?>
              </small>
            </td>
          </tr>
        </table>
      </a>
      <?php
    }
    echo '</div>';
  }
  else
  {
    echo show_error('Разделы форума еще не созданы.');
  }
  if ($u['access'] == 3) echo '<div class="fights-link" style="margin: 5px;"><a href="/forum/create">Создать раздел</a></div>';
break;
// TODO: Создание раздела
case 'forum_create':
  if ($u['access'] != 3) header('Location: /forum/');
  $title = 'Создать раздел';
  include 'data/head.php';
  if (isset($_REQUEST['create']))
  {
    if (empty($_POST['name'])) $error[] = 'Название раздела обязательно для заполнения.';
    elseif (strlen($_POST['name']) < 3) $error[] = 'Название раздела не может быть короче 3 символов.';
    elseif (strlen($_POST['name']) > 64) $error[] = 'Название раздела не может быть длинее 64 символов.';

    if (empty($_POST['text'])) $error[] = 'Описание раздела обязательно для заполнения.';
    elseif (strlen($_POST['text']) < 32) $error[] = 'Описание раздела не может быть короче 32 символов.';
    elseif (strlen($_POST['text']) > 512) $error[] = 'Описание раздела не может быть длинее 512 символов.';

    if (empty($error))
    {
      $stmt = $go -> prepare('INSERT INTO `forum` (`name`, `about`) VALUES (?, ?)');
      $stmt -> execute([$_POST['name'], $_POST['text']]);
      $forum = $go -> lastInsertId();
      $_SESSION['success'] = 'Раздел успешно создан';
      die(header('Location: /forum/'.$forum));
    }
    else
    {
      echo show_error($error);
    }
  }
  ?>
  <div class="fights fights-about">
    <form method="POST">
      Название раздела:<br/>
      <input type="text" name="name" placeholder="Введите название..."><br/>
      Подробное описание: <br/>
      <textarea name="text" placeholder="Введите описание..."></textarea><br/>
      <input type="submit" name="create" value="Создать раздел">
    </form>
  </div>
  <div class="fights-link" style="margin: 5px;"><a href="/forum">Назад к разделам</a></div>
  <?php
break;
// TODO: Изменение раздела
case 'forum_edit':
  $stmt = $go -> prepare('SELECT * FROM `forum` WHERE `id` = ?');
  $stmt -> execute([$ids]);
  $forum = $stmt -> fetch();
  $title = 'Изменение раздела';
  include 'data/head.php';
  if (!isset($forum['id'])) header('Location: /forum/');
  elseif ($u['access'] < 3) echo show_error('Ошибка доступа.');
  else
  {
    if (isset($_REQUEST['edit']))
    {
      if (empty($_POST['name'])) $error[] = 'Название раздела обязательно для заполнения.';
      elseif (strlen($_POST['name']) < 3) $error[] = 'Название раздела не может быть короче 3 символов.';
      elseif (strlen($_POST['name']) > 64) $error[] = 'Название раздела не может быть длинее 64 символов.';

      if (empty($_POST['text'])) $error[] = 'Описание раздела обязательно для заполнения.';
      elseif (strlen($_POST['text']) < 32) $error[] = 'Описание раздела не может быть короче 32 символов.';
      elseif (strlen($_POST['text']) > 512) $error[] = 'Описание раздела не может быть длинее 512 символов.';

      if (empty($error))
      {
        $stmt = $go -> prepare('UPDATE `forum` SET `name` = ?, `about` = ? WHERE `id` = ?');
        $stmt -> execute([$_POST['name'], $_POST['text'], $forum['id']]);
        $_SESSION['success'] = 'Разел успешно изменен';
        die(header('Location: /forum/'.$forum['id']));
      }
      else
      {
        echo show_error($error);
      }
    }
    ?>
    <div class="fights fights-about">
      <form method="POST">
        Название раздела:<br/>
        <input type="text" name="name" value="<?php echo $forum['name'];?>" placeholder="Введите название..."><br/>
        Подробное описание: <br/>
        <textarea name="text" placeholder="Введите описание..."><?php echo $forum['about'];?></textarea><br/>
        <input type="submit" name="edit" value="Изменить раздел">
      </form>
    </div>
    <div class="fights-link" style="margin: 5px;"><a href="/forum/<?php echo $forum['id'];?>">Назад к разделу</a></div>
    <?php
  }
break;
case 'topic':
  $stmt = $go -> prepare('SELECT * FROM `forum` WHERE `id` = ?');
  $stmt -> execute([$ids]);
  $forum = $stmt -> fetch();
  if (!isset($forum['id'])) header('Location: /forum/');
  $title = $forum['name'];
  include 'data/head.php';
  ?>
  <div class="dialog">
    <h1 class="pda"><?php echo $forum['name'];?> <?php echo ($u['access'] == 3 ? '<a href="/forum/'.$forum['id'].'/edit">[Ред]</a>':NULL);?></h1>
    <p>
      › <?php echo $forum['about'];?>
    </p>
  </div>
  <?php
  $pages = new Paginator(10, 'page');
  $stmt = $go -> prepare('SELECT `id` FROM `forum_topics` WHERE `id_forum` = ?');
  $stmt -> execute([$ids]);
  $total = $stmt -> rowCount();
  $pages -> set_total($total);
  if ($total > 0)
  {
    $stmt = $go -> prepare('SELECT `id`, `name`, `id_user`, `timeAdd` FROM `forum_topics` WHERE `id_forum` = ? ORDER BY `pin` DESC, (SELECT max(`id`) FROM `forum_posts` WHERE `id_topic` = `forum_topics`.`id`) DESC '.$pages -> get_limit());
    $stmt -> execute([$ids]);
    $get = $stmt -> fetchAll();
    if ($pages->_page == 1) $place = 0;
      else $place = (10 * $pages->_page) - 10;
    echo '<div style="margin: 2px 5px;">';
    foreach ($get as $topic)
    {
      $place += 1;
      $stmt = $go -> prepare('SELECT `id` FROM `forum_posts` WHERE `id_topic` = ?');
      $stmt -> execute([$topic['id']]);
      $count = $stmt -> rowCount();
      ?>
      <a href="/forum/topic/<?php echo $topic['id'];?>" class="weapon">
        <table width="100%">
          <tr>
            <td class="attack-icon" valign="top"><?php echo ($place < 10 ? '0':NULL).$place;?></td>
            <td style="white-space:nowrap;width: 100%;" valign="top" class="attack-text">
              <?php echo $topic['name'];?><br/>
              <small>
                Сообщений › <?php echo $count;?>
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
    echo show_error('Темы раздела еще не созданы.');
  }
  echo ($forum['canAdd'] == 'admin' ? ($u['access'] == 3 ? '<div class="fights-link" style="margin: 2px 5px;"><a href="/forum/'.$ids.'/create">Создать обсуждение</a></div>':NULL) : '<div class="fights-link" style="margin: 2px 5px;"><a href="/forum/'.$ids.'/create">Создать обсуждение</a></div>');
  echo '<div class="fights-link" style="margin: 2px 5px;"><a href="/forum">Назад к разделам</a></div>';
break;
// TODO: Создание топика
case 'topic_create':
  $stmt = $go -> prepare('SELECT * FROM `forum` WHERE `id` = ?');
  $stmt -> execute([$ids]);
  $forum = $stmt -> fetch();
  if (!isset($forum['id'])) header('Location: /forum/');
  $title = 'Создать обсуждение';
  include 'data/head.php';
  if ($forum['canAdd'] == 'admin' and $u['access'] != 3)
  {
    echo show_error('Вы не можете создать обсуждение в данном разделе.');
  }
  else
  {
    if (isset($_REQUEST['create']))
    {
      if (empty($_POST['name'])) $error[] = 'Название темы обязательно для заполнения.';
      elseif (strlen($_POST['name']) < 3) $error[] = 'Название темы не может быть короче 3 символов.';
      elseif (strlen($_POST['name']) > 64) $error[] = 'Название темы не может быть длинее 64 символов.';

      if (empty($_POST['text'])) $error[] = 'Описание темы обязательно для заполнения.';
      elseif (strlen($_POST['text']) < 32) $error[] = 'Описание темы не может быть короче 32 символов.';
      elseif (strlen($_POST['text']) > 512 and $u['access'] == 0) $error[] = 'Описание темы не может быть длинее 512 символов.';

      if (empty($error))
      {
        $stmt = $go -> prepare('INSERT INTO `forum_topics` (`id_forum`, `id_user`, `name`, `message`, `timeAdd`) VALUES (?, ?, ?, ?, ?)');
        $stmt -> execute([$ids, $uid, $_POST['name'], $_POST['text'], time()]);
        $topic = $go -> lastInsertId();
        $posted = '[quote]Обсуждение начато[/quote]';
        $stmt = $go -> prepare('INSERT INTO `forum_posts` (`id_topic`, `id_user`, `message`, `timeAdd`) VALUES (?, ?, ?, ?)');
        $stmt -> execute([$topic, $uid, $posted, time()]);
        $_SESSION['success'] = 'Тема обсуждения успешно создана';
        die(header('Location: /forum/topic/'.$topic));
      }
      else
      {
        echo show_error($error);
      }
    }
    ?>
    <div class="fights fights-about">
      <form method="POST">
        Название темы обсуждения:<br/>
        <input type="text" name="name" placeholder="Введите название..."><br/>
        Подробное описание: <br/>
        <textarea name="text" placeholder="Введите описание..."></textarea><br/>
        <input type="submit" name="create" value="Создать обсуждение">
      </form>
    </div>
    <div class="fights-link" style="margin: 5px;"><a href="/forum/<?php echo $ids;?>">Назад к разделу</a></div>
    <?php
  }
break;
// TODO: Изменение топика
case 'topic_edit':
  $stmt = $go -> prepare('SELECT * FROM `forum_topics` WHERE `id` = ?');
  $stmt -> execute([$ids]);
  $topic = $stmt -> fetch();
  $title = 'Изменение обсуждения';
  include 'data/head.php';
  if (!isset($topic['id'])) die(header('Location: /forum/'));
  elseif ($topic['id_user'] != $uid and $u['access'] == 0) echo show_error('Ошибка доступа.');
  elseif ($u['access'] == 0 and $topic['id_user'] == $uid and $topic['closed'] == 1) echo show_error('Вы не можете редактировать закрытый топик.');
  else
  {
    if (isset($_REQUEST['edit']))
    {
      if (empty($_POST['name'])) $error[] = 'Название темы обязательно для заполнения.';
      elseif (strlen($_POST['name']) < 3) $error[] = 'Название темы не может быть короче 3 символов.';
      elseif (strlen($_POST['name']) > 64) $error[] = 'Название темы не может быть длинее 64 символов.';

      if (empty($_POST['text'])) $error[] = 'Описание темы обязательно для заполнения.';
      elseif (strlen($_POST['text']) < 32) $error[] = 'Описание темы не может быть короче 32 символов.';
      elseif (strlen($_POST['text']) > 512 and $u['access'] == 0) $error[] = 'Описание темы не может быть длинее 512 символов.';

      if (empty($error))
      {
        $stmt = $go -> prepare('UPDATE `forum_topics` SET `name` = ?, `message` = ?, `timeUpd` = ?, `whoUpd` = ? WHERE `id` = ?');
        $stmt -> execute([$_POST['name'], $_POST['text'], time(), $uid, $topic['id']]);
        $_SESSION['success'] = 'Обсуждения успешно изменено';
        die(header('Location: /forum/topic/'.$topic['id']));
      }
      else
      {
        echo show_error($error);
      }
    }
    ?>
    <div class="fights fights-about">
      <form method="POST">
        Название темы обсуждения:<br/>
        <input type="text" name="name" value="<?php echo $topic['name'];?>" placeholder="Введите название..."><br/>
        Подробное описание: <br/>
        <textarea name="text" placeholder="Введите описание..."><?php echo $topic['message'];?></textarea><br/>
        <input type="submit" name="edit" value="Изменить обсуждение">
      </form>
    </div>
    <?php
  }
  echo '<div class="fights-link" style="margin: 5px;"><a href="/forum/topic/'.$topic['id'].'">Назад к топику</a></div>';
break;
// TODO: Удаление топика
case 'topic_delete';
  $stmt = $go -> prepare('SELECT * FROM `forum_topics` WHERE `id` = ?');
  $stmt -> execute([$ids]);
  $topic = $stmt -> fetch();
  $title = 'Удаление обсуждения';
  include 'data/head.php';
  if (!isset($topic['id'])) die(header('Location: /forum/'));
  elseif ($u['access'] == 0) die(header('Location: /forum/'));
  else
  {
    if (isset($_GET['yes']))
    {
      $stmt = $go -> prepare('DELETE FROM `forum_topics` WHERE `id` = ?');
      $stmt -> execute([$ids]);
      $_SESSION['success'] = 'Обсуждение успешно удалено.';
      die(header('Location: /forum/'.$topic['id_forum']));
    }
    ?>
    <div class="dialog">
      <h1 class="pda">КПК</h1>
      <p>
        Вы действительно хотите удалить обсуждение "<?php echo $topic['name'];?>"?
      </p>
      <div class="fights-link fights-orange-link center" style="margin: 5px 0;"><a href="?yes">Да, удалить</a></div>
    </div>
    <div class="fights-link" style="margin: 5px;"><a href="/forum/topic/<?php echo $topic['id'];?>">Назад к топику</a></div>
    <?php
  }
break;
// TODO: Закрепление/открепление топика
case 'topic_pin':
$stmt = $go -> prepare('SELECT * FROM `forum_topics` WHERE `id` = ?');
  $stmt -> execute([$ids]);
  $topic = $stmt -> fetch();
  if (!isset($topic['id'])) die(header('Location: /forum/'));
  elseif ($u['access'] < 2) die(header('Location: /forum/'));
  else
  {
    if ($topic['pin'] == 0)
    {
      $stmt = $go -> prepare('UPDATE `forum_topics` SET `pin` = ? WHERE `id` = ?');
      $stmt -> execute([1, $ids]);
      $_SESSION['success'] = 'Обсуждение закреплено.';
      die(header('Location: /forum/topic/'.$topic['id']));
    }
    else
    {
      $stmt = $go -> prepare('UPDATE `forum_topics` SET `pin` = ? WHERE `id` = ?');
      $stmt -> execute([0, $ids]);
      $_SESSION['success'] = 'Обсуждение откреплено.';
      die(header('Location: /forum/topic/'.$topic['id']));
    }
  }
break;
// TODO: Закрытие/открытие топика
case 'topic_access':
$stmt = $go -> prepare('SELECT * FROM `forum_topics` WHERE `id` = ?');
  $stmt -> execute([$ids]);
  $topic = $stmt -> fetch();
  if (!isset($topic['id'])) die(header('Location: /forum/'));
  elseif ($u['access'] < 1) die(header('Location: /forum/'));
  else
  {
    if ($topic['closed'] == 0)
    {
      if (isset($_REQUEST['closed']))
      {
        $post = ['reason' => trim(mb_strtolower($_POST['reason']))];

        if (empty($post['reason'])) show_error('Введите причину.');
        else
        {
          $stmt = $go -> prepare('UPDATE `forum_topics` SET `closed` = ? WHERE `id` = ?');
          $stmt -> execute([1, $ids]);
          $message = '[quote]Обсуждение закрыто <br/>Причина: '.$post['reason'].'[/quote]';
          $stmt = $go -> prepare('INSERT INTO `forum_posts` (`id_topic`, `id_user`, `message`, `timeAdd`) VALUES (?, ?, ?, ?)');
          $stmt -> execute([$ids, $uid, $message, time()]);
          $_SESSION['success'] = 'Обсуждение закрыто.';
          die(header('Location: /forum/topic/'.$topic['id']));
        }
      }
      else
      {
        $title = 'Закрытие обсуждения';
        include 'data/head.php';
        ?>
        <div class="fights fights-about">
          <form method="POST">
            Причина:<br/>
            <textarea name="reason" placeholder="Введите причину..."></textarea>
            <input type="submit" name="closed" value="Закрыть">
          </form>
        </div>
        <div class="fights-link" style="margin: 5px;"><a href="/forum/topic/<?php echo $topic['id'];?>">Назад к топику</a></div>
        <?php
      }
    }
    else
    {
      $stmt = $go -> prepare('UPDATE `forum_topics` SET `closed` = ? WHERE `id` = ?');
      $stmt -> execute([0, $ids]);
      $message = '[quote]Обсуждение вновь открыто.[/quote]';
      $stmt = $go -> prepare('INSERT INTO `forum_posts` (`id_topic`, `id_user`, `message`, `timeAdd`) VALUES (?, ?, ?, ?)');
      $stmt -> execute([$ids, $uid, $message, time()]);
      $_SESSION['success'] = 'Обсуждение открыто.';
      die(header('Location: /forum/topic/'.$topic['id']));
    }
  }
break;
// TODO: Просмотр топика
case 'view':
  $stmt = $go -> prepare('SELECT * FROM `forum_topics` WHERE `id` = ?');
  $stmt -> execute([$ids]);
  $topic = $stmt -> fetch();
  if (!isset($topic['id'])) header('Location: /forum/');
  $title = $topic['name'];
  include 'data/head.php';
  ?>
  <div class="col" style="margin: 2px 5px;">
    <strong><?php echo $topic['name'];?></strong><br/>
    <small><?php echo ($topic['closed'] == 1 ? 'Обсуждение закрыто':'Обсуждение открыто');?></small>
  </div>
  <div class="dialog">
    <h1 class="pda"><?php echo show_user($topic['id_user']);?></h1>
    <div class="dialog-p">
      <?php echo bbcode($topic['message'], $topic['id_user']);?>
    </div>
    <div class="small">
      Дата создания › <?php echo date('j.m.Y в H:i:s', $topic['timeAdd']);?><br/>
      <?php if ($topic['timeUpd'] != NULL): ?>
        Дата изменения › <?php echo date('j.m.Y в H:i:s', $topic['timeUpd']);?> (<?php echo show_user($topic['whoUpd']);?>)
      <?php endif; ?>
    </div>
    <?php if ($uid == $topic['id_user'] or $u['access'] == 3): ?>
      <div class="about">
        <a href="/forum/topic/<?php echo $topic['id'];?>/edit">Изменить</a>
        <?php if ($u['access'] > 0): ?>
          / <a href="/forum/topic/<?php echo $topic['id'];?>/delete">Удалить</a>
          / <a href="/forum/topic/<?php echo $topic['id'];?>/access"><?php echo ($topic['closed'] == 0 ? 'Закрыть':'Открыть');?></a>
        <?php endif; ?>
        <?php if ($u['access'] > 1): ?>
          / <a href="/forum/topic/<?php echo $topic['id'];?>/pin"><?php echo ($topic['pin'] == 0 ? 'Закрепить':'Открепить');?></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
  <?php
  $pages = new Paginator(10, 'page');
  $stmt = $go -> prepare('SELECT `id` FROM `forum_posts` WHERE `id_topic` = ?');
  $stmt -> execute([$ids]);
  $total = $stmt -> rowCount();
  $pages -> set_total($total);
  if (isset($_REQUEST['create']))
  {
    if ($topic['closed'] == 1) $error[] = 'Тема закрыта.';
    if (empty($_POST['text'])) $error[] = 'Напишите текст вашего сообщения.';
      elseif (strlen($_POST['text']) < 3) $error[] = 'Текст сообщения не может быть короче 3 символов.';
      elseif (strlen($_POST['text']) > 512) $error[] = 'Описание темы не может быть длинее 512 символов.';
    if (empty($error))
    {
      $stmt = $go -> prepare('INSERT INTO `forum_posts` (`id_topic`, `id_user`, `message`, `timeAdd`) VALUES (?, ?, ?, ?)');
      $stmt -> execute([$topic['id'], $uid, $_POST['text'], time()]);
      header('Location: /forum/topic/'.$topic['id'].'?page='.$pages -> get_last(1));
    }
    else
    {
      echo show_error($error);
    }
  }
  if ($total > 0)
  {
    $stmt = $go -> prepare('SELECT * FROM `forum_posts` WHERE `id_topic` = ? ORDER BY `timeAdd` ASC '.$pages -> get_limit());
    $stmt -> execute([$ids]);
    $get = $stmt -> fetchAll();
    if ($pages->_page == 1) $place = 0;
      else $place = (10 * $pages->_page) - 10;
    foreach ($get as $posts)
    {
      $place += 1;
      ?>
      <div class="dialog">
        <h1 class="chat"> <?php echo show_user($posts['id_user']);?> <?php echo ($posts['answer'] != NULL ? 'в ответ '.show_user($posts['answer']):NULL);?></h1>
        <div class="dialog-p">
          <?php echo bbcode($posts['message'], $posts['id_user']);?>
        </div>
        <div class="small">
          #<?php echo ($place < 10 ? '0':NULL).$place;?> / <?php echo date('j.m.Y в H:i:s', $posts['timeAdd']);?>
        </div>
      </div>
      <?php
    }
    if ($total > 10) echo $pages -> page_links();
  }
  else
  {
    echo show_error('В данной теме еще нет сообщений.');
  }
  if ($topic['closed'] == 0)
  {
    ?>
    <div class="dialog">
      <form method="POST">
        <textarea name="text" placeholder="Введите сообщение..."></textarea><br/>
        <input type="submit" name="create" value="Написать">
      </form>
    </div>
    <?php
  }
  else
  {
    echo show_error('Обсуждение закрыто.');
  }
  echo '<div class="fights-link" style="margin: 5px;"><a href="/forum/'.$topic['id_forum'].'">Назад к темам</a></div>';
break;
}
include 'data/foot.php';