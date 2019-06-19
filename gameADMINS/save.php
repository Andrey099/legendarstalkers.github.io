<?php
include 'data/base.php';
include 'data/head.php';
user();
if ($u['save'] == 1 or $u['start'] != 2)
{
  ?>
  <div class="center"><img src="/imgs/home.png" /></div>
  <div class="dialog">
    <h1 class="human">Шрам</h1>
    <p>
      › <?php echo $u['login'];?>, ты что тут забыл? Вали отсюда, пока стрелять не начал.
    </p>
    <h1 class="you">Вы</h1>
    <p>
      › Понял, ухожу.
    </p>
  </div>
  <a class="button" href="/">В деревню</a>
  <?php
}
else
{
  if (isset($_REQUEST['next']))
  {
    if (empty($_POST['pass']) or empty($_POST['repass'])) $error[] = 'Введите пароли.';
    elseif ($_POST['pass'] != $_POST['repass']) $error[] = 'Пароли не совпадают.';
    elseif (strlen($_POST['pass']) < 6) $error[] = 'Пароль не может быть короче 6 символов.';
    elseif (strlen($_POST['pass']) > 32) $error[] = 'Пароль не может быть длинее 32 символов.';

    if (!empty($error))
    {
      show_error($error);
    }
    else
    {
      $stmt = $go -> prepare('UPDATE `users` SET `password` = ?, `rubles` = `rubles` + ?, `save` = ? WHERE `id` = ?');
      $stmt -> execute([md5($_POST['pass']), 10, 1, $uid]);
      setcookie('password', md5($_POST['pass']), time() + 3600*24*30*12, '/');
      $_SESSION['success'] = 'Аккаунт успешно сохранен.';
      header('Location: /');
      die();
    }
  }
  ?>
  <div class="info">
    <div class="about center">Сохрани свой аккаунт и получи бонусом 10 рублей</div>
    <form method="post">
      Введите пароль [Мин.:6/Макс.:32]:<br/>
      <small>будет использоваться для входа в игру</small>
      <input type="password" name="pass" placeholder="Введите пароль..." />
      Введите пароль еще раз:
      <input type="password" name="repass" placeholder="Введите пароль еще раз..." />
      <input type="submit" name="next" value="Сохранить аккаунт">
    </form>
  </div>
  <?php
}
include 'data/foot.php';
?>