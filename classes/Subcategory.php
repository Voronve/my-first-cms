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
		if( isset($data['name']) ) $this->name = (int) $data['name'];
		if( isset($data['cat_id']) ) $this->cat_id = (int) $data['cat_id'];
	}
	
	/**
	 * Устанавливаем свойства подкатегориииз формы редактирования
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
	public function getById($id){
		$conn = PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
		$sql = "SELECT * FROM subcategory WHERE id = :id";
		$st = $conn->prepare($sql);
		$st->bindValue(":id", $id, PDO::PARAM_INT);
		$st->execute();
		$row = $st->fetch();
		$conn = null;
		if($row){
			return new Subcategory($row);
		}
		
	}
	
	
	
	
	
	
}
