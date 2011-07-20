<?php
/**
 * Grava os logs em um arquivo pre selecionado
 *
 * @author    Santiago Carmo
 * @version   0.002
 * @copyright Santiago Carmo <santiago@santiagocarmo.com>
 */
class Logger {
  /**
   * Armazena o nome do arquivo de log que será usado
   * @access private
   */
  private $file = PATH;

  /**
   * Inicia a class, cria um nome para o arquivo de log
   * e seta o arquivo de log a variavel $file
   *
   * O nome criado é baseado no ano atual e no mes atual
   * assim há um melhor controle entre os arquivos de log
   *
   */
  public function __construct () {
    if(!file_exists($this->file . 'log')) `mkdir {$this->file}log`;
    $this->file .= 'log/'. date("Ymd") . '-log.txt';
  }
  
  /**
   * Metodo write, verifica se o arquivo existe
   * se não existir cria um arquivo novo e coloca o ponteiro
   * no final do arquivo
   *
   * Escreve as mensagens de log e fecha o arquivo
   *
   * Obs.: improve this log to show more information on development environment and less on production
   * @param $message = mensagem a ser escrita
   * 
   */
  public function write($message) {
    $date     = date("Y-m-d H:i:s");
    $line_log = "{$date} :: {$_SERVER['REMOTE_ADDR']} :: {$message}\n";
    $handler  = fopen($this->file, 'a+');

    fwrite($handler, $line_log);
    fclose($handler);
  }

}

?>
