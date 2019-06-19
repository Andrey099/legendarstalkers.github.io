<?php
include 'data/base.php';
include 'data/head.php';
if (isset($uid))
{
  /*$stmt = $go -> prepare('SHOW COLUMNS FROM `zone` WHERE `Field` = ?');
  $stmt -> execute(['quest_1_1']);
  $s = $stmt -> fetch();
  $tp = $s['Type'];
  $str = preg_replace("/[^0-9',]/", '', $tp);
  $str = explode(',',$str);
  echo count($str)-1;*/
  ?>
  <div class="center"><img src="/imgs/start.png" /></div>
  <div class="line"></div>
  <div style="margin: 5px;">
    <a href="/zone/" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
          <td class="attack-text">
            Окрестности<br/>
            <small>
              Задания
            </small>
          </td>
        </tr>
      </table>
    </a>
    <a href="/hike/" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><?php echo ($u['hikeTime'] < time() ? '<img width="16px" src="/imgs/plus.png">':'<img width="16px" src="/imgs/menu.png">')?></td>
          <td class="attack-text">
            Патруль<br/>
            <small>
              Задание от Шрама
            </small>
          </td>
        </tr>
      </table>
    </a>
    <a href="/ladder/" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
          <td class="attack-text">
            Ладдер<br/>
            <small>
              Поединок сталкеров
            </small>
          </td>
        </tr>
      </table>
    </a>
    <a href="/fights/" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
          <td class="attack-text">
            Вылазка<br/>
            <small>
              Сражение с боссами
            </small>
          </td>
        </tr>
      </table>
    </a>
    <a href="/shop/" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
          <td class="attack-text">
            Палыч<br/>
            <small>
              Местный торговец
            </small>
          </td>
        </tr>
      </table>
    </a>
    <a href="/digger/" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
          <td class="attack-text">
            Диггер<br/>
            <small>
              Найдется все!
            </small>
          </td>
        </tr>
      </table>
    </a>
    <a href="/hq/" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
          <td class="attack-text">
            Штаб<br/>
            <small>
              Сердце деревни
            </small>
          </td>
        </tr>
      </table>
    </a>
    <a href="/fire/" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
          <td class="attack-text">
            Костер<br/>
            <small>
              Общение между сталкерами
            </small>
          </td>
        </tr>
      </table>
    </a>
    <a href="/groups/" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
          <td class="attack-text">
            Группировки<br/>
            <small>
              Союз сталкеров
            </small>
          </td>
        </tr>
      </table>
    </a>
    <?php if ($u['access'] > 0): ?>
    <a href="/access/" class="weapon">
      <table width="100%">
        <tr>
          <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
          <td class="attack-text">
            Панель<br/>
            <small>
              Панель управления
            </small>
          </td>
        </tr>
      </table>
    </a>
    <?php endif;?>
  </div>
  <?php
}
else
{
  ?>
  <div class="center"><img src="/imgs/start.png" /></div>
    <div class="line"></div>
    <div class="info">
      Эй, иди сюда, ситуацию проясню!<hr/>
      Тут не курорт - пахать надо много и быстро. Будешь это делать, получишь уважение других. Ну как, входить будешь, сталкер? Если нет, то уйди и не мозоль мне глаза.
    </div>
    <a class="button" href="/start">Начать игру</a>
    <a class="button" href="/login">Авторизация</a>
  <?php
}
include 'data/foot.php';
?>