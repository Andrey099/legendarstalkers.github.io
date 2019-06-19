<?php
include '../data/base.php';
user();

$stmt = $go -> prepare('SELECT `id`,`quest_1_1`,`quest_1_2`,`quest_1_3`,`quest_1_4`,`quest_1_5`,`quest_1_6`,`quest_1_7`,`repute_1`,`success_1` FROM `zone` WHERE `id_user` = ?');
$stmt -> execute([$uid]);
$zona = $stmt -> fetch();

$title = $zones[1]['name'];

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
    'name' => 'Искать документы',
    'energy' => 1,
    'max' => 4,
    'bolt' => 0,
    'repute' => 5,
    'base' => 'quest_1_1'
  ],
  '2' => [
    'name' => 'Сделать замеры излучения',
    'energy' => 1,
    'max' => 3,
    'bolt' => 4,
    'repute' => 4,
    'base' => 'quest_1_2'
  ],
  '3' => [
    'name' => 'Помочь Петровичу в магазине',
    'energy' => 2,
    'max' => 5,
    'bolt' => 3,
    'repute' => 5,
    'base' => 'quest_1_3'
  ],
  '4' => [
    'name' => 'Принять радиосообщение',
    'energy' => 3,
    'max' => 5,
    'bolt' => 0,
    'repute' => 6,
    'base' => 'quest_1_4'
  ],
  '5' => [
    'name' => 'Идти на сигнал о бедствии',
    'energy' => 4,
    'max' => 4,
    'bolt' => 0,
    'repute' => 4,
    'base' => 'quest_1_5'
  ],
  '6' => [
    'name' => 'Разобраться с засадой',
    'energy' => 6,
    'max' => 6,
    'bolt' => 8,
    'repute' => 2,
    'base' => 'quest_1_6'
  ],
  '7' => [
    'name' => 'Спасти доктора',
    'energy' => 8,
    'max' => 7,
    'bolt' => 10,
    'repute' => 1,
    'base' => 'quest_1_7'
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
    $sql = 'UPDATE `zone` SET `'.$quest[$q]['base'].'` = `'.$quest[$q]['base'].'` + ?, `repute_1` = `repute_1` + ? WHERE `id_user` = ?';
    $stmt = $go -> prepare($sql);
    $stmt -> execute([1, $quest[$q]['repute'], $uid]);

    $stmt = $go -> prepare('UPDATE `users` SET `bolts` = `bolts` + ?, `repute` = `repute` + ?, `energy` = `energy` - ?, `exp` = `exp` + ? WHERE `id` = ?');
    $stmt -> execute([$quest[$q]['bolt'], $quest[$q]['repute'], $quest[$q]['energy'], $quest[$q]['repute'], $uid]);

    if ($q == 7 and ($zona['quest_1_7']+1) == $quest[7]['max'])
    {
      $stmt = $go -> prepare('UPDATE `zone` SET `quest_1_1` = ?, `quest_1_2` = ?, `quest_1_3` = ?, `quest_1_4` = ?, `quest_1_5` = ?, `quest_1_6` = ?, `quest_1_7` = ?, `success_1` = `success_1` + ? WHERE `id_user` = ?');
      $stmt -> execute([0,0,0,0,0,0,0,1,$uid]);
      $give_bolts = $give_bolts + 36;
      $give_repute = $give_repute + 18;
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
        <?php if ($q == 7 and ($zona['quest_1_7']+1) == $quest[7]['max']): ?>
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

$stmt = $go -> prepare('SELECT `id`,`quest_1_1`,`quest_1_2`,`quest_1_3`,`quest_1_4`,`quest_1_5`,`quest_1_6`,`quest_1_7`,`repute_1`,`success_1` FROM `zone` WHERE `id_user` = ?');
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
  Локация › <?php echo $zones[1]['name'];?><hr/>
  Репутации в локации: <?php echo $view['repute_1'];?><br/>
  Полных прохождений: <?php echo $view['success_1'];?><hr/>
  Награда за прохождение: 36 болтов / 18 известности.
</div>
<?php 
include '../data/foot.php';