<?php

require("config.php");
session_start();
$action = isset($_GET['action']) ? $_GET['action'] : "";
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "";

if ($action != "login" && $action != "logout" && !$username) {
	login();
	exit;
}

switch ($action) {
	case 'login':
		login();
		break;
	case 'logout':
		logout();
		break;
	case 'newArticle':
		newArticle();
		break;
	case 'editArticle':
		editArticle();
		break;
	case 'deleteArticle':
		deleteArticle();
		break;
	case 'listCategories':
		listCategories();
		break;
	case 'newCategory':
		newCategory();
		break;
	case 'editCategory':
		editCategory();
		break;
	case 'deleteCategory':
		deleteCategory();
		break;
	case 'listSubcategories':
		listSubcategories();
		break;
	case 'newSubcategory':
		newSubcategory();
		break;
	case 'editSubcategory':
		editSubcategory();
		break;
	case 'deleteSubcategory':
		deleteSubcategory();
		break;
	case 'listUsers':
		listUsers();
		break;
	case 'editUser':
		editUser();
		break;
	case 'deleteUser':
		deleteUser();
		break;
	case 'newUser':
		newUser();
		break;
	default:
		listArticles();
}

/**
 * Авторизация пользователя (админа) -- установка значения в сессию
 */
function login() {

	$results = array();
	$results['pageTitle'] = "Admin Login | Widget News";

	if (isset($_POST['login'])) {

		// Пользователь получает форму входа: попытка авторизировать пользователя

		if ($_POST['username'] == ADMIN_USERNAME && $_POST['password'] == ADMIN_PASSWORD) {

			// Вход прошел успешно: создаем сессию и перенаправляем на страницу администратора
			$_SESSION['username'] = ADMIN_USERNAME;
			header("Location: admin.php");
		} else {
			$conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
			$sql = "SELECT * FROM users WHERE name = :name AND pass = :pass";
			$prep = $conn->prepare($sql);
			$prep->bindValue(":name", $_POST['username'], PDO::PARAM_STR);
			$prep->bindValue(":pass", $_POST['password'], PDO::PARAM_STR);
			$prep->execute();
			$row = $prep->fetch();
			if ($row['name']) {
				if ($row['active']) {
					$_SESSION['username'] = $row['name'];
					header("Location: admin.php");
				} else {
					$results['errorMessage'] = "Извините, но вам запрещен доступ";
					require( TEMPLATE_PATH . "/admin/loginForm.php" );
				}
			} else {
				// Ошибка входа: выводим сообщение об ошибке для пользователя
				$results['errorMessage'] = "Неправильный логин или пароль, попробуйте ещё раз.";
				require( TEMPLATE_PATH . "/admin/loginForm.php" );
			}
			/* $results['errorMessage'] = "Неправильный логин или пароль, попробуйте ещё раз.";
			  require( TEMPLATE_PATH . "/admin/loginForm.php" ); */
		}
	} else {
		// Пользователь еще не получил форму: выводим форму
		require(TEMPLATE_PATH . "/admin/loginForm.php");
	}
}

function logout() {
	unset($_SESSION['username']);
	header("Location: admin.php");
}

function newArticle() {

	$results = array();
	$results['pageTitle'] = "New Article";
	$results['formAction'] = "newArticle";

	if (isset($_POST['saveChanges'])) {
//            echo "<pre>";
//            print_r($results);
//            print_r($_POST);
//            echo "<pre>";
//            В $_POST данные о статье сохраняются корректно
        if ($_POST['categoryId'] != Subcategory::getById($_POST['subcategoryId'])->cat_id) {
			header("Location: admin.php?error=CategoryNotMatch");
			return;
		}
		// Пользователь получает форму редактирования статьи: сохраняем новую статью
		$article = new Article();
		$article->storeFormValues($_POST);
//            echo "<pre>";
//            print_r($article);
//            echo "<pre>";
//            А здесь данные массива $article уже неполные(есть только Число от даты, категория и полный текст статьи)          
		$article->insert();
		
		//Сохраняем новые связи статья-авторы
		$activeAuthorsId = $_POST['authorsId'];
		$connection = 0;
		foreach ($activeAuthorsId as $authorId) 
		{
			$connData['article_id'] = $article->id;
			$connData['user_id'] = $authorId;
			$connection = new Connection($connData);
			$connection->insert($connData);
		}
		header("Location: admin.php?status=changesSaved");
	} elseif (isset($_POST['cancel'])) {

		// Пользователь сбросил результаты редактирования: возвращаемся к списку статей
		header("Location: admin.php");
	} else {

		// Пользователь еще не получил форму редактирования: выводим форму
		$results['article'] = new Article;
		$data = Category::getList();
		$results['categories'] = $data['results'];
		$data = Subcategory::getList();
		$results['subcategories'] = $data['results'];
		$data = User::getlist();
		$results['users'] = $data['results'];
		$results['authors'] = array();
		//
		$results['categoryIdCompare'] = null;
		require( TEMPLATE_PATH . "/admin/editArticle.php" );
	}
}

