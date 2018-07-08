<?php
/**
 *  Класс для работы с авторством статей
 */

class Connection
{
	//Свойства
	/**
    * @var int ID статьи 
    */
	
	public $articleId = null;
	
	/**
    * @var int ID автора статьи
    */
    public $userId = null;
		
	/**
    * Устанавливаем свойства объекта с использованием значений в передаваемом массиве
    *
    * @param assoc Значения свойств
    */
	
	public function __construct( $data=array() ) {
      if ( isset( $data['articleId'] ) ) $this->articleId = (int) $data['articleId'];
      if ( isset( $data['userId'] ) ) $this->userId = $data['userId'];
      
	}
	
	/**
    * Устанавливаем свойства объекта с использованием значений из формы редактирования
    * @param assoc Значения из формы редактирования
    */

    public function storeFormValues ( $params ) {

      // Store all the parameters
      $this->__construct( $params );
    }
	
	/**
    * Возвращаем объекты Connection, соответствующие заданному ID статьи
    *
    * @param int $articleId ID статьи
    * @return  Array|false Массив объектов Connection object или false, если записи не были найдены или в случае другой ошибки
    */

    public static function getByArticleId( $articleId ) 
    {
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "SELECT * FROM connections WHERE article_id = :article_id";
        $st = $conn->prepare( $sql );
        $st->bindValue(":article_id", $articleId, PDO::PARAM_INT);
        $st->execute();
		$list = array();
		while ($row = $st->fetch()){
			$connection = new Connection($row);
			$list[] = $connection;
		}
        $conn = null;
		return $list;
    }
	
	/**
    * Возвращаем все объекты User из базы данных
    *
    * @param int Optional Количество возвращаемых строк (по умолчанию = all)
    * @param string Optional Столбец, по которому сортируются категории(по умолчанию = "name ASC")
    * @return Array|false Двух элементный массив: results => массив с объектами User; totalRows => общее количество пользователей
    */
    public static function getList( $numRows=1000000, $order="article_id" ) 
    {
		$conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD);
    
		$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM users
            ORDER BY $order LIMIT :numRows";

		$st = $conn->prepare( $sql );
		$st->bindValue( ":numRows", $numRows, PDO::PARAM_INT );
		$st->execute();
		$list = array();

		while ( $row = $st->fetch() ) {
			$user = new User( $row );
			$list[] = $user;
		}

		// Получаем общее количество категорий, которые соответствуют критериям
		$sql = "SELECT FOUND_ROWS() AS totalRows";
		$totalRows = $conn->query( $sql )->fetch();
		$conn = null;
		return ( array ( "results" => $list, "totalRows" => $totalRows[0] ) );
    }
	
	public static function isUserExist($login){
		$conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD);
		$sql = "SELECT name FROM users WHERE name = :name";
		$st= $conn->prepare($sql);
		$st->bindValue( ":name", $login, PDO::PARAM_STR );
		$st->execute();
		if( $st->fetch()[0]){
			return true;
		}else{
			return false;
		}
	} 
	
	/**
    * Вставляем текущий объект User в базу данных и устанавливаем его свойство ID.
    */

    public function insert() {

      // У объекта User уже есть ID?
      if ( !is_null( $this->id ) ) trigger_error ( "Category::insert(): Attempt to insert a User object that already has its ID property set (to $this->id).", E_USER_ERROR );

      // Вставляем нового пользователя
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $sql = "INSERT INTO users ( name, pass, active ) VALUES ( :name, :pass, :active )";
      $st = $conn->prepare ( $sql );
      $st->bindValue( ":name", $this->name, PDO::PARAM_STR );
      $st->bindValue( ":pass", $this->pass, PDO::PARAM_STR );
	  $st->bindValue( ":active", $this->active, PDO::PARAM_INT );
      $st->execute();
      $this->id = $conn->lastInsertId();
      $conn = null;
    }


    /**
    * Обновляем текущий объект User в базе данных.
    */

    public function update() {

      // У объекта User  есть ID?
      if ( is_null( $this->id ) ) trigger_error ( "User::update(): Attempt to update a User object that does not have its ID property set.", E_USER_ERROR );

      // Обновляем данные пользователя
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $sql = "UPDATE users SET name=:name, active=:active WHERE id = :id";
      $st = $conn->prepare ( $sql );
      $st->bindValue( ":name", $this->name, PDO::PARAM_STR );
	  $st->bindValue( ":active", $this->active, PDO::PARAM_INT);
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
      $st->execute();
      $conn = null;
    }


    /**
    * Удаляем текущий объект User из базы данных.
    */

    public function delete() {

      // У объекта User  есть ID?
      if ( is_null( $this->id ) ) trigger_error ( "Category::delete(): Attempt to delete a User object that does not have its ID property set.", E_USER_ERROR );

      // Удаляем пользователя
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $st = $conn->prepare ( "DELETE FROM users WHERE id = :id LIMIT 1" );
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
      $st->execute();
      $conn = null;
    }	
}
