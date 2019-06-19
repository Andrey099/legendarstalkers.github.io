<?php
include '../data/base.php';
user();

$stmt = $go -> prepare('SELECT `id`,`quest_2_1`,`quest_2_2`,`quest_2_3`,`quest_2_4`,`quest_2_5`,`quest_2_6`,`quest_2_7`,`repute_2`,`success_2`, `success_1` FROM `zone` WHERE `id_user` = ?');
$stmt -> execute([$uid]);
$zona = $stmt -> fetch();

if ($zona['success_1'] < 1)
{
  $_SESSION['error'] = 'Сначала выполните все задания в локации "'.$zones[1]['name'].'".';
  die(header('Location: /zone'));
}

$title = $zones[2]['name'];

if (!isset($_GET['quest'])) include '../data/head.php';

if (!isset($zona['id']))
{
  $stmt = $go -> prepare('INSERT INTO `zone` (`id_user`) VALUES (?)');
  $stmt -> execute([$uid]);
  header('Location: /zone/1');
  die();
}

// Настройки для заданий
$quest = [
  '1' => [
    'name' => 'Охота на Химеру',
    'energy' => 5,
    'max' => 4,
    'bolt' => 2,
    'repute' => 7,
    'base' => 'quest_2_1'
  ],
  '2' => [
    'name' => 'Исследовать растение',
    'energy' => 9,
    'max' => 3,
    'bolt' => 8,
    'repute' => 5,
    'base' => 'quest_2_2'
  ],
  '3' => [
    'name' => 'Выслушать байку',
    'energy' => 1,
    'max' => 5,
    'bolt' => 0,
    'repute' => 3,
    'base' => 'quest_2_3'
  ],
  '4' => [
    'name' => 'Найти инструмент',
    'energy' => 4,
    'max' => 5,
    'bolt' => 4,
    'repute' => 2,
    'base' => 'quest_2_4'
  ],
  '5' => [
    'name' => 'Вернуть анализатор',
    'energy' => 3,
    'max' => 4,
    'bolt' => 2,
    'repute' => 3,
    'base' => 'quest_2_5'
  ],
  '6' => [
    'name' => 'Осмотреть схрон',
    'energy' => 7,
    'max' => 6,
    'bolt' => 10,
    'repute' => 7,
    'base' => 'quest_2_6'
  ],
  '7' => [
    'name' => 'Пересчитать патроны',
    'energy' => 10,
    'max' => 7,
    'bolt' => 12,
    'repute' => 12,
    'base' => 'quest_2_7'
  ]
];

if (isset($_GET['quest']))
{
  $q = abs(intval($_GET['quest']));
  $b = $q - 1;

  if (empty($q)) $error[] = 'Выберите задание.'; // Если не передан id квеста
  elseif (!is_numeric($q)) $error[] = 'Ошибка в запросе.'; // если id не является числом
  elseif (array_key_exists($q, $quest) == FALSE) $error[] = 'Такого задания не существует';
  elseif ($zona[$quest[$q]['base']] >= $quest[$q]['max']) $error[] = 'Вы уже прошли это задание.';
  elseif ($q != 1 and $zona[$quest[$b]['base']] < $quest[$b]['max']) $error[] = 'Сначала пройдите предыдущее задание.';
  elseif ($u['energy'] < $quest[$q]['energy'])  $error[] = 'Недостаточно энергии.';

  if (empty($error))
  {
    $give_bolts = $quest[$q]['bolt'];
    $give_repute = $quest[$q]['repute'];
    $sql = 'UPDATE `zone` SET `'.$quest[$q]['base'].'` = `'.$quest[$q]['base'].'` + ?, `repute_2` = `repute_2` + ? WHERE `id_user` = ?';
    $stmt = $go -> prepare($sql);
    $stmt -> execute([1, $quest[$q]['repute'], $uid]);

    $stmt = $go -> prepare('UPDATE `users` SET `bolts` = `bolts` + ?, `repute` = `repute` + ?, `energy` = `energy` - ?, `exp` = `exp` + ? WHERE `id` = ?');
    $stmt -> execute([$quest[$q]['bolt'], $quest[$q]['repute'], $quest[$q]['energy'], $quest[$q]['repute'], $uid]);

    if ($q == 7 and ($zona['quest_2_7']+1) == $quest[7]['max'])
    {
      $stmt = $go -> prepare('UPDATE `zone` SET `quest_2_1` = ?, `quest_2_2` = ?, `quest_2_3` = ?, `quest_2_4` = ?, `quest_2_5` = ?, `quest_2_6` = ?, `quest_2_7` = ?, `success_2` = `success_2` + ? WHERE `id_user` = ?');
      $stmt -> execute([0,0,0,0,0,0,0,1,$uid]);
      $give_bolts = $give_bolts + 72;
      $give_repute = $give_repute + 36;
    }
    $exp['groups'] = $grp -> expGive($uid, $give_repute);
    include '../data/head.php';
    ?>
    <div class="dialog">
      <h1 class="pda">Награда</h1>
      <p>
        <?php echo ($give_bolts > 0 ? '› <img src="/imgs/bolts.png" width="12px" /> Болты: + '.$give_bolts.'<br/>':'')?>
        <?php echo ($give_repute > 0 ? '› <img src="/imgs/repute.png" width="12px" /> Репутация: + '.$give_repute.'<br/>':'')?>
        <?php if ($exp['groups'] != 0): ?>
        › <img src="/imgs/repute.png" width="12px" /> Опыт ГП: + <?php echo $exp['groups'];?><br/>
        <?php endif;?>
        <?php echo ($quest[$q]['energy'] > 0 ? '› <img src="/imgs/energy.png" width="12px" /> Энергия: - '.$quest[$q]['energy'].'<br/>':'')?>
        <?php if ($q == 7 and ($zona['quest_2_7']+1) == $quest[7]['max']): ?>
          <div class="about small">
            Мои поздравления!<br/>
            Вы успешно выполнили все задания на этой локации.
          </div>
        <?php endif;?>
      </p>
    </div>
    <?php
  }
  else
  {
    include '../data/head.php';
    echo show_error($error);
  }
}