/**
 * Редактирование статьи
 * 
 * @return null
 */
function editArticle() {

	$results = array();
	$activeAuthorsId = array();
	$results['pageTitle'] = "Edit Article";
	$results['formAction'] = "editArticle";

	if (isset($_POST['saveChanges'])) {

		// Пользователь получил форму редактирования статьи: сохраняем изменения
		if (!$article = Article::getById((int) $_POST['articleId'])) {
			header("Location: admin.php?error=articleNotFound");
			return;
		}
		if ($_POST['categoryId'] != Subcategory::getById($_POST['subcategoryId'])->cat_id) {
			header("Location: admin.php?error=CategoryNotMatch");
			return;
		}
		$article->storeFormValues($_POST);
		$article->update();
		
		//Удалаем предыдущие связи  и устанавливаем новые
		$connections = Connection::getById($article->id);
		foreach ($connections as $connection){
			$connection->delete();
		}
		$activeAuthorsId = $_POST['authorsId'];
		$connection = 0;
		foreach ($activeAuthorsId as $authorId) {
			$connData['article_id'] = $article->id;
			$connData['user_id'] = $authorId;
			$connection = new Connection($connData);
			$connection->insert();
		}
		header("Location: admin.php?status=changesSaved");
	} elseif (isset($_POST['cancel'])) {

		// Пользователь отказался от результатов редактирования: возвращаемся к списку статей
		header("Location: admin.php");
	} else {

		// Пользвоатель еще не получил форму редактирования: выводим форму
		$results['article'] = Article::getById((int) $_GET['articleId']);
		$data = Subcategory::getList();
		$results['subcategories'] = $data['results'];
		$data = Category::getList();
		$results['categories'] = $data['results'];
		$data = User::getlist();
		$results['users'] = $data['results'];
		$data = Connection::getById($results['article']->id);
		$results['authors'] = array();
		foreach($data as $connection){
			$results['authors'][] = $connection->userId;
		}
		$results['categoryIdCompare'] = Subcategory::getById($results['article']->subcategoryId)->cat_id;
		
	}
		require(TEMPLATE_PATH . "/admin/editArticle.php");
}


function deleteArticle() {

	if (!$article = Article::getById((int) $_GET['articleId'])) {
		header("Location: admin.php?error=articleNotFound");
		return;
	}

	$article->delete();
	header("Location: admin.php?status=articleDeleted");
}

function listArticles() {
	$results = array();

	$data = Article::getList();
	$results['articles'] = $data['results'];
	$results['totalRows'] = $data['totalRows'];

	$data = Subcategory::getList();
	$results['subcategories'] = array();
	foreach ($data['results'] as $subcategory) {
		$results['subcategories'][$subcategory->id] = $subcategory;
		$results['categories'][$subcategory->id] = Category::getById($subcategory->cat_id);
	}

	$results['pageTitle'] = "Все статьи";

	if (isset($_GET['error'])) { // вывод сообщения об ошибке (если есть)
		if ($_GET['error'] == "articleNotFound") {
			$results['errorMessage'] = "Error: Article not found.";
		} elseif ($_GET['error'] == "CategoryNotMatch") {
			$results['errorMessage'] = "Error: Category doesn't match to subcategory";
		}
	}

	if (isset($_GET['status'])) { // вывод сообщения (если есть)
		if ($_GET['status'] == "changesSaved") {
			$results['statusMessage'] = "Your changes have been saved.";
		}
		if ($_GET['status'] == "articleDeleted") {
			$results['statusMessage'] = "Article deleted.";
		}
	}

	require(TEMPLATE_PATH . "/admin/listArticles.php" );
}

function listCategories() {
	$results = array();
	$data = Category::getList();
	$results['categories'] = $data['results'];
	$results['totalRows'] = $data['totalRows'];
	$results['pageTitle'] = "List of categories";

	if (isset($_GET['error'])) {
		if ($_GET['error'] == "categoryNotFound")
			$results['errorMessage'] = "Error: Category not found.";
		if ($_GET['error'] == "categoryContainsArticles")
			$results['errorMessage'] = "Error: Category contains articles. Delete the articles, or assign them to another category, before deleting this category.";
	}

	if (isset($_GET['status'])) {
		if ($_GET['status'] == "changesSaved")
			$results['statusMessage'] = "Your changes have been saved.";
		if ($_GET['status'] == "categoryDeleted")
			$results['statusMessage'] = "Category deleted.";
	}

	require( TEMPLATE_PATH . "/admin/listCategories.php" );
}

