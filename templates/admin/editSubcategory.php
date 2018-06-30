<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>

        <h1><?php echo $results['pageTitle']?></h1>

        <form id="subcat" action="admin.php?action=<?php echo $results['formAction']?>" method="post"> 
          <!-- Обработка формы будет направлена файлу admin.php ф-ции newSubcategory либо editSubcategory в зависимости от formAction, сохранённого в result-е -->
        <input type="hidden" name="subcategoryId" value="<?php echo $results['subcategory']->id ?>"/>

    <?php if ( isset( $results['errorMessage'] ) ) { ?>
            <div class="errorMessage"><?php echo $results['errorMessage'] ?></div>
    <?php } ?>

        <ul>

          <li>
            <label for="name">Subcategory Name</label>
            <input type="text" name="name" id="name" placeholder="Name of the subcategory" required autofocus maxlength="255" value="<?php echo htmlspecialchars( $results['subcategory']->name )?>" />
          </li>

          <li>
            <label for="category">Choose category</label>
             <select name="category" form="subcat">
				<?php for($i = 0; $i < $categories["totalRows"]; $i++ ){
					echo "<option value='$catname[$i]'>$catname[$i]</option>";} ?>
			</select> 
          </li>

        </ul>

        <div class="buttons">
          <input type="submit" name="saveChanges" value="Save Changes" />
          <input type="submit" formnovalidate name="cancel" value="Cancel" />
        </div>

      </form>

    <?php if ( $results['subcategory']->id ) { ?>
          <p><a href="admin.php?action=deleteSubcategory&amp;subcategoryId=<?php echo $results['subcategory']->id ?>" onclick="return confirm('Delete This subcategory?')">Delete This Subcategory</a></p>
    <?php } ?>

<?php include "templates/include/footer.php" ?>

