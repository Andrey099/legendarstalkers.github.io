<?php
include 'data/base.php';
user();

switch ($act)
{
default:

break;

case 'background':
$title = 'Локация';
include 'data/head.php';
$stmt = $go -> prepare('SELECT * FROM `background` WHERE `id` = ? LIMIT 1');
$stmt -> execute([$ids]);
$bg = $stmt -> fetch();

if (!isset($bg['id']))
{
  ?>
  <div class="dialog">
    <h1 class="pda">КПК</h1>
    <p>
      › Я не нашла никакой информации о этом жилище.
    </p>
  </div>
  <?php
}
else
{
  ?>
  <div class="background-block" style="background: url(/files/background/<?php echo $bg['background'];?>) center no-repeat;"></div>
  <div class="fights fights-about">
    ID › <?php echo $bg['id'];?><br/>
    Локация › <?php echo $bg['name'];?><br/>
    <?php echo ($bg['about'] != NULL ? 'Способ получения › '.$bg['about']:NULL);?>
  </div>
  <?
}

break;

case 'objects':
$title = 'Предмет';
include 'data/head.php';
if (!is_numeric($ids)) show_error('Ошибка в запросе');
else
{
  $info = $obg -> getObject($ids);
  if ($info == 0)
  {
    ?>
    <div class="dialog">
      <h1 class="pda">КПК</h1>
      <p>
        › Я не нашла никакой информации о этом предмете.
      </p>
    </div>
    <?php
  }
  else
  {
    if (isset($_SERVER['HTTP_REFERER'])) $back = $_SERVER['HTTP_REFERER'];
    else $back = '/info';

    $types = [
      'hp' => 'Медикамент',
      'energy' => 'Энергетик',
      'key' => 'Медаль'
    ];
    ?>
    <div class="fights fights-about">
      <table width="100%">
        <tr>
          <td width="64px" valign="top"><img src="/files/objects/<?php echo (file_exists($_SERVER['DOCUMENT_ROOT'].'/files/objects/'.$info['id'].'.png') != FALSE ? $info['id']:'default');?>.png" /></td>
          <td valign="top">
            <div class="attack-text">
              <?php echo $info['name'];?> [ID: <?php echo $info['id'];?>]<br/>
              <small>
                <?php echo $types[$info['types']];?>
              </small>
            </div>
          </td>
        </tr>
      </table><hr/>
      Описание › <?php echo $info['about'];?><br/>
    </div>
    <div class="fights-link" style="margin: 5px;"><a href="<?php echo $back;?>">Вернуться назад</a></div>
    <?
  }
}
break;

case 'items':
$stmt = $go -> prepare('SELECT * FROM `weapons` WHERE `id` = ? LIMIT 1');
$stmt -> execute([$ids]);
$wp = $stmt -> fetch();

$title = ($wp == FALSE ? 'Амуниция':$wp['name']);
include 'data/head.php';

if (!isset($wp['id']))
{
  ?>
  <div class="dialog">
    <h1 class="pda">КПК</h1>
    <p>
      › Я не нашла никакой информации о этом предмете.
    </p>
  </div>
  <?php
}
else
{
  $info = $wpn -> getWeapon($ids);
  $slot = [
    'boot' => ['name' => 'Ноги', 'info' => 'Удар с ноги'],
    'hand' => ['name' => 'Руки', 'info' => 'Удар с руки'],
    'body' => ['name' => 'Тело', 'info' => '[x]'],
    'head' => ['name' => 'Голова', 'info' => 'Удар с головы'],
    'knife' => ['name' => 'Нож', 'info' => 'Удар с ножа'],
    'pistol' => ['name' => 'Пистолет', 'info' => 'Выстрел с пистолета'],
    'gun' => ['name' => 'Автомат', 'info' => 'Выстрел с автомата'],
    'power' => ['name' => 'Сила', 'info' => 'Сила'],
    'dash' => ['name' => 'Рывок', 'info' => 'Рывок'],
    'defense' => ['name' => 'Защита', 'info' => 'Защита'],
    'hp' => ['name' => 'Максимальное здоровье', 'info' => 'Максимальное здоровье'],
    'energy' => ['name' => 'Максимальная энергия', 'info' => 'Максимальная энергия']
  ];
  $quality = [
    'trash' => 'Помойное',
    'normal' => 'Обычное',
    'rare' => 'Редкое',
    'heroic' => 'Невероятно редкое',
    'souvenir' => 'Сувенирное'
  ];
  $how = [
    'shop' => 'покупка у торговца',
    'random' => 'случайное выпадение',
    'craft' => 'сборка'
  ];
  if (isset($_SERVER['HTTP_REFERER'])) $back = $_SERVER['HTTP_REFERER'];
  else $back = '/info';
  ?>
  <div class="fights fights-about">
    <table width="100%">
      <tr>
        <td width="64px" valign="top"><img src="/files/<?php echo $info['slot'];?>/<?php echo $info['id'];?>.png" title="<?php echo $info['name'];?>" /></td>
        <td valign="top">
          <div class="attack-text">
            <?php echo $info['name'];?> [ID: <?php echo $info['id'];?>]<br/>
            <small>
              <?php echo $slot[$info['slot']]['name'];?><br/>
              - <?php echo $quality[$info['quality']];?> качество
            </small>
          </div>
        </td>
      </tr>
    </table><hr/>
    <?php echo ($info['about'] != NULL ? 'Описание › '.$info['about'].'<br/>':NULL);?>
    Как получить: <?php echo $how[$info['how']];?><br/>
    <?php if (array_key_exists('stats', $info) == TRUE): ?>
      <strong>Характеристики:</strong><br/>
      <?php for ($w = 1; $w <= count($info['stats']); $w++):?>
        <?php echo $slot[$info['stats'][$w]['atrb']]['info'];?>: <?php echo ($info['stats'][$w]['bonus'] > 0 ? '+':NULL).$info['stats'][$w]['bonus'];?><br/>
      <?php endfor;?>
    <?php endif;?>
    <strong>Требования:</strong><br/>
    Минимальный уровень: <?php echo $info['lvl'];?><br/>
    <?php
    if ($info['lvl'] <= $u['level'])
    {
      if (isset($_GET['buy']))
      {
        if (isset($_GET['buy']) and isset($_GET['ok']))
        {
          if ($info['amount'] > $u[$info['price']]) show_error('Недостаточно валюты для покупки');
          else
          {
            $stmt = $go -> prepare('INSERT INTO `weapons_users` (`id_user`, `id_weapon`, `dateAdd`, `used`) VALUES (?, ?, ?, ?)');
            $stmt -> execute([$uid, $info['id'], time(), 0]);
            $sql = 'UPDATE `users` SET `'.$info['price'].'` = `'.$info['price'].'` - ? WHERE `id` = ?';
            $stmt = $go -> prepare($sql);
            $stmt -> execute([$info['amount'], $uid]);
            $_SESSION['success'] = $info['name'].' успешно куплен и находится в Вашем инвентаре.';
            die(header('Location: /info/items/'.$info['id']));
          }
        }
        else
        {
          ?>
          <div class="fights fights-about center" style="margin: 5px 0;">
            Вы действительно хотите купить <?php echo $info['name'];?> за <img src="/imgs/<?php echo $info['price'];?>.png" width="12px" /> <?php echo $info['amount'];?>?
            <div style="margin: 5px 0 0 0;">
              <div class="grid fights-link">
                <div class="six columns ln">
                  <a href="?buy&ok">Купить</a>
                </div>
                <div class="six columns">
                  <a href="?">Отказаться</a>
                </div>
              </div>
            </div>
          </div>
          <?php
        }
      }
      else
      {
        ?>
        <div class="fights-link fights-orange-link" style="margin: 5px 0;"><a href="?buy">— Купить за <img src="/imgs/<?php echo $info['price'];?>.png" width="12px" /> <?php echo $info['amount'];?></a></div>
        <?php
      }
    }
    ?>
  </div>
  <div class="fights-link" style="margin: 5px;"><a href="<?php echo $back;?>">Вернуться назад</a></div>
  <?
}

break;
}

include 'data/foot.php';
?>