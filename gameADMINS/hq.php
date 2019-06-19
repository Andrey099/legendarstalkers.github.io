<?php
include 'data/base.php';
switch ($act)
{
default:
  $title = 'Штаб';
  include 'data/head.php';
  ?>
  <div style="margin: 5px;">
    <a href="/hq/coin" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
          <td class="attack-text">
            Монетка<br/>
            <small>
              Испытай удачу
            </small>
          </td>
        </tr>
      </table>
    </a>
  </div>
  <?php
break;

case 'coin':
  $title = 'Монетка';
  include 'data/head.php';
  if (isset($_GET['game']))
  {
    if (empty($_GET['game']))
    {
      $_SESSION['error'] = 'Выберите сторону монетки.';
      die(header('Location: /hq/coin'));
    }
    elseif ($_GET['game'] != 1 and $_GET['game'] != 2 and $_GET['game'] != 3)
    {
      $_SESSION['error'] = 'Ошибка в выборе стороны, попробуйте еще раз.';
      die(header('Location: /hq/coin'));
    }
    elseif ($u['bolts'] < 100)
    {
      $_SESSION['error'] = 'Недостаточно болтов, чтобы сыграть.';
      die(header('Location: /hq/coin'));
    }
    else
    {
      if ($_GET['game'] == 1)
      {
        $side_1 = 20;
        $side_2 = 70;
      }
      elseif ($_GET['game'] == 2)
      {
        $side_1 = 70;
        $side_2 = 20;
      }
      else
      {
        $side_1 = 50;
        $side_2 = 50;
      }
      $flip = ['1' => $side_1,'2' => $side_2,'3' => '2'];
      $side = ['1' => 'орлом вверх','2' => 'решкой вверх','3' => 'на ребро'];

      $drop = drop($flip);

      if ($drop == 3 and $_GET['game'] == 3) $win = 2000;
        else $win = 200;

      if ($drop == $_GET['game'])
      {
        $stmt = $go -> prepare('UPDATE `users` SET `bolts` = `bolts` + ? WHERE `id` = ?');
        $stmt -> execute([$win, $uid]);
      }
      else
      {
        $stmt = $go -> prepare('UPDATE `users` SET `bolts` = `bolts` - ? WHERE `id` = ?');
        $stmt -> execute([100, $uid]);
      }
      ?>
      <div class="dialog">
        <h1 class="human">Монгол</h1>
        <div class="dialog-p">
          * подбрасывает монетку*<br/>
          <div class="about">
            Монета приземлилась <?php echo $side[$drop];?>
          </div>
          <?php if ($drop == $_GET['game']): ?>
            › Ты победил, сталкер. Вот твоя награда.
            <div class="about">
              <img src="/imgs/bolts.png" width="10px" /> Болты<br/>
              + <?php echo $win;?>
            </div>
          <?php else: ?>
            › Ты проиграл, сталкер. Ничего страшного, повезет в следующий раз.
            <div class="about">
              <img src="/imgs/bolts.png" width="10px" /> Болты<br/>
              - 100 болтов
            </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="fights-link fights-orange-link" style="margin: 5px;"><a href="/hq/coin">Сыграть еще раз</a></div>
      <div class="fights-link" style="margin: 5px;"><a href="/hq">Вернуться в штаб</a></div>
      <?php
    }
  }
  else
  {
    ?>
    <div class="dialog">
      <h1 class="pda">Правила</h1>
      <div class="dialog-p small">
        › Орел/Решка - если вы угадали, ставка умножается в 2 раза.<br/>
        › Ребро - если вы угадали, ставка умножается в 20 раз.<br/>
        › Ставка: <img src="/imgs/bolts.png" width="10px" /> 100 болтов
      </div>
      <h1 class="human">Монгол</h1>
      <div class="dialog-p">
        › Ну что, сталкер, играем?
      </div>
      <div class="grid">
        <div class="four columns">
          <div style="margin: 1px;">
            <div class="fights-link fights-orange-link"><a href="?game=1">Орел</a></div>
          </div>
        </div>
        <div class="four columns">
          <div style="margin: 1px;">
            <div class="fights-link fights-orange-link"><a href="?game=2">Решка</a></div>
          </div>
        </div>
        <div class="four columns">
          <div style="margin: 1px;">
            <div class="fights-link fights-orange-link"><a href="?game=3">Ребро</a></div>
          </div>
        </div>
      </div>
    </div>
    <div class="fights-link" style="margin: 5px;"><a href="/hq">Вернуться в штаб</a></div>
    <?php
  }
break;
}
include 'data/foot.php';