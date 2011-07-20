<?php

class ActiveControllerBase {
  public function render($view, $attr = array()) {
    extract($attr);
    include PATH . "../app/views/{$view}.php";
  }
}

?>