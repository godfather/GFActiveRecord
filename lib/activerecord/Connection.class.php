<?php

/**
 * Cria uma conexão com o banco de dados
 * Utiliza um arquivo .ini para recuperar os dados de conexão do banco
 *
 * Nessa primeira versão só sao suportadas base de dados MYSQL
 *
 * @author    Santiago Carmo
 * @version   0.001
 * @copyright Santiago Carmo <santiago@santiagocarmo.com>
 */
class Connection
{

    /**
     * Metodo marcado como private para evitar que essa classe possa 
     * ser instanciada, garantindo assim que exista apenas uma conexao ativa
     * @return private 
     * @access private
     */
    private function __construct()
    {
    }

    /**
     * o nome do arquivo .ini deve ser o mesmo nome do SGBD no caso
     * 
     * @param   $name = Nome da database
     * @access public
     * @static      
     */
    public static function open($path, $options = Array())
    {
      $default = array('char' => 'utf8');
      $options = array_merge($default, $options);
      /**
       * verifica se o arquivo de configuração existe
       * se existir parsea os dados em um array
       * caso contrário lança uma exceção
       */
      if(file_exists($path))
      {
        $db = parse_ini_file($path);
      }
      else
      {
        throw new Exception("Arquivo '{$path}' não encontrado");
      }

      //por uma melhor organização os dados parseados são atribuidos 
      //as variaveis de mesmo nome, mas isso não é realmente obrigatório
      $user = $db['user'];
      $pass = $db['pass'];
      $name = $db['name'];
      $host = $db['host'];

      /**
       * Abre uma conexão mysql PDO
       * No futuro a medida que for necessário connectar a outros SGBD
       * deve colocar esses dados em um switch que conectará ao driver correto
       */
      $conn = new PDO("mysql:host={$host};dbname={$name};unix_socket=/tmp/mysql.sock", $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$options['char']}"));


      /**
       * Configura a biblioteca PDO para lançar exceções, caso essa linha seja omitida
       * os error da base de dados não serão exibidos
       * se ele não for omitida as chamadas a classe Connection devem ocorrer dentro de
       * um bloco try catch
       */
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      //retorna a conexao ativa
      return $conn;

    } 

}

?>
