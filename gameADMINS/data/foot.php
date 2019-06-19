    <?php
    if (isset($uid) and $u['start'] == 2)
    {
      ?>
      <div class="line"></div>
      <div style="margin: 2px 3px;">
        <div class="grid">
            <div class="six columns">
              <div class="cl-foot">
                <a href="/id/<?php echo $uid;?>" class="weapon">
                  <table width="100%">
                    <tr>
                      <td class="attack-text">
                        Дом<br/>
                        <small>Ваш профиль</small>
                      </td>
                    </tr>
                  </table>
                </a>
              </div>
            </div>
            <div class="six columns">
              <div class="cl-foot">
                <a href="/pda" class="weapon">
                  <table width="100%">
                    <tr>
                      <td class="attack-text">
                        КПК<br/>
                        <small>Связь</small>
                      </td>
                    </tr>
                  </table>
                </a>
              </div>
            </div>
            <div class="six columns">
              <div class="cl-foot">
                <a href="/" class="weapon">
                  <table width="100%">
                    <tr>
                      <td class="attack-text">
                        Площадь<br/>
                        <small>Главная страница</small>
                      </td>
                    </tr>
                  </table>
                </a>
              </div>
            </div>
            <?php if ($g['user'] != 0): ?>
            <div class="six columns">
              <div class="cl-foot">
                <a href="/groups/<?php echo $g['user']['group']['id']?>" class="weapon">
                  <table width="100%">
                    <tr>
                      <td class="attack-text">
                        Группировка<br/>
                        <small>Ваша группировка</small>
                      </td>
                    </tr>
                  </table>
                </a>
              </div>
            </div>
            <?php else: ?>
            <div class="six columns">
              <div class="cl-foot">
                <a href="/groups" class="weapon">
                  <table width="100%">
                    <tr>
                      <td class="attack-text">
                        Группировки<br/>
                        <small>Весь список</small>
                      </td>
                    </tr>
                  </table>
                </a>
              </div>
            </div>
            <?php endif; ?>
            <div class="six columns">
              <div class="cl-foot">
                <a href="/users" class="weapon">
                  <table width="100%">
                    <tr>
                      <td class="attack-text">
                        Рейтинг<br/>
                        <small>Лучшие сталкеры</small>
                      </td>
                    </tr>
                  </table>
                </a>
              </div>
            </div>
            <div class="six columns">
              <div class="cl-foot">
                <a href="/inv" class="weapon">
                  <table width="100%">
                    <tr>
                      <td class="attack-text">
                        Склад<br/>
                        <small>Инвентарь</small>
                      </td>
                    </tr>
                  </table>
                </a>
              </div>
            </div>
        </div>
      </div>
      <?php
      if (isset($_GET['exit']))
      {
        setcookie('login', '', time()+86400*365, '/');
        setcookie('password', '', time()+86400*365, '/');
        header('Location: /');
      }
    }
    ?>
    <div class="line"></div>
    <div class="copy">
      X-RAY  • Почувствуй себя сталкером<br/>
      &copy; xynd3r
    </div>
  </div>
  <div class="outblock">
  <?
    if (isset($uid) and $u['start'] == 2)
    {
      $stmt = $go -> prepare('SELECT `id` FROM `users` WHERE `updDate` > ?');
      $stmt -> execute([time() - 900]);
      $online = $stmt -> rowCount();
      ?>
      Текущее время: <?php echo date("d.m.Y H:i:s");?><br/>
      <a href="/users/online">Онлайн (<?php echo $online;?>)</a> / <a href="/fire/">Костер</a> / <a href="/forum/">Форум</a> / <a href="?exit">Выйти</a><br/>
      <?
    }
    $end_time = microtime();
    $end_array = explode(" ",$end_time, 5);
    $end_time = $end_array[1] + $end_array[0];
    $time = $end_time - $start_time;
    printf("Ген.: %f сек", $time);
  ?>
  </div>
</body>
</html>