<?php
/**
 * Класс для работы с субкатегориями 
 */
class Subcategory
{
	/**
	 *
	 * @var int ID подкатегории 
	 */
	public $id = null;
	
	/**
	 *
	 * @var string название подкатегории  
	 */
	public $name = null;
	
	/**
	 *
	 * @var int идентификатор категории к которой относится данная подкатегория 
	 */
	public $cat_id = null;
	
	/**
	 * Устанавливаем свойства объекта с использованием значений из формы редактирования
	 * 
	 * @param assoc $data Значения свойств
	 */
	public function __construct( $data=array() ){
		if( isset($data['id']) ) $this->id = (int) $data['id'];
		if( isset($data['name']) ) $this->name = $data['name'];
		if( isset($data['cat_id']) ) $this->cat_id = (int) $data['cat_id'];
	}
	
	/**
	 * Устанавливаем свойства подкатегории из формы редактирования
	 * @param assoc $params Значения свойств, переданные из формы
	 * 
	 */
	public function storeFormValues($params){
		$this->__construct( $params );
	}
	
	
	/**
	 * Возвращаем объект Subcategory. соответствующий заданнному  ID
	 * 
	 * @param int $id Идентификатор субкатегории
	 * @return Subcategory|false Возвращает объект Subcategory или  false, если 
	 * запись не была найдена или в случае ошибки
	 * 
	 */
	public static function getById($id){
		$conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
		$sql = "SELECT * FROM subcategories WHERE id = :id";
		$st = $conn->prepare($sql);
		$st->bindValue(":id", $id, PDO::PARAM_INT);
		$st->execute();
		$row = $st->fetch();
		$conn = null;
		if($row){
			return new Subcategory($row);
		}
	}
	/**
	 *  Узнаём id выбранной пользователем категории по её названию
	 * 
	 * @param string $name Категория, которую выбрал пользователь
	 * @return int идентификатор категории  
	 */
	public static function getCategIdByName($name){
		$conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
		$sql = "SELECT id FROM categories WHERE name = :name";
		$st = $conn->prepare($sql);
		$st->bindValue(":name", $name, PDO::PARAM_STR);
		$st->execute();
		$row = $st->fetch();
		$conn = null;
		if($row){
			return $row[0];
		}
	}
	
	/**
	 * Возвращаем все объекты Subcategory из базы данных и их количество
	 * 
	 * @param int $numRows Количество возвращаемых объектов(по умолчанию all)
	 * @param string $order порядок сортировки объектов(по умолчанию "name ASC")
	 * @return array|false возвращаем либо неудачу либо массив из 2-х элементов:
	 * массив с искомыми объектами субкатегорий и их количество  
	 */
	public static function getList($numRows=1000000, $order="name ASC")
	{
		$conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
		
		$sql = "SELECT * FROM subcategories ORDER BY $order LIMIT :numRows";
		
		$st= $conn->prepare($sql);
		$st->bindValue(":numRows", $numRows, PDO::PARAM_INT );
		$st->execute();
		$list = array();
		
		while( $row = $st->fetch() ){
			$subcategory = new Subcategory($row);
			$list[] = $subcategory;
		}
		
		$sql = "SELECT FOUND_ROWS() AS totalRows";
		$totalRows = $conn->query($sql)->fetch();
		$conn = null;
		return (array("results" => $list, "totalRows" => $totalRows[0] ) );
	}
	
	/**
	 * 
	 * @param string $name Название подкатегории которую нужно проверить на существование 
	 * @return boolean  Возвращаем true если подкатегория существует
	 */
	public static function isSubcategoryExist($name){
		$conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
		$sql = "SELECT name FROM subcategories WHERE name = :name";
		$st = $conn->prepare($sql);
		$st->bindValue(":name", $name, PDO::PARAM_STR);
		$st->execute();
		if($st->fetch()[0]){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * Вставляем текущий обьект Subcategory в базу данных и устанавливаем его свойство ID 
	 */
	
	public function insert(){
		// Проверяем есть ли уже у обьекта Subcategory ID ?
		if ( !is_null( $this->id ) ) trigger_error ( "Subcategory::insert(): Attempt to insert a Subcategory object that already has its ID property set (to $this->id).", E_USER_ERROR );
		//Вставляем субкатегорию
		$conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
		$sql = "INSERT INTO subcategories(name, cat_id) VALUES(:name, :cat_id)";
		$st = $conn->prepare($sql);
		$st->bindValue(":name", $this->name, PDO::PARAM_STR );
		$st->bindValue(":cat_id", $this->cat_id, PDO::PARAM_INT );
		$st->execute();
		$this->id = $conn->lastInsertId();
		$conn = null;
	}
	
	public function update(){
		// Проверяем есть ли уже у обьекта Subcategory ID ?
		if ( !is_null( $this->id ) ) trigger_error ( "Subcategory::insert(): Attempt to insert a Subcategory object that already has its ID property set (to $this->id).", E_USER_ERROR );
	    $conn = new PDO (DB_DSN, DB_USERNAME, DB_PASSWORD );
		$sql = "UPDATE subcategories SET name=:name, cat_id=:cat_id WHERE id=:id";
		$st = $conn->prepare($sql);
		$st->bindValue(":name", $this->name, PDO::PARAM_STR);
		$st->bindValue(":name", $this->cat_id, PDO::PARAM_INT);
		$st->execute();
		$conn = null;
	}
	
	public function delate(){
		// У объекта Subcategory  есть ID?
      if ( is_null( $this->id ) ) trigger_error ( "Subcategory::delete(): Attempt to delete a Subcategory object that does not have its ID property set.", E_USER_ERROR );

      // Удаляем подкатегорию
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $st = $conn->prepare ( "DELETE FROM subcategories WHERE id = :id LIMIT 1" );
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
      $st->execute();
      $conn = null;
		
	}

}
