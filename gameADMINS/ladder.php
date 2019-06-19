<?php
include 'data/base.php';
switch ($act)
{
default:
  $title = 'Ладдер';
  include 'data/head.php';
  $stmt = $go -> prepare('SELECT * FROM `ladder` WHERE `id_user` = ?');
  $stmt -> execute([$uid]);
  $ladder = $stmt -> fetch();
  if (isset($ladder['id']))
  {
    if (isset($_GET['attack']))
    {
      if (empty($_GET['attack'])) show_error('Выберите противника.');
      elseif (!is_numeric($_GET['attack'])) show_error('Ошибка в запросе к серверу.');
      elseif ($_SESSION['enemy'] != $_GET['attack']) show_error('Это не ваш противник.');
      else
      {
        $stmt = $go -> prepare('SELECT `ladder`.`id`, `ladder`.`id_user` FROM `ladder` JOIN `users` ON (`ladder`.`id_user` = `users`.`id`) WHERE `ladder`.`id_user` = ? and `ladder`.`points` < ? and `ladder`.`points` > ? and (SELECT COUNT(*) FROM `ladder_fights` WHERE `id_user` = ? and `id_enemy` = ?) < ? LIMIT 1');
        $stmt -> execute([$_GET['attack'], $ladder['points']+100, $ladder['points']-100, $uid, $_GET['attack'], 3]);
        $fight = $stmt -> fetch();

        if (!isset($fight['id'])) show_error('Вы не можете сразиться с данным противником.');
        else
        {
          $damage['enemy'] = $wpn -> getLadderDamage($fight['id_user'], $uid);
          if ($damage['enemy']['min'] > $u['hp']) show_error('Минимальный урон противника по Вам превышает Ваше текущее здоровье.');
          else
          {
            $damage['user'] = $wpn -> getLadderDamage($uid, $fight['id_user']);

            $dash['enemy'] = $wpn -> getAtrb($fight['id_user'], 'dash');
            $dash['user'] = $wpn -> getAtrb($uid, 'dash');

            $crit['enemy'] = drop(['0' => (100-$dash['enemy']), '1' => $dash['enemy']]);
            $crit['user'] = drop(['0' => (100-$dash['user']), '1' => $dash['user']]);

            if ($crit['enemy'] == 1) $dmg['enemy'] = $damage['enemy']['crit'];
              else $dmg['enemy'] = $damage['enemy']['normal'];

            if ($crit['user'] == 1) $dmg['user'] = $damage['user']['crit'];
              else $dmg['user'] = $damage['user']['normal'];

            $stmt = $go -> prepare('UPDATE `users` SET `hp` = `hp` - ? WHERE `id` = ?');
            $stmt -> execute([$dmg['enemy'], $uid]);

            $stmt = $go -> prepare('INSERT INTO `ladder_fights` (`id_user`, `id_enemy`, `time`) VALUES (?, ?, ?)');
            $stmt -> execute([$uid, $fight['id_user'], time()]);

            if ($dmg['enemy'] > $dmg['user'])
            {
              $stmt = $go -> prepare('UPDATE `ladder` SET `points` = `points` + ?, `win` = `win` + ? WHERE `id_user` = ?');
              $stmt -> execute([1, 1, $fight['id_user']]);

              $stmt = $go -> prepare('UPDATE `ladder` SET `points` = `points` - ?, `lose` = `lose` + ? WHERE `id_user` = ?');
              $stmt -> execute([2, 1, $uid]);
              ?>
              <div class="col margin-left-right">
                <strong>Вы проиграли!</strong><br/>
                <small>результат поединка</small>
              </div>
              <?php
            }
            else
            {
              $stmt = $go -> prepare('UPDATE `ladder` SET `points` = `points` + ?, `win` = `win` + ? WHERE `id_user` = ?');
              $stmt -> execute([2, 1, $uid]);

              $stmt = $go -> prepare('UPDATE `ladder` SET `points` = `points` - ?, `lose` = `lose` + ? WHERE `id_user` = ?');
              $stmt -> execute([1, 1, $fight['id_user']]);
              ?>
              <div class="col margin-left-right">
                <strong>Вы победили!</strong><br/>
                <small>результат поединка</small>
              </div>
              <?php
            }
            ?>
            <div class="ladder">
              <div class="grid">
                <div class="six columns ln">
                  <div class="cl ladder-center">
                    <h1>Противник</h1><hr/>
                    Нанес вам <?php echo declension($dmg['enemy'], ['урон', 'урона', 'урона']);?> здоровью.<br/>
                    Тип урона: <?php echo ($crit['enemy'] == 1 ? '<span style="color: #ff0000;">критический</span>':'обычный');?>
                  </div>
                </div>
                <div class="six columns">
                  <div class="cl ladder-center">
                    <h1>Вы</h1><hr/>
                    Вы нанесли <?php echo declension($dmg['user'], ['урон', 'урона', 'урона']);?> здоровью.<br/>
                    Тип урона: <?php echo ($crit['user'] == 1 ? '<span style="color: #ff0000;">критический</span>':'обычный');?>
                  </div>
                </div>
              </div>
            </div>
            <?
          }
        }
      }
      echo '<div class="fights-link fights-orange-link center" style="margin: 5;"><a href="/ladder">Другой соперник</a></div>';
    }
    else
    {
      $stmt = $go -> prepare('SELECT `ladder`.`id`, `ladder`.`id_user`, `ladder`.`points`, `users`.`level` FROM `ladder` JOIN `users` ON (`ladder`.`id_user` = `users`.`id`) WHERE `ladder`.`id_user` != ? and `ladder`.`points` < ? and `ladder`.`points` > ? and (SELECT COUNT(*) FROM `ladder_fights` WHERE `id_user` = ? and `id_enemy` = `ladder`.`id_user`) < ? ORDER BY RAND() LIMIT 1');
      $stmt -> execute([$uid, $ladder['points']+100, $ladder['points']-100, $uid, 3]);
      $enemy = $stmt -> fetch();
      if (isset($enemy['id_user']))
      {
        $_SESSION['enemy'] = $enemy['id_user'];
        ?>
        <div class="ladder">
          <div class="grid">
            <div class="six columns ln">
              <div class="cl ladder-center">
                <h1>Противник</h1><hr/>
                Сила › <?php echo $wpn -> getAtrb($enemy['id_user'], 'power');?><br/>
                Рывок › <?php echo $wpn -> getAtrb($enemy['id_user'], 'dash');?><br/>
                Защита › <?php echo $wpn -> getAtrb($enemy['id_user'], 'defense');?><br/>
                Уровень › <?php echo $enemy['level'];?>
                <hr/>
                <?php echo declension($enemy['points'], ['очко', 'очка', 'очков']);?>
              </div>
            </div>
            <div class="six columns">
              <div class="cl ladder-center">
                <h1>Вы</h1><hr/>
                Сила › <?php echo $wpn -> getAtrb($u['id'], 'power');?><br/>
                Рывок › <?php echo $wpn -> getAtrb($u['id'], 'dash');?><br/>
                Защита › <?php echo $wpn -> getAtrb($u['id'], 'defense');?><br/>
                Уровень › <?php echo $u['level'];?>
                <hr/>
                <?php echo declension($ladder['points'], ['очко', 'очка', 'очков']);?>
              </div>
            </div>
            <div class="twelve columns ln-top">
              <div class="cl ladder-center">
                <div class="fights-link fights-orange-link center" style="margin: 2px 0;"><a href="?attack=<?php echo $enemy['id_user'];?>">Начать поединок</a></div>
              </div>
            </div>
          </div>
        </div>
        <?php
      }
      else
      {
        show_error('Противников нет.');
      }
    }
    $stmt = $go -> prepare('SELECT * FROM `ladder` WHERE `id_user` = ?');
    $stmt -> execute([$uid]);
    $stats = $stmt -> fetch();

    $stmt = $go -> prepare('SELECT `id` FROM `ladder` WHERE `points` > ? and `id_user` != ?');
    $stmt -> execute([$stats['points'], $uid]);
    $place = $stmt -> rowCount();
    ?>
    <div class="ladder center">
      <div class="grid">
        <div class="twelve columns">
          <div class="col">
            <strong><?php echo $place+1;?> место</strong><br/>
            <small>Позиция в ладдере</small>
          </div>
        </div>
        <div class="six columns">
          <div class="col">
            <strong><?php echo $stats['points'];?></strong><br/>
            <small>Очков ладдера</small>
          </div>
        </div>
        <div class="six columns">
          <div class="col">
            <strong><?php echo $stats['win'];?>/<?php echo $stats['lose'];?></strong><br/>
            <small>Побед/Поражений</small>
          </div>
        </div>
      </div>
    </div>
    <?php
  }
  else
  {
    if (isset($_GET['join']))
    {
      if ($u['repute'] < 500) show_error('Присоединиться к ладдеру можно только при наличии более 500 репутации в деревне.');
      else
      {
        $stmt = $go -> prepare('INSERT INTO `ladder` (`id_user`, `addDate`) VALUES (?, ?)');
        $stmt -> execute([$uid, time()]);
        $_SESSION['success'] = 'Вы присоединились к ладдеру.';
        die(header('Location: /ladder'));
      }
    }
    ?>
    <div class="dialog">
      <h1 class="pda">КПК</h1>
      <div class="dialog-p">
        <strong>Ладдер</strong> - еженедельное соревнование местных сталкеров между собой в рукопашном сражении.<br/>
        › За победу в поединке Вы получаете очки ладдера, за проигрышь - очки отнимаются.<br/>
        › Все начинают с 1000 поинтов. Если поинтов становится 0, то этот сталкер выбывает из ладдера.<br/>
        › С каждым можно сразиться не более 3 раз в сутки.<br/>
        › В конце недели 3 игрока с самым большим числом поинтов получают особую награду.
      </div>
    </div>
    <?php
    if ($u['repute'] < 500)
    {
      show_error('Присоединиться к ладдеру можно только при наличии более 500 репутации в деревне.');
    }
    else echo '<div class="fights-link fights-orange-link center" style="margin: 2px 5px;"><a href="?join">Принять участие</a></div>';
  }
  ?>
  <div class="fights-link center" style="margin: 2px 5px;"><a href="/ladder/members">Участники ладдера</a></div>
  <?php
break;
case 'members':
  $title = 'Список участников';
  include 'data/head.php';
  $pages = new Paginator(10, 'page');
  $stmt = $go -> prepare('SELECT `id` FROM `ladder`');
  $stmt -> execute([]);
  $total = $stmt -> rowCount();
  $pages -> set_total($total);
  if ($total > 0)
  {
    $stmt = $go -> prepare('SELECT `users`.`login`, `users`.`id`, `ladder`.`points`, `ladder`.`win` FROM `ladder` JOIN `users` ON (`ladder`.`id_user` = `users`.`id`) ORDER BY `ladder`.`points` DESC '.$pages -> get_limit());
    $stmt -> execute([]);
    $get = $stmt -> fetchAll();

    if ($pages->_page == 1) $place = 0;
      else $place = (10 * $pages->_page) - 10;
    echo '<div style="margin: 5px;">';
    foreach($get as $member)
    {
      $place += 1;
      ?>
      <a href="/id/<?php echo $member['id'];?>" class="weapon">
        <table width="100%">
          <tr>
            <td style="white-space:nowrap;width: 100%;" class="attack-text">
              <?php echo $member['login'];?><br/>
              <small>
                <?php echo $member['points'];?> поинтов / <?php echo $member['win'];?> побед
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
    echo show_error('Игроки не найдены');
  }
  echo '<div class="fights-link" style="margin: 5px;"><a href="/ladder">Назад в ладдер</a></div>';
break;
}
include 'data/foot.php';