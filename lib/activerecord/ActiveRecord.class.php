<?php

/**
 * Mapeia uma tabela em  objetos
 * Fornece ações de persistencia no banco de dados
 *
 * Classe marcada como abstract para que possa ser extendida
 *
 * @author    Santiago Carmo
 * @version   0.001
 * @copyright Santiago Carmo <santiago@santiagocarmo.com>
 */
abstract class ActiveRecord
{
  /**
   * Array que armazena os dados atualmente em uso
   * @access private
   */
  protected $data;
  

  /**
   * Ao instanciar a class se passado algum parametro
   * esse metodo já inicia os dados de um objeto
   *
   * @param $options = Opções para selecionar o objeto
   * @access public
   */
  public function __construct($options = NULL)
  {
    if($options)
    {
      $object = $this->find($options);
      if($object)
      {
        $this->from_array($object->to_array());
      }
    }
  }

  /**
   * Retorna o nome da class
   * Esse nome será usado para selecionar a tabela no banco
   * por convenção a class que extender ActiveRecord deverá ter 
   * o mesmo nome da tabela aquem ela faz refêrencia
   *
   */
  private function get_entity()
  {
    return $entity = strtolower(get_class($this));
  }

  /**
   * O metodo __get é chamado se uma propriedade não declarada 
   * dentro da class for chamada ele cria essa propriedade em 
   * tempo de execução e declara como public
   *
   * Ele verifica se existe um metodo chamado get_propriedade 
   * definido no escopo da classe se houver ele chama esse metodo 
   * ao invéz de chammar a propriedade
   *
   * @param $prop = Nome da propriedade chamada
   * @access public
   */
  public function __get($prop)
  {
    if(method_exists($this, "get_{$prop}"))
    {
      return call_user_func(array($this, "get_{$prop}"));
    }
    else
    {
      return $this->data[$prop];
    }
  }
  
  /**
   * O metodo __set é chamado quando se atribui um valor a uma propriedade
   * não declarada no scopo da class, ele cria essa propriedade em tempo de
   * execução e atribui o valor
   *
   * Ele verifica se existe um metodo definido no escopo da class chamado 
   * set_'propriedade' se existir ele executa esse método ao invés de atribuir 
   * o valor a propriedade
   *
   * @param $prop  = Nome da propriedade chamada
   * @param $value = Valor a ser atribuido 
   * @access public
   */
  public function __set($prop, $value)
  {
    if(method_exists($this, "set_{$prop}"))
    {
      call_user_func(array($this, "set_{$prop}"), $value);
    }
    else
    {
      $this->data[$prop] = $value;
    }
  }
  
  /**
   * Se usado em conjunto com o metodo to_array
   * pega os dados passados (normalmente pelo metodo find())
   * e adiciona ao conjunto de dados $data
   *
   * @param $data = Dados a serem adicionados ao array
   * @acces public
   */
  public function from_array($data)
  {
    $this->data = $data;
  }

  /**
   * Pega os ddos do array privado $data
   *
   */
  public function to_array()
  {
    return $this->data;
  }

  /**
   * Metodo Find, faz um select no banco de dados 
   * retorna um objeto (linha) ou conjunto vazio
   * Possui uma propriedade opcional $options que passa para a query
   * os filtros da busca no banco
   *
   * OBS.: Esse metodo deve ser aperfeiçoado para que no futuro possa se passar
   * lista de campos que desejamos obter
   *
   * @param $options = Lista de opções de filtro, normalmente uma clausula WHERE
   * @access public
   */
  public function find( $options = NULL )
  {
    $query = "SELECT * FROM {$this->get_entity()}";
    ($options !== NULL) ? $query .= " {$options} LIMIT 1": $query .= " LIMIT 1";

    if($conn = Transaction::get())
    { 
      Transaction::log($query);
      $result = $conn->query($query);
      if($result)
      { 
        $object = $result->fetchObject($this->get_entity($this));
      }
      return $object;
    }
    else
    {
      throw new Exception('Você precisa de uma transação ativa para realizar essa operação');
    }
  }
  
  /**
   * Funciona de forma semelhante ao metodo find 
   * mas ao invez de retornar apenas um objeto ele retorna uma coleção de objetos
   *
   * Para que os dados dessa coleção possam ser lidos deve ser usado um foreach
   * para um exemplo vide o arquivo index.php em resorces/exemplo
   *
   * @param $options = dados opcionais que podem ser passados como critério de seleção
   * @access public
   */
  public function find_all( $options = NULL )
  {
    $query = "SELECT * FROM {$this->get_entity()}";
    ($options !== NULL) ? $query .= " {$options}": $query;

    if($conn = Transaction::get())
    {
      Transaction::log($query); 
      $result = $conn->query($query);
      if($result)
      { 
        while ($row = $result->fetchObject($this->get_entity($this)))
        {
          $objects[] = $row;
        }
      }
      return $objects;
    }
    else
    {
      throw new Exception('Você precisa de uma transação ativa para realizar essa operação');
    }
  }
  
  public function find_by_sql($query)
  {
    if($conn = Transaction::get())
    {
      Transaction::log('FIND BY QUERY: ' . $query);
      $result = $conn->query($query);
      if($result)
      {
        /*switch(true)
        {
          case is_array($result):*/
            while ($row = $result->fetchObject($this->get_entity($this)))
            {
              $objects[] = $row;
            }
            /*break;
          default:
            $objects = $result->fetchObject($this->get_entity($this));
            break;
        }*/
        return $objects;
      }
    }
    else
    {
       throw new Exception('Você precisa de uma transação ativa para realizar essa operação');
    }
  }


