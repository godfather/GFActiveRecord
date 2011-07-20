<?php
  //path
  define(PATH, dirname(__FILE__) . '/../');

  //redirect function
  
  function redirect_to($url, $params=array())
  {
    $default = array();
    $params = array_merge($default, $params);
    foreach($params as $key => $value) { $redirect[] = "{$key}={$value}"; }
    if(count($redirect) > 0)$redirect = implode('&', $redirect);
    print  "<script>window.location=('{$url}?{$redirect}')</script>";    
  }
  
  function link_to ($url, $text, $params=array())
  {
    $default = array('get' => '', 'html' => '');
    $params  = array_merge($default, $params);
    Session::unset_value('errors');
    Session::unset_value('form_values');
    print  "<a href='{$url}?{$params['get']}' {$params['html']} >{$text}</a>";    
  }
  

  function show_errors($form)
  {
    if(Session::get_value('errors'))
    {
      echo '<div id="show-error">';
      echo '<h2>Ocorreram alguns erros durante o envio:</h2>';
      echo '<ul>';
      if(is_array(Session::get_value('errors')))
      {
        if(array_key_exists($form, array_flip(Session::get_value('errors'))))
        {
          foreach(Session::get_value('errors') as $value) { if($value != $form) echo "<li>{$value}</li>";}
        }
      }
      else
      {
        print Session::get_value('errors');
      }
      echo '</ul></div>';
    } 
  }
  
  function restore_form_values($form_values)
  {
    if($form_values)
    {
      foreach($form_values as $k => $v) { $object->{$k} = $v; }
      return $object;
    }
  }
