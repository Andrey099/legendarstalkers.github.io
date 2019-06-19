<?php
include 'data/base.php';
switch ($act)
{
default:
  $title = 'Рейтинг сталкеров';
  include 'data/head.php';

  $pages = new Paginator(10, 'page');
  $stmt = $go -> prepare('SELECT `id` FROM `users`');
  $stmt -> execute([]);
  $total = $stmt -> rowCount();
  $pages -> set_total($total);
  ?>
  <div style="margin: 5px;">
    <div class="grid fights-link">
      <div class="six columns ln">
        <a href="/users">Рейтинг</a>
      </div>
      <div class="six columns">
        <a href="/users/online">Онлайн</a>
      </div>
    </div>
  </div>
  <?php
  if ($total > 0)
  {
    $stmt = $go -> prepare('SELECT `id`, `login`, `repute`, `level` FROM `users` ORDER BY `repute` DESC '.$pages -> get_limit());
    $stmt -> execute([]);
    $get = $stmt -> fetchAll();

    if ($pages->_page == 1) $place = 0;
      else $place = (10 * $pages->_page) - 10;
    echo '<div style="margin: 5px;">';
    foreach($get as $rating)
    {
      $place += 1;
      ?>
      <a href="/id/<?php echo $rating['id'];?>" class="weapon">
        <table width="100%">
          <tr>
            <td style="white-space:nowrap;width: 100%;" class="attack-text">
              <?php echo $rating['login'];?><br/>
              <small>
                <?php echo numb($rating['repute']);?> репутации / <?php echo $rating['level'];?> уровень
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
break;

case 'online':
  $title = 'Онлайн сталкеры';
  include 'data/head.php';

  $pages = new Paginator(10, 'page');
  $stmt = $go -> prepare('SELECT `id` FROM `users` WHERE `updDate` > ?');
  $stmt -> execute([time() - 900]);
  $total = $stmt -> rowCount();
  $pages -> set_total($total);
  ?>
  <div style="margin: 5px;">
    <div class="grid fights-link">
      <div class="six columns ln">
        <a href="/users">Рейтинг</a>
      </div>
      <div class="six columns">
        <a href="/users/online">Онлайн</a>
      </div>
    </div>
  </div>
  <?php
  if ($total > 0)
  {
    $stmt = $go -> prepare('SELECT `id`, `login`, `repute`, `level` FROM `users` WHERE `updDate` > ? ORDER BY `repute` DESC '.$pages -> get_limit());
    $stmt -> execute([time() - 900]);
    $get = $stmt -> fetchAll();

    if ($pages->_page == 1) $place = 0;
      else $place = (10 * $pages->_page) - 10;
    echo '<div style="margin: 5px;">';
    foreach($get as $rating)
    {
      $place += 1;
      ?>
      <a href="/id/<?php echo $rating['id'];?>" class="weapon">
        <table width="100%">
          <tr>
            <td style="white-space:nowrap;width: 100%;" class="attack-text">
              <?php echo $rating['login'];?><br/>
              <small>
                <?php echo numb($rating['repute']);?> репутации / <?php echo $rating['level'];?> уровень
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
break;
}
include 'data/foot.php';
?>