$stmt = $go -> prepare('SELECT `id`,`quest_2_1`,`quest_2_2`,`quest_2_3`,`quest_2_4`,`quest_2_5`,`quest_2_6`,`quest_2_7`,`repute_2`,`success_2` FROM `zone` WHERE `id_user` = ?');
$stmt -> execute([$uid]);
$view = $stmt -> fetch();

for ($q = 1; $q <= count($quest); $q++)
{
  if ($q != 1) $back = $q - 1;
  ?>
  <div class="info" style="border: 1px solid #333; padding: 2px 5px;">
    <h1 class="human">
      <span class="pull-right" style="text-align: right;">
        <?php echo $quest[$q]['energy'];?> ед.<br>
        <span class="small" style="color: #888;">энергия</span>
      </span>
      <?php echo $quest[$q]['name'];?><br>
      <span class="small" style="color: #888;">задание</span>
    </h1>
    <div class="grid" style="margin: 2px 0">
      <?php echo show_quest($view[$quest[$q]['base']], $quest[$q]['max']); ?>
    </div>
    <div style="margin: 5px 0;">
      <div class="grid">
        <div class="six columns">
          <?php
          if ($q == 1)
          {
            if ($view[$quest[$q]['base']] < $quest[$q]['max'])
            {
              echo '<div class="quest-btn" style="margin: 2px 0"><a href="?quest='.$q.'">Выполнить задание</a></div>';
              $show = 1;
            }
          }
          else
          {
            if ($view[$quest[$back]['base']] == $quest[$back]['max'])
            {
              if ($view[$quest[$q]['base']] < $quest[$q]['max'])
              {
                echo '<div class="quest-btn" style="margin: 2px 0"><a href="?quest='.$q.'">Выполнить задание</a></div>';
                $show = 1;
              }
            }
          }
          ?>
        </div>
        <?php if (isset($show)):?>
        <div class="six columns">
          <h1 class="human">
             <?php echo ($quest[$q]['bolt'] > 0 ? '<img src="/imgs/bolts.png" width="12px" /> '.$quest[$q]['bolt']: '');?> <?php echo ($quest[$q]['repute'] > 0 ? '<img src="/imgs/repute.png" width="12px" /> '.$quest[$q]['repute']: '');?><br/>
            <span class="small" style="color: #888;">твоя награда</span>
          </h1>
        </div>
        <?php endif;?>
      </div>
    </div>
  </div>
  <?php
  unset($show);
}
?>
<div class="list margin margin-left-right">
  Локация › <?php echo $zones[2]['name'];?><hr/>
  Репутации в локации: <?php echo $view['repute_2'];?><br/>
  Полных прохождений: <?php echo $view['success_2'];?><hr/>
  Награда за прохождение: 72 болтов / 36 известности.
</div>
<?php 
include '../data/foot.php';