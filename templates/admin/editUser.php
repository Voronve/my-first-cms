<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>

        <h1><?php echo $results['pageTitle']?></h1>

        <form action="admin.php?action=<?php echo $results['formAction']?>" method="post"> 
          <!-- Обработка формы будет направлена файлу admin.php ф-ции newUser либо editUser в зависимости от formAction, сохранённого в result-е -->
        <input type="hidden" name="userId" value="<?php echo $results['user']->id ?>"/>

    <?php if ( isset( $results['errorMessage'] ) ) { ?>
            <div class="errorMessage"><?php echo $results['errorMessage'] ?></div>
    <?php } ?>

        <ul>

          <li>
            <label for="name">User name</label>
            <input type="text" name="name" id="name" placeholder="Name of the user" required autofocus maxlength="255" value="<?php echo htmlspecialchars( $results['user']->name )?>" />
          </li>
          <li>
            <label for="pass">Password</label>
            <input type="text" name="pass" id="pass" placeholder="Users password" required autofocus maxlength="255" value="<?php echo htmlspecialchars( $results['user']->pass )?>" />
          </li>
          <li>
			<label for="active" style="line-height: 0;">Activeness</label>
			<input style="width: auto;" type="checkbox" value="1" name="active" <?php 
					if($results['user']->active || $_GET['action'] == 'newUser'){
						echo 'checked'; }?>>
          </li>

        </ul>

        <div class="buttons">
          <input type="submit" name="saveChanges" value="Save Changes" />
          <input type="submit" formnovalidate name="cancel" value="Cancel" />
        </div>

      </form>

    <?php if ( $results['user']->id ) { ?>
          <p><a href="admin.php?action=deleteUser&amp;userId=<?php echo $results['user']->id ?>" onclick="return confirm('Delete This User?')">Delete This User</a></p>
    <?php } ?>

<?php include "templates/include/footer.php" ?>

