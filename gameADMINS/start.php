<?php
include 'data/base.php';

switch ($act)
{
  default:
    $title = 'Таможня';
    include 'data/head.php';
    guest();
    ?>
    <div class="center"><img src="/imgs/home.png" /></div>
    <div class="dialog">
      <h1 class="human">Шрам</h1>
      <p>
        <?php
        if (isset($_REQUEST['next']))
        {
          $_SESSION['nick'] = $_POST['nickname'];
          if (empty($_POST['nickname'])) echo '› Ты что издеваться вздумал? Быстро назови свои имя или я открою огонь.';
          elseif (strlen($_POST['nickname']) < 2) echo '› Имя из одной буквы? Не пытайся меня обмануть, сталкер, а то добром это не закончится.';
          elseif (strlen($_POST['nickname']) > 18) echo '› Как-как? Слишком длинное, не запомню я его. Сделай короче.';
          else
          {
            $stmt = $go -> prepare('SELECT `id` FROM `users` WHERE `login` = ?');
            $stmt -> execute([$_POST['nickname']]);
            $total = $stmt -> rowCount();

            if ($total > 0) echo '› У нас уже тут есть сталкер с именем "'.$_POST['nickname'].'" и он - не ты. Говори другое имя.';
            else
            {
              unset($_SESSION['nick']);
              $stmt = $go -> prepare('INSERT INTO `users` (`login`, `password`, `addDate`, `updDate`) VALUES (?, ?, ?, ?)');
              $stmt -> execute([$_POST['nickname'], md5($_POST['nickname']), time(), time()]);

              setcookie('login', $_POST['nickname'], time()+86400*365, '/');
              setcookie('password', md5($_POST['nickname']), time()+86400*365, '/');
              header('Location: /start/step/1');
            }
          }
        }
        else
        {
          echo '› Так-так, кто это тут у нас пытается пройти? Назови свои имя, сталкер!';
        }
        ?>
      </p>
    </div>
    <div class="fights fights-about">
      <form method="POST">
        <small>Имя (от 2 до 18 символов):</small><br/>
        <input type="text" name="nickname" value="<?php echo (!empty($_SESSION['nick']) ? $_SESSION['nick']:'');?>" placeholder="Введите имя..." />
        <input type="submit" name="next" value="Продолжить" />
      </form>
    </div>
    <div class="fights-link" style="margin: 5px;"><a href="/">Вернуться назад</a></div>
    <?php
  break;

  case 'step1':
    $title = 'Кабинет Шрама';
    include 'data/head.php';
    user();
    if ($u['start'] == 1) die(header('Location: /start/step/2'));
    elseif ($u['start'] == 2) die(header('Location: /'));
    else
    {
      if (isset($_GET['bag']))
      {
        $stmt = $go -> prepare('UPDATE `users` SET `start` = ? WHERE `id` = ?');
        $stmt -> execute([1, $uid]);
        ?>
        <div class="center"><img src="/imgs/home.png" /></div>
        <div class="dialog">
          <h1 class="human">Шрам</h1>
          <p>
            › Я тебя понял, <?php echo $u['login'];?>. Быстро доставай все из карманов и выкладывай на стол.
          </p>
          <h1 class="you">Вы</h1>
          <p>
            › Вот, это все, что у меня есть.
            <div class="list">
              Содержимое карманов<hr/>
              › 100 рублей<br/>
              › 2 шоколадных батончика<br/>
              › КПК<br/>
            </div>
          </p>
          <h1 class="human">Шрам</h1>
          <p>
            › Так, половину я заберу себе, остальное хватай и иди за мной, сейчас введу в курс происходящего.
          </p>
        </div>
        <a class="button" href="/start/step/2">Забрать вещи и пойти</a>
        <?php
      }
      else
      {
        ?>
        <div class="center"><img src="/imgs/home.png" /></div>
        <div class="dialog">
          <h1 class="human">Шрам</h1>
          <p>
            › Так-так, кто это тут у нас пытается пройти? Назови свои имя, сталкер!
          </p>
          <h1 class="you">Вы</h1>
          <p>
            › Называй меня <?php echo $u['login'];?>, я тут новенький.
          </p>
          <h1 class="human">Шрам</h1>
          <p>
            › Я тебя понял, <?php echo $u['login'];?>. Быстро доставай все из карманов и выкладывай на стол.
          </p>
        </div>
        <a class="button" href="?bag">Достать все из карманов</a>
        <?php
      }
    }
  break;
  case 'step2':
    $title = 'Раздевалка';
    include 'data/head.php';
    user();
    if ($u['start'] == 0) die(header('Location: /start/step/1'));
    elseif ($u['start'] == 2) die(header('Location: /'));
    else
    {
      if (isset($_GET['bag']))
      {
        $stmt = $go -> prepare('UPDATE `users` SET `start` = ? WHERE `id` = ?');
        $stmt -> execute([2, $uid]);
        ?>
        <div class="center"><img src="/imgs/home.png" /></div>
        <div class="dialog">
          <h1 class="human">Шрам</h1>
          <p>
            › А сейчас я расскажу тебе все, что нужно знать новичку. Слушай все и запоминай, больше тебе тут никто не поможет.
          </p>
          <h1 class="human">Шрам</h1>
          <div class="dialog-p">
            <div class="about margin">Основное</div>
            › Энергия. Требуется для выполнения заданий в окрестностях. Пополняется на 1 ед. раз в 5 минут.<br/>
            › Известность. Дается за разнообразные действия в деревне. Больше известности - больше возможностей.<br/>
            <div class="about margin">Валюта</div>
            › Основная валюта - это болты. Денег в зоне мало, приходиться выкручиваться. За них ты сможешь покупать разные предметы.<br/>
            › Вторая валюта - это рубли. Так как их мало, то за них ты сможешь купить особо редкие для зоны предметы, ну или обменять на болты.<br/>
            <div class="about margin">Деревня</div>
            › Окрестности. Здесь ты будешь выполнять задания и получать награду за них.<br/>
            › Патруль. Раз в 8 часов можешь ходить в патруль, где есть шанс найти болты.<br/>
            › Торговец. У Петровича ты сможешь сделать разные покупки, которые нужны для заданий или походов.<br/>
            › Диггер. Никто не знает его имени, но этот человек может достать очень редкие предметы.<br/>
            › Походы. Как только ты станешь более-менее известным, сможешь начать ходить в походы на разных тварей, убивая которых будешь получать редкие предметы.<br/>
            › Костер. Место, где собираются все сталкеры для общения между собой.<br/>
            › Штаб. Тут тебе выдают специальные задания на день, выполнив которые будешь получать рубли.<br/>
            › Палатка. Место твоего проживания в деревне, где сможешь узнать всю информацию о себе.<br/>
            › КПК. Требуется для общения с другими сталкерами за пределами костра.<br/>
            › Группировки. Объединения сталкеров.<br/>
          </div>
          <h1 class="human">Шрам</h1>
          <p>
            › Все, ступай в деревню. У меня больше нет времени на тебя, пост ждет.
          </p>
        </div>
        <a class="button" href="/">Отправиться в деревню</a>
        <?php
      }
      else
      {
        ?>
        <div class="center"><img src="/imgs/home.png" /></div>
        <div class="dialog">
          <h1 class="human">Шрам</h1>
          <p>
            › Сейчас мы тебя соберем, а потом послушаешь лекцию от меня.
          </p>
          <h1 class="you">Вы</h1>
          <p>
            › Лекцию? Я готов.
          </p>
          <h1 class="human">Шрам</h1>
          <p>
            › Это хорошо, что готов, иначе тебе тут не выжить.<br/>
            › Вот тебе рюкзак, тут есть все, что нужно для начала.
            <div class="list">
              Содержимое рюкзака<hr/>
              › 100 болтов<br/>
              › 3 вилки<br/>
              › 2 ножа<br/>
              › 1 патрон на пистолет<br/>
              › Палатка
            </div>
          </p>
        </div>
        <a class="button" href="?bag">Взять рюкзак</a>
        <?php
      }
    }
  break;
}

include 'data/foot.php';