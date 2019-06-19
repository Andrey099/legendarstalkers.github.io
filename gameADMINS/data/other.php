<?php
session_start();
ob_start();
/*
* Classes
*/
include 'class/pagination.php';
include 'class/weapons.php';
include 'class/objects.php';
include 'class/groups.php';
include 'class/xss_filter.php';
$wpn = new Weapons();
$obg = new Objects();
$grp = new Groups();
$xss = new xss_filter();

$act = isset($_GET['act']) ? trim($_GET['act']) : '';
$ids = isset($_GET['id']) ? abs(intval($_GET['id'])) : '';

if (isset($_COOKIE['login'])  && isset($_COOKIE['password']))
{
  $stmt = $go -> prepare('SELECT * FROM `users` WHERE `login` = ? and `password` = ? LIMIT 1');
  $stmt -> execute([$xss -> filter_it($_COOKIE['login']), $xss -> filter_it($_COOKIE['password'])]);
  $u = $stmt -> fetch();
  $uid = $u['id'];

  if (($u['login'] !== $xss -> filter_it($_COOKIE['login'])) or ($u['password'] !== $xss -> filter_it($_COOKIE['password'])))
  {
    setcookie("login", "", time() - 3600*24*30*12, "/");
    setcookie("password", "", time() - 3600*24*30*12, "/");
    header('Location: /');
  }
} /* COOKIE */

function guest()
{
  global $uid;
  if (isset($uid)) header('Location: /');
} /* GUEST */

function user()
{
  global $uid;
  if (!isset($uid)) header('Location: /');
} /* USER */

// ACCESS
function access($access)
{
  global $u;
  if (!isset($u['id'])) header('Location: /');
  elseif ($u['access'] < $access) header('Location: /404/');
}

function numb($num)
{
  $num = (0+str_replace(",", "", $num));

  if(!is_numeric($num)) return false;

  if($num > 1000000000000) return round(($num/1000000000000),1).'t';
  elseif($num > 1000000000) return round(($num/1000000000),1).'b';
  elseif($num > 1000000) return round(($num/1000000),1).'m';
  elseif($num > 10000) return round(($num/1000),1).'k';

  return number_format($num);
}

function show_error ($error)
{
  echo '<div class="dialog">';
    echo '<h1 class="pda">КПК</h1>';
    echo '<p>';
    if (is_array($error) == TRUE)
    {
      foreach ($error as $get)
      {
        echo '› '.$get.'<br/>';
      }
    }
    else
    {
      echo '› '.$error.'<br/>';
    }
    echo '</p>';
  echo '</div>';
}

function show_user_information ($id, $var)
{ // $id - уникальный ID юзера; $var - значение юзера с бд
  global $go;
  $stmt = $go -> prepare('SELECT `'.$var.'` FROM `users` WHERE `id` = ? LIMIT 1');
  $stmt -> execute([$id]);
  $us = $stmt -> fetch();
  return $us[$var];
}

function show_user ($id=0, $type='link')
{
  global $go;
  $stmt = $go -> prepare('SELECT `id`, `login`, `access`, `updDate` FROM `users` WHERE `id` = ? LIMIT 1');
  $stmt -> execute([$id]);
  $us = $stmt -> fetch();

  if ($us['access'] == 1) $ac = '<span class="access-mod">';
  elseif ($us['access'] == 2) $ac = '<span class="access-adm">';
  elseif ($us['access'] == 3) $ac = '<span class="access-dev">';
  else $ac = '<span class="access-user">';

  if (!isset($us['id'])) $re = '<span style="color: #ffa200;">Шрам</span>';
  elseif ($type == 'text') $re = (time() < ($us['updDate']+900) ? '<img width="5px" alt="[online]" title="онлайн" src="/imgs/online.png" />':NULL).$ac.' '.$us['login'].'</span>';
  else $re = (time() < ($us['updDate']+900) ? '<img width="5px" alt="[online]" title="онлайн" src="/imgs/online.png" />':NULL).$ac.' <a href="/id/'.$id.'">'.$us['login'].'</a></span>';

  return $re;
}

/**
 * Функция склонения слов
 *
 * @param mixed $digit
 * @param mixed $expr
 * @param bool $onlyword
 * @return
 */
function declension($digit,$expr,$onlyword=false){
  if (!is_array($expr)) $expr = array_filter(explode(' ', $expr));
  if (empty($expr[2])) $expr[2]=$expr[1];
  $i = preg_replace('/[^0-9]+/s','',$digit)%100;
  if ($onlyword) $digit='';
  if ($i>=5 && $i<=20) $res=$digit.' '.$expr[2];
  else
  {
      $i%=10;
      if($i==1) $res=$digit.' '.$expr[0];
      elseif($i>=2 && $i<=4) $res=$digit.' '.$expr[1];
      else $res=$digit.' '.$expr[2];
  }
  return trim($res);
}

