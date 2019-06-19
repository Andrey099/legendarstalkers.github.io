<?php
include 'data/base.php';
$title = 'Чат';
include 'data/head.php';
user();
$act = (isset($_GET['search']) ? 'search' : 'default');
if (isset($_POST['next']))
{
  if ($u['repute'] < 300) $error[] = 'Сначала заработай 300 репутации, чтобы общаться тут';
  elseif (empty($_POST['message'])) $error[] = 'Введите текст сообщения';
  elseif (strlen($_POST['message']) < 2) $error[] = 'Сообщение не может быть короче 2 символов.';
  elseif (strlen($_POST['message']) > 320) $error[] = 'Сообщение не может быть длинее 320 символов.';

  if (!empty($error))
  {
    show_error($error);
  }
  else
  {
    if (isset($_GET['answer'])) $answer = $_GET['answer'];
    else $answer = 0;

    $stmt = $go -> prepare('INSERT INTO `chat` (`id_user`, `message`, `time`, `type`, `answer`) VALUES (?, ?, ?, ?, ?)');
    $stmt -> execute([$uid, $_POST['message'], time(), $act, $answer]);
    header('Location: /fire'.(isset($_GET['search'])?'/search':null).'');
    die();
  }
}
?>
<div class="dialog">
  <h1 class="human">Вы</h1>
  <form method="post">
    <?php echo (isset($_GET['answer']) ? '<span style="color:#ffa200">Ответ для "'.show_user($_GET['answer']).'"</span>':'Введите сообщение');?> <small>[Мин.:2/Макс.:320]</small>:<br>
    <textarea name="message" value="Введите текст..."></textarea>
    <input type="submit" name="next" value="Отправить сообщение">
  </form>
</div>

<?php
$pages = new Paginator('10', 'page');
$stmt = $go -> prepare('SELECT (id) FROM `chat` WHERE `type` = ?');
$stmt -> execute([$act]);
$total = $stmt->rowCount();

if($total == '0') show_error('Сообщения отсутствуют...');
else
{
  $pages->set_total($total);
  $stmt = $go -> prepare('SELECT * FROM `chat` WHERE  `type` = ? ORDER BY `id` DESC '.$pages->get_limit());
  $stmt -> execute([$act]);
  $data = $stmt->fetchAll();
  foreach($data as $messages)
  {
    ?>
    <div class="dialog">
      <h1 class="chat">
        <?php echo show_user($messages['id_user']);?> <?php echo ($messages['answer'] != 0 ? 'в ответ '.show_user($messages['answer']):'')?>
      </h1>
      <p>
        › <?php echo $messages['message'];?><br/>
      </p>
      <div class="small">
        Время › <?php echo date('j.m.Y в H:i:s', $messages['time']);?>
        <?php echo ($messages['id_user'] != $uid ? '<span class="pull-right"><a href="?answer='.$messages['id_user'].'">Ответить</a></span>':'');?>
      </div>
    </div>
    <?php
  }
  if ($total > 10) echo $pages->page_links();
}
?>
<div style="margin: 5px;">
  <div class="grid fights-link">
    <div class="six columns ln">
      <a href="/fire">Обычный</a>
    </div>
    <div class="six columns">
      <a href="/fire/search">Поисковый</a>
    </div>
  </div>
</div>
<?php
include 'data/foot.php';
?>
