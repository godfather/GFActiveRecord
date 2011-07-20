<?
  require 'settings.php';
  /**
   * Function __autoload, carrega as classes automaticamente
   * Lê o conteudo dos diretorios listados no array $directory_list
   * e verifica se o nome da classes está nesse diretório
   * Se estiver carrega a class caso ela ainda não esteja carregada
   *
   * @param $class = Nome da classe, preenchido automaticamente ao 
   * tentar instanciar uma classe ainda não carregada
   */
  function __autoload($class) {
    $directory_list = array('lib/activerecord/', 'lib/activecontroller/', '../app/models/', '../app/controllers/', '../lib/');
    foreach($directory_list as $directory) {
      $extension = preg_match('/controllers/', $directory) ? '.php' : '.class.php';
      if(file_exists(PATH . "{$directory}{$class}{$extension}")) require_once(PATH ."{$directory}{$class}{$extension}");
    }
  }
