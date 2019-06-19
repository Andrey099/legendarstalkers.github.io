<?php
include 'data/base.php';
$title = 'Патруль';
include 'data/head.php';
user();
?>
<div class="center"><img src="/imgs/hike.png" /></div>
<?
if ($u['hikeTime'] == 0)
{
  if (isset($_GET['start']))
  {
    $stmt = $go-> prepare('UPDATE `users` SET `hikeTime` = ? WHERE `id` = ?');
    $stmt -> execute([(time()+(3600*3)), $uid]);
    header('location: /hike');
    die();
  }
  ?>
  <div class="dialog">
    <h1 class="human">Шрам</h1>
    <p>
      › Здарова, <?php echo $u['login'];?>, помоги мне.<br/>
      Нужно срочно в туалет сбегать. Сходи за меня в патруль, посмотри по сторонам, может найдешь чего - все твое будет. 
    </p><hr/>
    <div class="list">
      Подробности:<br/>
      › Время в патруле: 3 часа.<br/>
      › Награда: <img src="/imgs/repute.png" width="12px" />, <img src="/imgs/bolts.png" width="12px" /> (больше уровень - больше награда)<br/>
      › Возможная награда: случайный предмет.<br/>
    </div>
    <div class="fights-link fights-orange-link center" style="margin: 5px 0"><a href="?start">Начать патруль</a></div>
  </div>
  <?php
}
elseif ($u['hikeTime'] > time())
{
  ?>
  <div class="col margin-left-right">
    <strong><?php echo downcounter(date('Y-m-j H:i:s', $u['hikeTime']));?></strong><br/>
    <small>Времени до окончания патруля</small>
  </div>
  <div class="dialog">
    <h1 class="pda">КПК</h1>
    <p>
      После окончания патруля возвращайтесь обратно, чтобы получить награду.
    </p>
  </div>
  <?php
}
elseif ($u['hikeTime'] < time())
{
  if (isset($_GET['end']))
  {
    $bolts = mt_rand(1,10) * $u['level'];
    $repute = mt_rand(2,10) * $u['level'];
    $stmt = $go -> prepare('UPDATE `users` SET `hikeTime` = ?, `bolts` = `bolts` + ?, `repute` = `repute` + ? WHERE `id` = ?');
    $stmt -> execute([0, $bolts, $repute, $uid]);
    $exp['groups'] = $grp -> expGive($uid, $repute/2);
    ?>
    <div class="fights fights-about center">
      Твоя награда:<br/>
      <div class="fights-award"><img src="/imgs/bolts.png" width="12px" /> <?php echo $bolts;?></div>
      <div class="fights-award"><img src="/imgs/repute.png" width="12px" /> <?php echo $repute;?></div>
      <?php if ($exp['groups'] != 0): ?>
        <div class="fights-award"><img src="/imgs/repute.png" width="12px" /> ГП: <?php echo $exp['groups'];?></div>
      <?php endif; ?>
      <div class="fights-link fights-orange-link center" style="margin: 5px 0"><a href="/">Вернуться в город</a></div>
    </div>
    <?php
  }
  else
  {
    ?>
    <div class="dialog">
      <h1 class="human">Шрам</h1>
      <p>
        Спасибо за работу, сталкер!<br>
        Твой патруль окончен. Как договаривались, все твое.
      </p>
      <div class="fights-link fights-orange-link center" style="margin: 5px 0"><a href="?end">Забрать награду</a></div>
    </div>
    <?php
  }
}
include 'data/foot.php';
?>
