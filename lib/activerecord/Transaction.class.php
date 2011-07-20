<?php

/**
 * Gerencia transações com o banco de dados
 * Fornece uma conexão com o banco de dados e garante que essa seja 
 * a mesma em toda a transação
 * Garante que as informações estarão corretas antes de persistir os dados na base
 *
 * Classe marcada como final para que não possa ser extendida
 *
 * @author    Santiago Carmo
 * @version   0.001
 * @copyright Santiago Carmo <santiago@santiagocarmo.com>
 */
final class Transaction
{
    /**
     * Armazena a conexao ativa, marcada como private e statica para ser usada
     * apenas dentro do escopo da class
     * @var    
     * @access private
     */
    private static $conn;
    private static $logger;

    /**
     * Metodo definido como privado para garantir que não haja mais de uma instancia 
     * dessa classe na aplicação 
     * 
     * @access private
     */
    private function __construct()
    {
    }

    /**
     * Abre uma transação PDO e uma conexao com o banco de dados
     * 
     * @param   $database = Nome do arquivo de configuração
     * @access public static
     */
    public static function open($database, $char = NULL)
    {
      if(empty(self::$conn))
      {
        ($char !== NULL ) ? self::$conn = Connection::open($database, $char) : self::$conn = Connection::open($database);
        self::$conn-> beginTransaction();
        self::$logger = NULL;
        self::setLogger(new Logger);
      }
    }

    /**
     * Recupera a conexão ativa armazenada em $conn
     * 
     * @access public static
     */
    public static function get()
    {
      return self::$conn;
    }

    /**
     * caso ocorra algum erro, desfaz todas as alterações realizadas 
     * no objeto e fecha a conexao sem salvar os dados
     * 
     * @access public static
     */
    public static function rollback()
    {
      if(self::$conn)
      {
        self::$conn->rollback();
        self::$conn = NULL;
      }
    }

    /**
     * Aplica todas as alterações realizadas no objeto
     * Salva essas alterações na base 
     * Fecha a conexao
     * 
     * @access public
     */
    public static function close()
    {
      if(self::$conn)
      {
        self::$conn->commit();
        self::$conn = NULL;
      }
    }
    
    /**
     * Seta o arquivo de log criando uma instancia da class Logger
     *
     * @param $logger = nome do arquivo de log, só aceita instancias 
     * da class Logger
     * @access public
     */
    public static function setLogger(Logger $logger)
    {
      self::$logger = $logger;
    }

    /**
     * Escreve a mensagem de log, verifica se existe uma 
     * instancia do objeto logger
     *
     * @param $message = mensagema ser escrita
     */
    public static function log($message)
    {
      if(self::$logger)
      {
        self::$logger->write($message);
      }
    }
}

?>