function newCategory() {

	$results = array();
	$results['pageTitle'] = "New Article Category";
	$results['formAction'] = "newCategory";

	if (isset($_POST['saveChanges'])) {

		// User has posted the category edit form: save the new category
		$category = new Category;
		$category->storeFormValues($_POST);
		$category->insert();
		header("Location: admin.php?action=listCategories&status=changesSaved");
	} elseif (isset($_POST['cancel'])) {

		// User has cancelled their edits: return to the category list
		header("Location: admin.php?action=listCategories");
	} else {

		// User has not posted the category edit form yet: display the form
		$results['category'] = new Category;
		require( TEMPLATE_PATH . "/admin/editCategory.php" );
	}
}

function editCategory() {

	$results = array();
	$results['pageTitle'] = "Edit Article Category";
	$results['formAction'] = "editCategory";

	if (isset($_POST['saveChanges'])) {

		// User has posted the category edit form: save the category changes

		if (!$category = Category::getById((int) $_POST['categoryId'])) {
			header("Location: admin.php?action=listCategories&error=categoryNotFound");
			return;
		}

		$category->storeFormValues($_POST);
		$category->update();
		header("Location: admin.php?action=listCategories&status=changesSaved");
	} elseif (isset($_POST['cancel'])) {

		// User has cancelled their edits: return to the category list
		header("Location: admin.php?action=listCategories");
	} else {

		// User has not posted the category edit form yet: display the form
		$results['category'] = Category::getById((int) $_GET['categoryId']);
		require( TEMPLATE_PATH . "/admin/editCategory.php" );
	}
}

function deleteCategory() {

	if (!$category = Category::getById((int) $_GET['categoryId'])) {
		header("Location: admin.php?action=listCategories&error=categoryNotFound");
		return;
	}
	$subcategories = Subcategory::getList(1000000, $category->id);

	if ($subcategories['totalRows'] > 0) {
		header("Location: admin.php?action=listCategories&error=categoryContainsArticles");
		return;
	}

	$category->delete();
	header("Location: admin.php?action=listCategories&status=categoryDeleted");
}

function listSubcategories() {
	$results = array();
	$data = Subcategory::getList();
	$results['subcategories'] = $data['results'];
	$results['totalRows'] = $data['totalRows'];
	$results['pageTitle'] = "List of subcategories";
	//Извлекаем название категории по Id
	foreach ($results['subcategories'] as $subcategory) {
		$category = Category::getById($subcategory->cat_id);
		$subcategory->cat_name = $category->name;
	}
	if (isset($_GET['error'])) {
		if ($_GET['error'] == "subcategoryNotFound")
			$results['errorMessage'] = "Error: Subcategory not found.";
		if ($_GET['error'] == "subcategoryContainsArticles")
			$results['errorMessage'] = "Error: Subcategory contains articles. Delete the articles, or assign them to another subcategory, before deleting this subcategory.";
	}

	if (isset($_GET['status'])) {
		if ($_GET['status'] == "changesSaved")
			$results['statusMessage'] = "Your changes have been saved.";
		if ($_GET['status'] == "subcategoryDeleted")
			$results['statusMessage'] = "Subcategory deleted.";
	}

	require( TEMPLATE_PATH . "/admin/listSubcategories.php" );
}

function newSubcategory() {

	$results = array();
	$results['pageTitle'] = "New Article Subcategory";
	$results['formAction'] = "newSubcategory";

	if (isset($_POST['saveChanges'])) {
		//Находим идентификатор категории по её названию в форме
		$_POST['cat_id'] = Subcategory::getCategIdByName($_POST['category']);
		// User has posted the subcategory edit form: save the new subcategory
		$subcategory = new Subcategory;
		$subcategory->storeFormValues($_POST);
		$subcategory->insert();
		header("Location: admin.php?action=listSubcategories&status=changesSaved");
	} elseif (isset($_POST['cancel'])) {

		// User has cancelled their edits: return to the category list
		header("Location: admin.php?action=listSubcategories");
	} else {

		// User has not posted the category edit form yet: display the form
		$results['subcategory'] = new Subcategory;
		$categories = Category::getList();
		$catname = array();
		foreach ($categories['results'] as $category) {
			$catname[] = $category->name;
		}
		/* print_r($catname);die; */

		require( TEMPLATE_PATH . "/admin/editSubcategory.php" );
	}
}

