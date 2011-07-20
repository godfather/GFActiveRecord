<?php

/**
 *
 *
 */
class Session
{
  /**
   *
   *
   */
  public function __construct()
  {
    if(!isset($_SESSION)) @session_start();
  }

  /**
   *
   *
   */
  public static function set_value($var, $value)
  {
    $_SESSION[$var] = $value;
  }

  /**
   *
   *
   */
  public static function get_value($var)
  {
    return $_SESSION[$var];
  }

  /**
   *
   *
   */
  public static function unset_value($var)
  {
    unset($_SESSION[$var]);
  }
}

?>
