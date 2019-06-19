<?php
include 'data/base.php';
$title = 'Авторизация';
include 'data/head.php';
guest();
?>
  <div class="center margin"><img src="/imgs/blockpost.png" /></div>
  <div class="dialog">
      <h1 class="human">Блокпост</h1>
      <p>
        <?php
        if (isset($_POST['form']))
        {
          $_SESSION['nick'] = $_POST['nickname'];
          if (empty($_POST['nickname']) or empty($_POST['password'])) echo '› Ты что издеваться вздумал? Быстро назови свои имя и пароль или я открою огонь.';
          else
          {
            $stmt = $go -> prepare('SELECT `login`, `password` FROM `users` WHERE `login` = ? AND `password` = ? LIMIT 1');
            $stmt -> execute([$_POST['nickname'], md5($_POST['password'])]);
            $total = $stmt -> rowCount();

            if ($total == 0) echo '› Хм... Что-то я не вижу таких данных в списке наших сталкеров.';
            else
            {
              unset($_SESSION['nick']);
              $_SESSION['success'] = 'Вы снова вернулись в зону.';
              setcookie('login', $_POST['nickname'], time()+86400*365, '/');
              setcookie('password', md5($_POST['password']), time()+86400*365, '/');
              header('Location: /');
            }
          }
        }
        else
        {
          echo '› Дальше проход закрыт! Введи свои данные в форму ниже, чтобы пройти блокпост и попасть в зону.';
        }
        ?>
      </p>
    </div>
    <div class="info">
      <form method="post" action="">
        <input type="text" name="nickname" value="<?php echo (!empty($_SESSION['nick']) ? $_SESSION['nick']:'');?>" placeholder="Введите имя..." /><br>
        <input type="password" name="password" placeholder="Введите пароль...">
        <input type="submit" name="form" value="Авторизоваться" />
      </form>
    </div>
<?php
include 'data/foot.php';
?>
