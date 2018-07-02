<?php

//phpinfo(); die();

require("config.php");

try {
    initApplication();
} catch (Exception $e) { 
    $results['errorMessage'] = $e->getMessage();
    require(TEMPLATE_PATH . "/viewErrorPage.php");
}


function initApplication()
{
    $action = isset($_GET['action']) ? $_GET['action'] : "";

    switch ($action) {
        case 'archiveSubcat':
          archiveSubcat();
          break;
	  case 'archiveCat':
          archiveCat();
          break;
        case 'viewArticle':
          viewArticle();
          break;
        default:
          homepage();
    }
}

function archiveSubcat() 
{
    $results = [];
    
    $subcategoryId = ( isset( $_GET['subcategoryId'] ) && $_GET['subcategoryId'] ) ? (int)$_GET['subcategoryId'] : null;
    $results['category'] = 0;
    $results['subcategory'] = Subcategory::getById( $subcategoryId );
    
    $data = Article::getList( 100000, $results['subcategory'] ? $results['subcategory']->id : null, true );
    
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    
    $data = Subcategory::getList();
    $results['subcategories'] = array();
    
    foreach ( $data['results'] as $subcategory ) {
        $results['subcategories'][$subcategory->id] = $subcategory;
    }
    
    $results['pageHeading'] = $results['subcategory'] ?  $results['subcategory']->name : "Article Archive";
    $results['pageTitle'] = $results['pageHeading'] . " | Widget News";
    
    require( TEMPLATE_PATH . "/archive.php" );
}

function archiveCat() 
{
    $results = [];
    
    $subcategoryId = ( isset( $_GET['subcategoryId'] ) && $_GET['subcategoryId'] ) ? (int)$_GET['subcategoryId'] : null;
    
    $results['subcategory'] = Subcategory::getById( $subcategoryId );
	$results['category'] = Category::getById( $results['subcategory']->cat_id );
	$data = Subcategory::getList(1000000, $results['subcategory']->cat_id);
	$articleArr = array();
	foreach($data['results'] as $subcategory){
		$articleArr[] = Article::getList(1000000, $subcategory->id, true);
		
	}
	
	$results['articles'] = array();
	$results['totalRows'] = 0;
	for( $i = 0; $i < count($articleArr); $i++){
		$results['articles'] = array_merge($results['articles'], $articleArr[$i]['results']);
		$results['totalRows'] = $results['totalRows'] + $articleArr[$i]['totalRows'];
	}
	$results['pageHeading'] = $results['category'] ?  $results['category']->name : "Article Archive";
	
    require( TEMPLATE_PATH . "/archive.php" );
}

/**
 * Загрузка страницы с конкретной статьёй
 * 
 * @return null
 */
function viewArticle() 
{   
    if ( !isset($_GET["articleId"]) || !$_GET["articleId"] ) {
      homepage();
      return;
    }

    $results = array();
    $articleId = (int)$_GET["articleId"];
    $results['article'] = Article::getById($articleId);
    
    if (!$results['article']) {
        throw new Exception("Статья с id = $articleId не найдена");
    }
    
    $results['subcategory'] = Subcategory::getById($results['article']->subcategoryId);
    $results['pageTitle'] = $results['article']->title . " | Простая CMS";
    
    require(TEMPLATE_PATH . "/viewArticle.php");
}

/**
 * Вывод домашней ("главной") страницы сайта
 */
function homepage() 
{
    $results = array();
    $data = Article::getList(HOMEPAGE_NUM_ARTICLES);
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    $data = Subcategory::getList();
    $results['subcategories'] = array();
    foreach ( $data['results'] as $subcategory ) { 
        $results['subcategories'][$subcategory->id] = $subcategory;
		$results['categories'][$subcategory->id] = Category::getById($subcategory->cat_id);
    }
	foreach ( $results['articles'] as $article ) { 
        $article->content = mb_substr($article->content, 0, 50) . ' ...';
    } 
    
    $results['pageTitle'] = "Простая CMS на PHP";
    
//    echo "<pre>";
//    print_r($data);
//    echo "</pre>";
//    die();
    
    require(TEMPLATE_PATH . "/homepage.php");
    
}