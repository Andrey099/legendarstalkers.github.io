<?php
include 'data/base.php';
$title = 'Окрестности';
include 'data/head.php';
user();

$stmt = $go -> prepare('SELECT `id`, `repute_1`, `repute_2`, `success_1` FROM `zone` WHERE `id_user` = ?');
$stmt -> execute([$uid]);
$zona = $stmt -> fetch();

if (!isset($zona['id']))
{
  $stmt = $go -> prepare('INSERT INTO `zone` (`id_user`) VALUES (?)');
  $stmt -> execute([$uid]);
  header('Location: /zone');
  die();
}
else
{
  $stmt = $go -> prepare('SELECT `id_user`, `repute_1` FROM `zone` ORDER BY `repute_1` DESC LIMIT 1');
  $stmt -> execute();
  $zona_1_lider = $stmt -> fetch();
  ?>
  <div class="info" style="border: 1px solid #333; padding: 2px 5px;">
    <h1 class="human">
      <span class="pull-right" style="text-align: right;">
        <span class="small" style="color: #888;">репутация</span><br/>
        <?php echo $zona['repute_1'];?>
      </span>
      <span class="small" style="color: #888;">локация</span><br/>
      <?php echo $zones[1]['name']?>
    </h1>
    <div style="margin: 5px 0;">
      <div class="grid">
        <div class="six columns">
          <?php echo show_user($zona_1_lider['id_user']);?><br/>
          <span class="small" style="color: #888;"><?php echo numb($zona_1_lider['repute_1']);?> реп.</span>
        </div>
        <div class="six columns">
          <div class="quest-btn" style="margin: 2px 0"><a href="/zone/1">Отправиться</a></div>
        </div>
      </div>
    </div>
  </div>
  <?php
  $stmt = $go -> prepare('SELECT `id_user`, `repute_2` FROM `zone` ORDER BY `repute_2` DESC LIMIT 1');
  $stmt -> execute();
  $zona_2_lider = $stmt -> fetch();
  ?>
  <div class="info" style="border: 1px solid #333; padding: 2px 5px;">
    <h1 class="human">
      <span class="pull-right" style="text-align: right;">
        <span class="small" style="color: #888;">репутация</span><br/>
        <?php echo $zona['repute_2'];?>
      </span>
      <span class="small" style="color: #888;">локация</span><br/>
      <?php echo $zones[2]['name']?>
    </h1>
    <div style="margin: 5px 0;">
      <div class="grid">
        <div class="<?php echo ($zona['success_1'] == 0 ? 'twelve':'six');?> columns">
          <?php echo show_user($zona_2_lider['id_user']);?><br/>
          <span class="small" style="color: #888;"><?php echo numb($zona_2_lider['repute_2']);?> реп.</span>
        </div>
        <?php if ($zona['success_1'] >= 1): ?>
        <div class="six columns">
          <div class="quest-btn" style="margin: 2px 0"><a href="/zone/2">Отправиться</a></div>
        </div>
        <?php endif;?>
      </div>
    </div>
  </div>
  <?php
}
include 'data/foot.php';
?>