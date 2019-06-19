<?php
include 'data/base.php';
$title = 'Вы заблудились';
include 'data/head.php';
?>
<div class="background-block" style="background: url(/imgs/404.png) center no-repeat;">
  <div class="background-text">
    <strong>PDA</strong><br/>
    <small>Кажется мы заблудились</small>
  </div>
</div>
<div class="dialog">
  <h1 class="pda">PDA</h1>
  <p>
    › Как мы сюда попали? Сейчас узнаю!<br/>
    <span class="access-dev">Возможные причины</span><br/>
    › Данная страница не существует.<br/>
    › Нет доступа к данной странице.<br/>
    › Неправильно введен адрес страницы.<br/>
    › Глупые разработчики сделали ссылку на страницу, но саму страницу - нет.
  </p>
</div>
<?php
include 'data/foot.php';
?>