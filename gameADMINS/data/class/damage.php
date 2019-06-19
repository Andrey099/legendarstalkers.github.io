<?php
/**
* Damage in game
*
* @author xynd3r
* @version v1
*/

class Fight
{
  function __construct()
  {
  }

  public function getDamage ($id, $type)
  {
    if (empty($id) and empty($type)) return 0;
    else
    {
      $damage = 0;
      $damage += $this -> $type($id);
      return abs(intval($damage));
    }
  }

  private function boot ($id)
  {
    $damage = 15; // Дефолтный урон
    return $damage;
  }
  private function hand ($id)
  {
    $damage = 10; // Дефолтный урон
    return $damage;
  }
  private function head ($id)
  {
    $damage = 20; // Дефолтный урон
    return $damage;
  }
  private function knife ($id)
  {
    $damage = 50; // Дефолтный урон
    return $damage;
  }
  private function pistol ($id)
  {
    $damage = 100; // Дефолтный урон
    return $damage;
  }
  private function gun ($id)
  {
    $damage = 250; // Дефолтный урон
    return $damage;
  }
}
?>