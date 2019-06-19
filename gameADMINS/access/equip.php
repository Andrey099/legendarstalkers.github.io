<?php
include '../data/base.php';
access(3);

switch ($act)
{
default:
  $title = 'Управление экипировкой';
  include '../data/head.php';
  ?>
  <div class="fights-link" style="margin: 5px"><a href="/access/equip/create">Добавить экипировку</a></div>
  <div class="fights-link" style="margin: 5px"><a href="/access/equip/list">Список экипировки</a></div>
  <div class="fights-link" style="margin: 5px"><a href="/access/">Панель управления</a></div>
  <?php
break;
case 'create':
  $title = 'Новая экипировка';
  include '../data/head.php';
  if (isset($_POST['create']))
  {
    $post = [
      'name' => trim($_POST['name']),
      'about' => trim($_POST['about']),
      'slot' => trim($_POST['slot']),
      'quality' => trim($_POST['quality']),
      'how' => trim($_POST['how']),
      'lvl' => abs(intval($_POST['lvl'])),
      'price' => (empty($_POST['price']) or $_POST['how'] != 'shop' ? NULL:$_POST['price']),
      'amount' => (empty($_POST['amount']) or $_POST['how'] != 'shop' ? NULL:$_POST['amount'])
    ];

    $stmt = $go -> prepare('INSERT INTO `weapons` (`name`, `about`, `slot`, `quality`, `how`, `lvl`, `price`, `amount`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt -> execute([$post['name'], $post['about'], $post['slot'], $post['quality'], $post['how'], $post['lvl'], $post['price'], $post['amout']]);
    $next = $go -> lastInsertId();
    $_SESSION['success'] = 'Предмет экипировки успешно создан.';
    die(header('Location: /access/equip/edit/'.$next));
  }
  ?>
  <div class="fights fights-about">
    <form method="POST">
      Название:<br/>
      <input type="text" name="name" placeholder="Введите название..." required/>
      Описание:<br/>
      <textarea name="about" placeholder="Введите описание..."></textarea>
      Слот:<br/>
      <select name="slot" required>
        <option value="boot">Ноги</option>
        <option value="hand">Руки</option>
        <option value="body">Тело</option>
        <option value="head">Голова</option>
        <option value="knife" selected>Нож</option>
        <option value="pistol">Пистолет</option>
        <option value="gun">Автомат</option>
      </select>
      Качество:<br/>
      <select name="quality" required>
        <option value="trash">Помойное</option>
        <option value="normal">Обычное</option>
        <option value="rare">Редкое</option>
        <option value="heroic">Невероятно редкое</option>
        <option value="souvenir">Сувенирное</option>
      </select>
      Как получить:<br/>
      <select name="how" required>
        <option value="shop">Магазин</option>
        <option value="random" selected>Рандом</option>
        <option value="craft">Крафт</option>
      </select>
      Минимальный уровень:<br/>
      <input type="number" name="lvl" min="1" value="1" placeholder="Введите уровень..." required/>
      За что покупается (если магазин):<br/>
      <select name="price">
        <option value="bolts">Болты</option>
        <option value="rubles">Рубли</option>
      </select>
      За сколько покупается (если магазин):<br/>
      <input type="number" name="amount" min="1" placeholder="Введите цену..."/>
      <input type="submit" name="create" value="Добавить предмет">
    </form>
  </div>
  <div class="fights-link" style="margin: 5px;"><a href="/access/equip">Управление экипировкой</a></div>
  <?php
break;
case 'list':
  $title = 'Список предметов';
  include '../data/head.php';
  foreach ($slots as $sl)
  {
    ?>
    <div class="fights-link" style="margin: 5px"><a href="/access/equip/list/<?php echo $sl['en'];?>">— <?php echo $sl['ru'];?></a></div>
    <?php
  }
  echo '<div class="fights-link" style="margin: 5px;"><a href="/access/equip">Управление экипировкой</a></div>';
break;
case 'viewList':
  $how = trim($_GET['how']);
  if (array_key_exists($how, $slots) == FALSE) die(header('Location: /shop/ammo'));
  $title = $slots[$how]['ru'];
  include '../data/head.php';
  $pages = new Paginator(10, 'page');
  $stmt = $go -> prepare('SELECT `id` FROM `weapons` WHERE `slot` = ?');
  $stmt -> execute([$how]);
  $total = $stmt -> rowCount();
  $pages -> set_total($total);

  if ($total > 0)
  {
    $stmt = $go -> prepare('SELECT * FROM `weapons` WHERE `slot` = ? ORDER BY `id` DESC '.$pages -> get_limit());
    $stmt -> execute([$how]);
    $get = $stmt -> fetchAll();

    foreach ($get as $weapon)
    {
      ?>
      <div class="fights fights-about">
        <table width="100%">
          <tr>
            <td width="52px" valign="top" align="center"><img src="/files/<?php echo (file_exists($_SERVER['DOCUMENT_ROOT'].'/files/'.$weapon['slot'].'/'.$weapon['id'].'.png') != FALSE ? $weapon['slot'].'/'.$weapon['id']:$weapon['slot'].'/default');?>.png" title="<?php echo $weapon['name'];?>" /></td>
            <td valign="top">
              <div class="attack-text">
                <h1 class="human"><?php echo $obg -> show_ammo($weapon['id']);?> <span class="small" style="color: #888;"><?php echo $slots[$weapon['slot']]['ru']?></span></h1>
                <div class="quest-btn" style="margin: 5px 0"><a href="/access/equip/edit/<?php echo $weapon['id'];?>">— Изменить</a></div>
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
    show_error('Нет амуниции.');
  }
  echo '<div class="fights-link" style="margin: 5px;"><a href="/access/equip/list">Вернуться назад</a></div>';
break;
case 'edit':
  if (!isset($ids)) die(header('Location: /access/equip'));
  $stmt = $go -> prepare('SELECT * FROM `weapons` WHERE `id` = ? LIMIT 1');
  $stmt -> execute([$ids]);
  $weapon = $stmt -> fetch();
  if ($weapon == FALSE) die(header('Location: /access/equip'));
  $title = $weapon['name'];
  include '../data/head.php';
  if (isset($_POST['image']))
  {
    $path = $_SERVER['DOCUMENT_ROOT'].'/files/'.$weapon['slot'].'/';
    $types = ['image/gif', 'image/png', 'image/jpeg'];
    $size = 1024000;
    if (!in_array($_FILES['images']['type'], $types)) show_error('Недопустимый тип файла');
    elseif ($_FILES['images']['size'] > $size) show_error('Слишком большой размер файла');
    else
    {
      if (!@copy($_FILES['images']['tmp_name'], $path . $weapon['id'] . '.png')) show_error('Ошибка в загрузке.');
      else show_error('Изображение успешно загружено.');
    }
  }
  $stmt = $go -> prepare('SELECT min(`id`) as `min`, max(`id`) as `max` FROM `weapons`');
  $stmt -> execute([]);
  $prenext = $stmt -> fetch();
  ?>
  <div style="margin: 5px;">
    <div class="grid fights-link">
      <div class="six columns ln">
        <a href="<?php echo ($ids <= $prenext['min'] ? '#':'/access/equip/edit/'.($ids - 1));?>">< Прошлый </a>
      </div>
      <div class="six columns">
        <a href="<?php echo ($ids >= $prenext['max'] ? '#':'/access/equip/edit/'.($ids + 1));?>">Следующий ></a>
      </div>
    </div>
  </div>
  <div class="col" style="margin: 2px 5px">Изображение</div>
  <div class="fights fights-about">
    <form method="POST" enctype="multipart/form-data">
      <table width="100%">
        <tr>
          <td width="64px" valign="top" align="center"><img src="/files/<?php echo (file_exists($_SERVER['DOCUMENT_ROOT'].'/files/'.$weapon['slot'].'/'.$weapon['id'].'.png') != FALSE ? $weapon['slot'].'/'.$weapon['id']:$weapon['slot'].'/default');?>.png" title="<?php echo $weapon['name'];?>" /></td>
          <td valign="top">
            <div class="attack-text">
              <input name="images" type="file">
              <input type="submit" name="image" value="Сменить изображение">
            </div>
          </td>
        </tr>
      </table>
    </form>
  </div>
  <div class="col" style="margin: 2px 5px">Характеристики</div>
  <?php
  $slot = [
    'boot' =>     'Удар с ноги',
    'hand' =>     'Удар с руки',
    'head' =>     'Удар с головы',
    'knife' =>    'Удар с ножа',
    'pistol' =>   'Выстрел с пистолета',
    'gun' =>      'Выстрел с автомата',
    'power' =>    'Сила',
    'dash' =>     'Рывок',
    'defense' =>  'Защита',
    'hp' =>       'Макс. здоровье',
    'energy' =>   'Макс. энергия'
  ];
  if (isset($_GET['create']))
  {
    if (isset($_POST['slots']))
    {
      $post = [
        'slot' => trim($_POST['slot']),
        'amount' => round(intval($_POST['amount']))
      ];

      if (array_key_exists($post['slot'], $slot) == FALSE) show_error('Ошибка в выборе слота');
      elseif (empty($post['amount'])) show_error('Введите сколько прибавлять');
      else
      {
        $stmt = $go -> prepare('SELECT `id` FROM `weapons_stats` WHERE `id_weapon` = ? and `atrb` = ? LIMIT 1');
        $stmt -> execute([$ids, $post['slot']]);
        $atb = $stmt -> fetch();

        if ($atb != FALSE)
        {
          $stmt = $go -> prepare('UPDATE `weapons_stats` SET `bonus` = ? WHERE `id` = ?');
          $stmt -> execute([$post['amount'], $atb['id']]);
        }
        else
        {
          $stmt = $go -> prepare('INSERT INTO `weapons_stats` (`id_weapon`, `atrb`, `bonus`) VALUES (?, ?, ?)');
          $stmt -> execute([$ids, $post['slot'], $post['amount']]);
        }
        $_SESSION['success'] = 'Характеристика успешно добавлена.';
        die(header('Location: ?'));
      }
    }
    ?>
    <div class="fights fights-about">
      К чему дается бонус<br/>
      <form method="POST">
        <select name="slot">
          <?php foreach ($slot as $s => $in): ?>
            <option value="<?php echo $s;?>"><?php echo $in;?></option>
          <?php endforeach; ?>
        </select>
        Сколько дается<br/>
        <input type="number" value="0" name="amount" />
        <input type="submit" name="slots" value="Добавить">
      </form>
      <div class="fights-link" style="margin: 5px 0 0 0;"><a href="?">Скрыть форму</a></div>
    </div>
    <?php
  }
  elseif (isset($_GET['e']))
  {
    if (empty($_GET['e'])) show_error('Выберите характеристику.');
    elseif (!is_numeric($_GET['e'])) show_error('Ошибка в запросе');
    else
    {
      $stmt = $go -> prepare('SELECT * FROM `weapons_stats` WHERE `id_weapon` = ? and `id` = ?');
      $stmt -> execute([$ids, $_GET['e']]);
      $st = $stmt -> fetch();
      if ($st == FALSE) show_error('Эта характеристика не от этого предмета или ее не существует');
      else
      {
        if (isset($_POST['slots']))
        {
          $post = [
            'slot' => trim($_POST['slot']),
            'amount' => round(intval($_POST['amount']))
          ];

          if (array_key_exists($post['slot'], $slot) == FALSE) show_error('Ошибка в выборе слота');
          elseif (empty($post['amount'])) show_error('Введите сколько прибавлять');
          else
          {
            $stmt = $go -> prepare('UPDATE `weapons_stats` SET `bonus` = ?, `atrb` = ? WHERE `id` = ?');
            $stmt -> execute([$post['amount'], $post['slot'], $st['id']]);
            $_SESSION['success'] = 'Характеристика успешно изменена.';
            die(header('Location: ?'));
          }
        }
        ?>
        <div class="fights fights-about">
        К чему дается бонус<br/>
        <form method="POST">
          <select name="slot">
            <?php foreach ($slot as $s => $in): ?>
              <option value="<?php echo $s;?>" <?php echo ($st['atrb'] == $s ? 'selected':NULL);?>><?php echo $in;?></option>
            <?php endforeach; ?>
          </select>
          Сколько дается<br/>
          <input type="number" value="<?php echo $st['bonus'];?>" name="amount" />
          <input type="submit" name="slots" value="Изменить">
        </form>
        <div class="fights-link" style="margin: 5px 0 0 0;"><a href="?">Скрыть форму</a></div>
      </div>
        <?php
      }
    }
  }
  elseif (isset($_GET['d']))
  {
    if (empty($_GET['d'])) show_error('Выберите характеристику.');
    elseif (!is_numeric($_GET['d'])) show_error('Ошибка в запросе');
    else
    {
      $stmt = $go -> prepare('SELECT * FROM `weapons_stats` WHERE `id_weapon` = ? and `id` = ?');
      $stmt -> execute([$ids, $_GET['d']]);
      $st = $stmt -> fetch();
      if ($st == FALSE) show_error('Эта характеристика не от этого предмета или ее не существует');
      elseif (isset($_GET['d']) and isset($_GET['ok']))
      {
        $stmt = $go -> prepare('DELETE FROM `weapons_stats` WHERE `id` = ?');
        $stmt -> execute([$_GET['d']]);
        $_SESSION['success'] = 'Характеристика успешно удалена.';
        die(header('Location: ?'));
      }
      else
      {
        ?>
        <div class="fights fights-about center" style="margin: 5px;">
          Вы действительно хотите удалить — <?php echo $slot[$st['atrb']];?> <?php echo ($st['bonus'] > 0 ? '+':NULL).$st['bonus'];?>?
          <div style="margin: 5px 0 0 0;">
            <div class="grid fights-link">
              <div class="six columns ln">
                <a href="?d=<?php echo $st['id']?>&ok">Удалить</a>
              </div>
              <div class="six columns">
                <a href="?">Оставить</a>
              </div>
            </div>
          </div>
        </div>
        <?php
      }
    }
  }

  $stmt = $go -> prepare('SELECT * FROM `weapons_stats` WHERE `id_weapon` = ?');
  $stmt -> execute([$ids]);
  $count = $stmt -> rowCount();
  $stats = $stmt -> fetchAll();
  if ($count > 0)
  {
    ?>
    <div class="fights fights-about">
      <?php
      foreach ($stats as $s)
      {
        ?>
        <?php echo $slot[$s['atrb']]?>: <?php echo ($s['bonus'] > 0 ? '+':NULL).$s['bonus'];?> (<a href="/access/equip/edit/<?php echo $ids;?>?e=<?php echo $s['id'];?>">Изменить</a> / <a href="/access/equip/edit/<?php echo $ids;?>?d=<?php echo $s['id'];?>">Удалить</a>)<br/>
        <?php
      }
      ?>
    </div>
    <?php
  }
  else
  {
    show_error('Характеристики еще не добавлены.');
  }

  if (isset($_POST['edit']))
  {
    $post = [
      'name' => trim($_POST['name']),
      'about' => trim($_POST['about']),
      'slot' => trim($_POST['slot']),
      'quality' => trim($_POST['quality']),
      'how' => trim($_POST['how']),
      'lvl' => abs(intval($_POST['lvl'])),
      'price' => trim($_POST['price']),
      'amount' => round(intval($_POST['amount']))
    ];

    $stmt = $go -> prepare('UPDATE `weapons` SET 
      `name` = ?, 
      `about` = ?, 
      `slot` = ?, 
      `quality` = ?, 
      `how` = ?, 
      `lvl` = ?, 
      `price` = ?, 
      `amount` = ? 
      WHERE `id` = ?');
    $stmt -> execute([$post['name'], $post['about'], $post['slot'], $post['quality'], $post['how'], $post['lvl'], $post['price'], $post['amount'], $ids]);
    $_SESSION['success'] = 'Информация успешно обновлена.';
    die(header('Location: ?'));
  }
  ?>
  <div class="fights-link" style="margin: 5px;"><a href="?create">Добавить характеристику</a></div>
  <div class="col" style="margin: 2px 5px">Изменение предмета</div>
  <div class="fights fights-about">
    <form method="POST">
      Название:<br/>
      <input type="text" name="name" value="<?php echo $weapon['name'];?>" placeholder="Введите название..." required/>
      Описание:<br/>
      <textarea name="about" rows="3" placeholder="Введите описание..."><?php echo $weapon['about'];?></textarea>
      Слот:<br/>
      <select name="slot" required>
        <option value="boot" <?php echo ($weapon['slot'] == 'boot' ? 'selected':NULL);?>>Ноги</option>
        <option value="hand" <?php echo ($weapon['slot'] == 'hand' ? 'selected':NULL);?>>Руки</option>
        <option value="body" <?php echo ($weapon['slot'] == 'body' ? 'selected':NULL);?>>Тело</option>
        <option value="head" <?php echo ($weapon['slot'] == 'head' ? 'selected':NULL);?>>Голова</option>
        <option value="knife" <?php echo ($weapon['slot'] == 'knife' ? 'selected':NULL);?>>Нож</option>
        <option value="pistol" <?php echo ($weapon['slot'] == 'pistol' ? 'selected':NULL);?>>Пистолет</option>
        <option value="gun" <?php echo ($weapon['slot'] == 'gun' ? 'selected':NULL);?>>Автомат</option>
      </select>
      Качество:<br/>
      <select name="quality" required>
        <option value="trash" <?php echo ($weapon['quality'] == 'trash' ? 'selected':NULL);?>>Помойное</option>
        <option value="normal" <?php echo ($weapon['quality'] == 'normal' ? 'selected':NULL);?>>Обычное</option>
        <option value="rare" <?php echo ($weapon['quality'] == 'rare' ? 'selected':NULL);?>>Редкое</option>
        <option value="heroic" <?php echo ($weapon['quality'] == 'heroic' ? 'selected':NULL);?>>Невероятно редкое</option>
        <option value="souvenir" <?php echo ($weapon['quality'] == 'souvenir' ? 'selected':NULL);?>>Сувенирное</option>
      </select>
      Как получить:<br/>
      <select name="how" required>
        <option value="shop" <?php echo ($weapon['how'] == 'shop' ? 'selected':NULL);?>>Магазин</option>
        <option value="random" <?php echo ($weapon['how'] == 'random' ? 'selected':NULL);?>>Рандом</option>
        <option value="craft" <?php echo ($weapon['how'] == 'craft' ? 'selected':NULL);?>>Крафт</option>
      </select>
      Минимальный уровень:<br/>
      <input type="number" name="lvl" min="1" value="<?php echo $weapon['lvl'];?>" placeholder="Введите уровень..." required/>
      За что покупается (если магазин):<br/>
      <select name="price">
        <option value="bolts" <?php echo ($weapon['price'] == 'bolts' ? 'selected':NULL);?>>Болты</option>
        <option value="rubles" <?php echo ($weapon['price'] == 'rubles' ? 'selected':NULL);?>>Рубли</option>
      </select>
      За сколько покупается (если магазин):<br/>
      <input type="number" name="amount" min="1" value="<?php echo $weapon['amount'];?>" placeholder="Введите цену..."/>
      <input type="submit" name="edit" value="Изменить предмет">
    </form>
  </div>
  <div class="fights-link" style="margin: 5px;"><a href="/access/equip/create">Новый элемент экипировки</a></div>
  <div class="fights-link" style="margin: 5px;"><a href="/access/equip">Управление экипировкой</a></div>
  <?php
break;
}
include '../data/foot.php';