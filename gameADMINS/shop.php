<?php
include 'data/base.php';
user();

switch ($act)
{
default:
  $title = 'Торговцы';
  include 'data/head.php';
  ?>
  <div style="margin: 5px;">
    <a href="/shop/ammo" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
          <td class="attack-text">
            Палыч<br/>
            <small>
              Покупка амуниции
            </small>
          </td>
        </tr>
      </table>
    </a>
    <a href="/shop/food" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
          <td class="attack-text">
            Зинка<br/>
            <small>
              Столовая
            </small>
          </td>
        </tr>
      </table>
    </a>
    <a href="/shop/change" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
          <td class="attack-text">
            Обмен<br/>
            <small>
              Рубли на болты
            </small>
          </td>
        </tr>
      </table>
    </a>
  </div>
  <?php
break;
case 'ammo':
  $title = 'Экипировка';
  include 'data/head.php';

  echo '<div class="col" style="margin: 0 5px;">Выберите слот амуниции</div>';
  foreach ($slots as $sl)
  {
    ?>
    <div class="fights-link" style="margin: 5px"><a href="/shop/ammo/<?php echo $sl['en'];?>">— <?php echo $sl['ru'];?></a></div>
    <?php
  }
  echo '<div class="fights-link" style="margin: 5px"><a href="/shop">Вернуться к торговцам</a></div>';
break;
case 'viewAmmo':
  $how = trim($_GET['how']);
  if (array_key_exists($how, $slots) == FALSE) die(header('Location: /shop/ammo'));
  $title = $slots[$how]['ru'];
  include 'data/head.php';
  $pages = new Paginator(10, 'page');
  $stmt = $go -> prepare('SELECT `id` FROM `weapons` WHERE `how` = ? and `lvl` <= ? and `slot` = ?');
  $stmt -> execute(['shop', $u['level'], $how]);
  $total = $stmt -> rowCount();
  $pages -> set_total($total);

  if ($total > 0)
  {
    $stmt = $go -> prepare('SELECT * FROM `weapons` WHERE `how` = ? and `lvl` <= ? and `slot` = ? ORDER BY `lvl` DESC '.$pages -> get_limit());
    $stmt -> execute(['shop', $u['level'], $how]);
    $get = $stmt -> fetchAll();

    foreach ($get as $weapon)
    {
      ?>
      <div class="fights fights-about">
        <table width="100%">
          <tr>
            <td width="64px" valign="top" align="center"><img src="/files/<?php echo (file_exists($_SERVER['DOCUMENT_ROOT'].'/files/'.$weapon['slot'].'/'.$weapon['id'].'.png') != FALSE ? $weapon['slot'].'/'.$weapon['id']:$weapon['slot'].'/default');?>.png" title="<?php echo $weapon['name'];?>" /></td>
            <td valign="top">
              <div class="attack-text">
                <h1 class="human"><?php echo $obg -> show_ammo($weapon['id']);?> <span class="small" style="color: #888;"><?php echo $slots[$weapon['slot']]['ru']?></span></h1>
                <small>Продается за <img src="/imgs/<?php echo $weapon['price'];?>.png" width="12px" /> <?php echo $weapon['amount'];?></small>
                <div class="quest-btn" style="margin: 5px 0"><a href="/info/items/<?php echo $weapon['id'];?>">— Подробнее</a></div>
              </div>
            </td>
          </tr>
        </table>
      </div>
      <?php
    }
    if ($total > 10) echo $pages -> page_links();
  }
  else
  {
    show_error('У Палыча сейчас нет ничего, что можно купить из этой амуниции.');
  }
  echo '<div class="fights-link" style="margin: 5px;"><a href="/shop/ammo">Вернуться назад</a></div>';
break;
case 'food':
  $title = 'Столовая';
  include 'data/head.php';
  ?>
  <div class="dialog">
    <h1 class="human">
      Зинка <span class="small" style="color: #888;">повар</span>
    </h1>
    <p>
      › Еды в зоне мало, как и денег, поэтому меняю одно на другое.
    </p>
  </div>
  <?php
  $food = [
    'bread' => [
      'name' => 'Хлеб',
      'type' => 'hp',
      'what' => 20,
      'price' => 'bolts',
      'amount' => ceil(50 * $u['level'] / 2)
    ],
    'fish' => [
      'name' => 'Рыбу',
      'type' => 'hp',
      'what' => 50,
      'price' => 'bolts',
      'amount' => ceil(100 * $u['level'] / 2)
    ],
    'vodka' => [
      'name' => 'Водочку',
      'type' => 'hp',
      'what' => 100,
      'price' => 'rubles',
      'amount' => ceil(1 * $u['level'] / 3)
    ]
  ];
  if (isset($_GET['buy']))
  {
    if (empty($_GET['buy'])) show_error('Выберите, что хотите купить.');
    elseif (array_key_exists($_GET['buy'], $food) == FALSE) show_error('Такого товара нет.');
    {
      if (isset($_GET['buy']) and isset($_GET['ok']))
      {
        if ($u['hp'] == $u['max_hp']) show_error('У вас полное здоровье.');
        elseif ($u[$food[$_GET['buy']]['price']] < $food['amount']) show_error('Вы не можете позволить себе купить это.');
        else
        {
          $hp = $u['max_hp'] * ($food[$_GET['buy']]['what'] / 100);
          $sql = 'UPDATE `users` SET `'.$food[$_GET['buy']]['type'].'` = `'.$food[$_GET['buy']]['type'].'` + ?, `'.$food[$_GET['buy']]['price'].'` = `'.$food[$_GET['buy']]['price'].'` - ? WHERE `id` = ?';
          $stmt = $go -> prepare($sql);
          $stmt -> execute([$hp, $food[$_GET['buy']]['amount'], $uid]);
          $_SESSION['success'] = 'Вы успешно употребили пищу.';
          die(header('Location: ?'));
        }
      }
      else
      {
        ?>
        <div class="fights fights-about center">
          Вы действительно хотите купить «<?php echo $food[$_GET['buy']]['name'];?>» за <?php echo $food[$_GET['buy']]['amount'];?> <img src="/imgs/<?php echo $food[$_GET['buy']]['price'];?>.png" width="12px" alt="*">?
          <div style="margin: 5px 0 0 0;">
            <div class="grid fights-link">
              <div class="six columns ln">
                <a href="/shop/food?buy=<?php echo $_GET['buy'];?>&ok">Купить</a>
              </div>
              <div class="six columns">
                <a href="/shop/food">Отменить</a>
              </div>
            </div>
          </div>
        </div>
        <?php
      }
    }
  }
  ?>
  <div style="margin: 5px;">
    <a href="/shop/food?buy=bread" class="weapon">
      <table width="100%">
        <tr>
          <td width="32px" valign="top"><img src="/files/food/bread.png" width="32px" alt="IMG"></td>
          <td valign="top" class="attack-text">
            Хлеб<br/>
            <small>
              <?php echo $food['bread']['what'];?>% здоровья за <?php echo $food['bread']['amount'];?> <img src="/imgs/<?php echo $food['bread']['price'];?>.png" width="12px" alt="*">
            </small>
          </td>
        </tr>
      </table>
    </a>
    <a href="/shop/food?buy=fish" class="weapon">
      <table width="100%">
        <tr>
          <td width="32px" valign="top"><img src="/files/food/fish.png" width="32px" alt="IMG"></td>
          <td valign="top" class="attack-text">
            Рыба<br/>
            <small>
              <?php echo $food['fish']['what'];?>% здоровья за <?php echo $food['fish']['amount'];?> <img src="/imgs/<?php echo $food['fish']['price'];?>.png" width="12px" alt="*">
            </small>
          </td>
        </tr>
      </table>
    </a>
    <a href="/shop/food?buy=vodka" class="weapon">
      <table width="100%">
        <tr>
          <td width="32px" valign="top"><img src="/files/food/vodka.png" width="32px" alt="IMG"></td>
          <td valign="top" class="attack-text">
            Водочка<br/>
            <small>
              <?php echo $food['vodka']['what'];?>% здоровья за <?php echo $food['vodka']['amount'];?> <img src="/imgs/<?php echo $food['vodka']['price'];?>.png" width="12px" alt="*">
            </small>
          </td>
        </tr>
      </table>
    </a>
  </div>
  <div class="fights-link" style="margin: 5px;"><a href="/shop/">Вернуться назад</a></div>
  <?php
break;
}
include 'data/foot.php';
?>