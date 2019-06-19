<?php
if (
  isset($_GET['ch']) and 
  isset($_GET['ce']) and 
  isset($_GET['tb']) and 
  isset($_GET['cha']) and 
  isset($_GET['tha'])
)
{
  $ch = ['0','1','2','3','4','5'];
  $ce = ['0','1','2','3','4'];
  $tb = ['0','1','2'];
  $cha = ['light','normal','dark'];
  $tha = ['0','1','2','3','4','5','6','7','8'];
  if (!array_key_exists($_GET['ch'], $ch)) $error[] = 'Неправильный параметр "Голова"';
  if (!array_key_exists($_GET['ce'], $ce)) $error[] = 'Неправильный параметр "Глаза"';
  if (!array_key_exists($_GET['tb'], $tb)) $error[] = 'Неправильный параметр "Борода"';
  if (!array_key_exists($_GET['cha'], $cha)) $error[] = 'Неправильный параметр "Цвет волос"';
  if (!array_key_exists($_GET['tha'], $tha)) $error[] = 'Неправильный параметр "Волосы"';

  if (empty($error))
  {
    header('Content-type: image/png');
    $background = imagecreatefrompng('files/portrait/background.png');
    $background_x = imageSX($background);
    $background_y = imageSY($background);
    $head = imagecreatefrompng('files/portrait/head/'.$ch[$_GET['ch']].'.png');
    imageCopy($background, $head, 0, 0, 0, 0, $background_x, $background_y);
    $eyes = imagecreatefrompng('files/portrait/eyes/'.$ce[$_GET['ce']].'.png');
    imageCopy($background, $eyes, 0, 0, 0, 0, $background_x, $background_y);
    if ($_GET['tb'] != 0)
    {
      $beard = imagecreatefrompng('files/portrait/beard/'.$cha[$_GET['cha']].'/'.$tb[$_GET['tb']].'.png');
      imageCopy($background, $beard, 0, 0, 0, 0, $background_x, $background_y);
    }
    $hair = imagecreatefrompng('files/portrait/hair/'.$cha[$_GET['cha']].'/'.$tha[$_GET['tha']].'.png');
    imageCopy($background, $hair, 0, 0, 0, 0, $background_x, $background_y);
    $border = imagecreatefrompng('files/portrait/border.png');
    imageCopy($background, $border, 0, 0, 0, 0, $background_x, $background_y);
    imagepng($background);
    imagedestroy($background);
  }
  else
  {
    header('Content-type: image/png');
    $background = imagecreatefrompng('files/portrait/default.png');
    imagepng($background);
    imagedestroy($background);
  }
}