  /**
   * Metodo update(), atualiza um registro em uma tabela usando
   * os dados de um objeto
   * é obrigatório a passagem do parametro $options para que o banco saiba qual é 
   * o registro a ser atualisado
   *
   * OBs.: em breve esse metodo será atualizado para que o parametro options 
   * seja opcional sendo que update poderá atualizar o objeto atual caso options 
   * não seja passado
   *
   * @param $options = Obrigatorio indica qual é o registro a ser atrulizado
   * @access public
   */
  public function update($options = NULL, $slash = true)
  {
    /*if(empty($options))
    {
      throw new Exception('Você precisa passar um parametro indicando qual objeto deve ser atualizado');
    }*/
    
    foreach($this->data as $column => $value)
    {
      $value = is_string($value) ? "'" . addslashes($value) . "'" : $value;
      $data[] = "{$column} = {$value}";
    }

    (!$options) ? $options = 'where id = ' . $this->data['id'] : $options = $options;
    $query = "UPDATE {$this->get_entity()} SET " . implode(',', $data) . " {$options}";

    if($conn = Transaction::get())
    {
      Transaction::log($query);
      $result = $conn->exec($query);
      return $result;
    }
    else
    {
      throw new Exception('Não há uma transação ativa');
    }
  }

  /**
   * Metodo delete(), deleta registros de uma tabela
   * recebe um parametro opcional $options que especifica
   * quais registros serão excluidos, caso esse parametro
   * seja omitido ele deleta todos os registros da tabela
   *
   *
   * Obs.: em breve esse metodo será atualizado como o metodo
   * update assim caso o parametro options seja omitido o 
   * objeto atual será excluido, caso seja preciso apagar todos 
   * os registros de uma tabela passamos como parametro a string 'all'
   *
   * @param $options = Opcional, india quais registros devem ser excluidos de uma tabela
   * @access public
   */
  public function delete($options = NULL)
  {
    $query = "DELETE FROM {$this->get_entity()}";
    ($options !== NULL) ? $query .= " {$options}" : $query;

    if($conn = Transaction::get())
    {
      Transaction::log($query);
      $result = $conn->exec($query);
      return $result;
    }
  }

  /**
   * Metodo save(), armazena os dados no banco
   * trabalha com o array privado $data da classe 
   * transforma o nome das chaves do array no nome dos campos e 
   * os valores do array em valores a serem armazenados
   *
   * Recebe opcionalemte um parametro array e trata da mesma dorma que $data
   * Esse array pode ser um $_POST ou $_GET, des de que o nome dos campos do form
   * seja o mesmo nome dos campos da tabela
   *
   * @param $array = Opcional um array que sera armazenado na tabela
   * @access public
   *
   */
  public function save($array = NULL, $slash = true)
  {
    $data;
    ($array) ? $data = $array : $data = $this->data;
    
    foreach($data as $column => $value) {
      if($slash) { $t[$column] = is_null($value) ? "NULL" : "'" . addslashes($value) . "'"; } 
      else { $t[$column] = is_null($value) ? "NULL" : "'{$value}'"; }
    }

    $columns = implode(', ', array_keys($t));
    $values = implode(', ', array_values($t));

    $query = "Insert into {$this->get_entity()} ({$columns}) value ({$values})";

    //return $query;

    if(empty($data))
    {
      throw new Exception('Não há dados a serem salvos');
    }

    if($conn = Transaction::get())
    {
      Transaction::log($query);
      $result = $conn->exec($query);
      return $result;
    }
    else
    {
      throw new Exception('Não há uma transação ativa');
    }
  }
  
  /**
   * Metodo return_recent_id, retorna o ultimo id inserido, deve ser usado logo 
   * após o metodo save(), dessa forma o ultimo id criado será retornado
   * 
   * OBs.: só poderá ser usado com tabelas que possuam um campo id (auto_increment)
   *
   * @access public
   */  
  public function return_recent_id()
  {
    if($conn = Transaction::get())
    {
      Transaction::log('Pegando o id do registro criado');
      $result = $conn->lastInsertId();
      return $result;
    }
  }


  public function get_last_id()
  {
    if($conn = Transaction::get())
    {
      $query = "SELECT id from {$this->get_entity()} ORDER BY id DESC LIMIT 1";
      Transaction::log("Pegando o id do ultimo registro da tabela {$this->get_entity()}");
      transaction::log($query);
      $result = $conn->query($query);
      $row = $result->fetch();
      return $row[0];
    }
  }
  
  public function count_all($options = array())
  {
    $default = array('filter' => NULL, 'alter_query' => NULL);
    $options = array_merge($default, $options);
    $query   = "SELECT COUNT(*) FROM {$this->get_entity()}";
    if($options['filter']      != NULL) $query .= $options['filter'];
    if($options['alter_query'] != NULL) $query  = $options['alter_query'];
    
    if($conn = Transaction::get())
    {
      Transaction::log($query);
      $result = $conn->query($query);
      if($result) $row = $result->fetch();
      return $row[0];
    }
  }
  
  //set values to data and verify validation before save
  public function set_paramns($paramns) {
    if($paramns['id']) unset($paramns['id']);
    foreach($paramns as $prop => $value) {
      if(method_exists($this, "set_{$prop}")) call_user_func(array($this, "set_{$prop}"), $value);
      else $this->data[$prop] = $value;
    }
  }
  
  public function get_next_id() {
    $query = "SHOW TABLE STATUS LIKE '{$this->get_entity()}'";
    if($conn = Transaction::get()) {
      Transaction::log("### GET NEXT ID: {$query}");
      $result = $conn->query($query);
      if($result) $row = $result->fetch(PDO::FETCH_OBJ);
      return $row->Auto_increment;
    }
  }

}

?>