/**
 * Счетчик обратного отсчета
 *
 * @param mixed $date
 * @return
 */

function downcounter($date){
  $check_time = strtotime($date) - time();
  if ($check_time <= 0)
  {
      return false;
  }

  $days = floor($check_time/86400);
  $hours = floor(($check_time%86400)/3600);
  $minutes = floor(($check_time%3600)/60);
  $seconds = $check_time%60; 

  $str = '';
  if ($days > 0) $str .= declension($days,['день','дня','дней']).' ';
  if ($hours > 0) $str .= declension($hours,['час','часа','часов']).' ';
  if ($minutes > 0) $str .= declension($minutes,['минута','минуты','минут']).' ';
  if ($seconds > 0) $str .= declension($seconds,['секунда','секунды','секунд']);

  return $str;
}

function bbcode($msg, $user=NULL)
{
  global $u;

  if ($user != NULL)
  {
    global $go;
    $stmt = $go -> prepare('SELECT `method`, `time_ban` FROM `banned` WHERE `id_user` = ? LIMIT 1');
    $stmt -> execute([$user]);
    $ban = $stmt -> fetch();
    if ($u['access'] == 3 and ($ban['method'] == 1 or $ban['time_ban'] > time()))
    {
      $msg = '[quote]Сообщение скрыто<br/>Причина: пользователь заблокирован. [/quote]';
    }
  }

  $bbcode = array();

  $bbcode['/\[i\](.+)\[\/i\]/isU']='<em>$1</em>';
  $bbcode['/\[b\](.+)\[\/b\]/isU']='<strong>$1</strong>';
  $bbcode['/\[u\](.+)\[\/u\]/isU']='<span style="text-decoration:underline;">$1</span>';
  $bbcode['/\[quote\](.+)\[\/quote\]/isU']='<div class="about" style="margin: 0;">$1</div>';
  $bbcode['/\[act=(.*)\](.*)\[\/act\]/Usi']='<a href="/$1">$2</a>';
  $bbcode['/\[admin\](.+)\[\/admin\]/isU'] =  $u['access'] > 2 ? '<span style="color:red">$1</span>':'<span style="color:red">[скрыто]</span>';

  if (count($bbcode)) $msg = preg_replace(array_keys($bbcode), array_values($bbcode), $msg);
return nl2br($msg);
}


/**
* Show quest line
*
* @param [complite] > сколько выполнено
* @param [all] > сколько всего заданий
*/

function show_quest ($complite, $all)
{
  $percent = 100/$all;
  $grey = $all - $complite;

  if ($complite > 0)
  {
    for ($c = 0; $c < $complite; $c++)
    {
      echo '<div class="one cols" style="width: '.$percent.'%"><div class="quest-green"></div></div>';
    }
  }
  if ($grey > 0)
  {
    for ($g = 0; $g < $grey; $g++)
    {
      echo '<div class="one cols" style="width: '.$percent.'%"><div class="quest-grey"></div></div>';
    }
  }
}

/*
function exps($L) {
  $a = 0;
  for($x = 0; $x < $L; $x++)
  {
    $a += floor(100 + (10 * $x) );
  }
  return floor($a);
}

for($L=1; $L<999; $L++) {
  echo exps($L).', ';
}
Высчитывает опыт для уровня
*/

function numberOfDecimals($value)
{
  if ((int)$value == $value)
  {
    return 0;
  }
  else if (!is_numeric($value))
  {
    return false;
  }

  return strlen($value) - strrpos($value, '.') - 1;
}

function drop($items)
{
  $sumOfPercents = 0;
  foreach($items as $itemsPercent)
  {
    $sumOfPercents += $itemsPercent;
  }

  $decimals = numberOfDecimals($sumOfPercents);
  $multiplier = 1;
  for ($i=0; $i < $decimals; $i++)
  {
    $multiplier *= 10;
  }

  $sumOfPercents *= $multiplier;
  $rand = rand(1, $sumOfPercents);

  $rangeStart = 1;
  foreach($items as $itemKey => $itemsPercent)
  {
    $rangeFinish = $rangeStart + ($itemsPercent * $multiplier);
    if($rand >= $rangeStart && $rand <= $rangeFinish)
    {
     return $itemKey;
    }
    $rangeStart = $rangeFinish + 1;
  }
}
function generateRandomSelection($min=0, $max, $count)
{
  $result=array();
  if($min>$max) return $result;
  $count=min(max($count,0),$max-$min+1);
  while(count($result)<$count) {
    $value = $min;
    foreach($result as $used) if($used<=$value) $value++; else break;
    $result[] = "'".$value."'";
    sort($result);
  }
  return $result;
}