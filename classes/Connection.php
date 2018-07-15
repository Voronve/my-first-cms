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
      if ( isset( $data['article_id'] ) ) $this->articleId = (int) $data['article_id'];
      if ( isset( $data['user_id'] ) ) $this->userId = (int) $data['user_id'];
      
	}
	
	/**
    * Устанавливаем свойства объекта с использованием значений из формы редактирования
    * @param assoc Значения из формы редактирования
    */

    public function storeFormValues ( $params ) 
	{

      // Store all the parameters
      $this->__construct( $params );
    }
	
	/**
    * Возвращаем объекты Connection, соответствующие заданному ID статьи
    *
    * @param int $articleId ID статьи
    * @return  Array|false Массив объектов Connection object или false, если записи не были найдены или в случае другой ошибки
    */

    public static function getById( $articleId = null, $userId = null) 
    {
		$selectByUser = $userId ? "AND user_id = :userId" : "";
	
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "SELECT * FROM connections WHERE article_id = :articleId " . $selectByUser;
        $st = $conn->prepare( $sql );
        $st->bindValue(":articleId", $articleId, PDO::PARAM_INT);
		if($userId){
			$st->bindValue(":userId", $userId, PDO::PARAM_INT);
		}
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
    /*public static function getList( $numRows=1000000, $order="article_id" ) 
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
	} */
	
	/**
    * Добавляем новую связь между юзером и статьей
    */

    public function insert() 
	{
      // Вставляем новую связь 
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $sql = "INSERT INTO connections ( article_id, user_id) VALUES ( :articleId, :userId )";
      $st = $conn->prepare ( $sql );
      $st->bindValue( ":articleId", $this->articleId, PDO::PARAM_INT );
      $st->bindValue( ":userId", $this->userId, PDO::PARAM_INT );
      $st->execute();
      $conn = null;
    }


    /**
    * Обновляем текущий объект Connection в базе данных.
    */

    /*public function update() {

      // У объекта Connection есть ID статьи и пользователя?
      if ( is_null( $this->article_id && $this->user_id ) ) trigger_error ( "Category::update(): Attempt to update a User object that does not have its key properties set.", E_USER_ERROR );

      // Обновляем связь
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $sql = "UPDATE users SET name=:name, active=:active WHERE id = :id";
      $st = $conn->prepare ( $sql );
      $st->bindValue( ":name", $this->name, PDO::PARAM_STR );
	  $st->bindValue( ":active", $this->active, PDO::PARAM_INT);
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
      $st->execute();
      $conn = null;
    }*/


    /**
    * Удаляем текущий объект Connection из базы данных.
    */

    public function delete() 
	{
      // Удаляем связь
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $st = $conn->prepare ( "DELETE FROM connections WHERE article_id = :article_id "
			  . "AND user_id = :user_id LIMIT 1" );
      $st->bindValue( ":article_id", $this->articleId, PDO::PARAM_INT );
	  $st->bindValue( ":user_id", $this->userId, PDO::PARAM_INT );
      $st->execute();
      $conn = null;
    }	
}
