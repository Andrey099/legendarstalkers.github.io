<?php
include '../data/base.php';
access(1);
include '../data/head.php';
if ($u['access'] > 0):
?>
<div style="margin: 5px;">
  <a href="/access/ban" class="weapon">
    <table width="100%">
      <tr>
        <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
        <td class="attack-text">
          Блокировки<br/>
          <small>
            Управление блокировками
          </small>
        </td>
      </tr>
    </table>
  </a>
  <a href="/access/equip" class="weapon">
    <table width="100%">
      <tr>
        <td class="attack-icon" width="16px" valign="top"><img width="16px" src="/imgs/menu.png"></td>
        <td class="attack-text">
          Экипировка<br/>
          <small>
            Управление экипировкой
          </small>
        </td>
      </tr>
    </table>
  </a>
</div>
<?php
endif;
include '../data/foot.php';