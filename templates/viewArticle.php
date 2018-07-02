<?php include "templates/include/header.php" ?>
	  
    <h1 style="width: 75%;"><?php echo htmlspecialchars( $results['article']->title )?></h1>
    <div style="width: 75%; font-style: italic;"><?php echo htmlspecialchars( $results['article']->summary )?></div>
    <div style="width: 75%;"><?php echo $results['article']->content?></div>
    <p class="pubDate">Published on <?php  echo date('j F Y', $results['article']->publicationDate)?>
    
    <?php if ( $results['subcategory'] ) { ?>
        in subcategory
        <a href="./?action=archiveSubcat&amp;subcategoryId=<?php echo $results['subcategory']->id?>">
            <?php echo htmlspecialchars($results['subcategory']->name) ?>
        </a>
    <?php } ?> 
    </p>

    <p><a href="./">Вернуться на главную страницу</a></p>
	  
<?php include "templates/include/footer.php" ?>    
                