function editSubcategory() {

	$results = array();
	$results['pageTitle'] = "Edit Article Subcategory";
	$results['formAction'] = "editSubcategory";

	if (isset($_POST['saveChanges'])) {

		// User has changed the subcategory: save the category changes

		if (!$subcategory = Subcategory::getById((int) $_POST['subcategoryId'])) {
			header("Location: admin.php?action=listSubcategories&error=categoryNotFound");
			return;
		}
		//Находим идентификатор категории по её названию в форме
		$_POST['cat_id'] = Subcategory::getCategIdByName($_POST['category']);
		$subcategory->storeFormValues($_POST);
		$subcategory->update();
		header("Location: admin.php?action=listSubcategories&status=changesSaved");
	} elseif (isset($_POST['cancel'])) {

		// User has cancelled their edits: return to the category list
		header("Location: admin.php?action=listSubcategories");
	} else {

		// User has not posted the subcategory edit form yet: display the form
		$results['subcategory'] = Subcategory::getById((int) $_GET['subcategoryId']);
		$categories = Category::getList();
		$catname = array();
		foreach ($categories['results'] as $category) {
			$catname[] = $category->name;
		}
		require( TEMPLATE_PATH . "/admin/editSubcategory.php" );
	}
}

function deleteSubcategory() {

	if (!$subcategory = Subcategory::getById((int) $_GET['subcategoryId'])) {
		header("Location: admin.php?action=listSubcategories&error=categoryNotFound");
		return;
	}

	$articles = Article::getList(1000000, $subcategory->id, true);
	//print_r($articles); die;


	if ($articles['totalRows'] > 0) {
		header("Location: admin.php?action=listSubcategories&error=subcategoryContainsArticles");
		return;
	}

	$subcategory->delete();
	header("Location: admin.php?action=listSubcategories&status=subcategoryDeleted");
}

function listUsers() {
	$results = array();
	$data = User::getList();
	$results['users'] = $data['results'];
	$results['totalRows'] = $data['totalRows'];
	$results['pageTitle'] = "User list";

	if (isset($_GET['error'])) {
		if ($_GET['error'] == "usersNotFound")
			$results['errorMessage'] = "Error: User not found.";
		if ($_GET['error'] == "userExist")
			$results['errorMessage'] = "Error: User with such name is alredy exist.";
	}

	if (isset($_GET['status'])) {
		if ($_GET['status'] == "changesSaved")
			$results['statusMessage'] = "Your changes have been saved.";
		if ($_GET['status'] == "userDeleted")
			$results['statusMessage'] = "User deleted.";
	}

	require( TEMPLATE_PATH . "/admin/listUsers.php" );
}

function editUser() {

	$results = array();
	$results['pageTitle'] = "Edit User";
	$results['formAction'] = "editUser";

	if (isset($_POST['saveChanges'])) {

		// Admin has changed users data: save the changes

		if (!$user = User::getById((int) $_POST['userId'])) {
			header("Location: admin.php?action=listUsers&error=userNotFound");
			return;
		}

		$user->storeFormValues($_POST);
		$user->update();
		header("Location: admin.php?action=listUsers&status=changesSaved");
	} elseif (isset($_POST['cancel'])) {

		// User has cancelled his edits: return to the users list
		header("Location: admin.php?action=listUsers");
	} else {

		// User has not posted the category edit form yet: display the form
		$results['user'] = User::getById((int) $_GET['userId']);
		require( TEMPLATE_PATH . "/admin/editUser.php" );
	}
}

function deleteUser() {

	if (!$user = User::getById((int) $_GET['userId'])) {
		header("Location: admin.php?error=userNotFound");
		return;
	}

	$user->delete();
	header("Location: admin.php?status=userDeleted&action=listUsers");
}

function newUser() {

	$results = array();
	$results['pageTitle'] = "Add new user";
	$results['formAction'] = "newUser";

	if (isset($_POST['saveChanges'])) {

		// User has added a new user - the change is saved
		$category = new User;
		if (User::isUserExist($_POST['name'])) {
			header("Location: admin.php?action=listUsers&error=userExist");
		} else {
			$category->storeFormValues($_POST);
			$category->insert();
			header("Location: admin.php?action=listUsers&status=changesSaved");
		}
	} elseif (isset($_POST['cancel'])) {

		// User has cancelled their edits: return to the category list
		header("Location: admin.php?action=listUsers");
	} else {

		// User has not posted the category edit form yet: display the form
		$results['user'] = new User;
		require( TEMPLATE_PATH . "/admin/editUser.php" );
	}
}
