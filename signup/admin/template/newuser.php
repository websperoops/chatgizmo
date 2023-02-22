<?php include "header.php";?>

<article class="content forms-page">
    <div class="title-block">
        <h1 class="title"><?php echo $SECTION_TITLE;?></h1>
        <p class="title-description"><?php echo $SECTION_DESC;?></p>
    </div>
    <?php if ($errors) { ?>
	<div class="alert alert-danger">
	<?php if (isset($errors["e"])) echo $errors["e"];
		  if (isset($errors["e1"])) echo $errors["e1"];
		  if (isset($errors["e2"])) echo $errors["e2"];
		  if (isset($errors["e3"])) echo $errors["e3"];
		  if (isset($errors["e4"])) echo $errors["e4"];
		  if (isset($errors["e5"])) echo $errors["e5"];
		  if (isset($errors["e6"])) echo $errors["e6"];?>
	</div>
	<?php } ?>
    <section class="section">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-block sameheight-item">
                    <div class="title-block">
                    	<h3 class="title"><?php echo $SECTION_TITLE;?></h3>
                    </div>
                    <form class="jak_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
                    	<div class="form-group<?php if (isset($errors["e"])) echo " has-error";?>">
                    		<label class="control-label" for="name"><?php echo $jkl['g24'];?></label>
                    		<input type="text" name="name" class="form-control underlined" id="name" value="<?php if (isset($_REQUEST["name"])) echo $_REQUEST["name"];?>">
                    	</div>

                    	<div class="form-group<?php if (isset($errors["e2"]) || isset($errors["e3"])) echo " has-error";?>">
                    		<label class="control-label" for="username"><?php echo $jkl['g25'];?></label>
                    		<input type="text" name="username" class="form-control underlined" id="username" value="<?php if (isset($_REQUEST["username"])) echo $_REQUEST["username"];?>">
                    	</div>

                    	<div class="form-group<?php if (isset($errors["e1"]) || isset($errors["e4"])) echo " has-error";?>">
                    		<label class="control-label" for="email"><?php echo $jkl['g26'];?></label>
                    		<input type="text" name="email" class="form-control underlined" id="email" value="<?php if (isset($_REQUEST["email"])) echo $_REQUEST["email"];?>">
                    	</div>

                    	<div class="row">
                    		<div class="col-md-11">
                    			<div class="form-group">
                    				<select name="avatar" id="avatar" class="form-control">
										<option value="/standard.png" selected>Avatar 1</option>
										<option value="/avatar.png">Avatar 2</option>
										<option value="/avatar2.png">Avatar 3</option>
										<option value="/avatar3.png">Avatar 4</option>
										<option value="/avatar4.png">Avatar 5</option>
									</select>
								</div>
                    		</div>
                    		<div class="col-md-1">
                    			<p><img id="avatarc" class="img-rounded" src="<?php echo BASE_URL_ORIG;?>img/avatars/standard.png" alt="avatar" width="40"></p>
                    		</div>
                    	</div>

                    	<?php if (JAK_USERID == 1) { ?>
                    	<div class="form-group">
                    		<label class="control-label"><?php echo $jkl['g34'];?></label>
	                        <div>
	                        <label>
				                <input class="checkbox" type="checkbox" name="permissions[]" value="c"><span><?php echo $jkl['g35'];?></span>
				            </label>
				            <label>
				                <input class="checkbox" type="checkbox" name="permissions[]" value="l"><span><?php echo $jkl['g36'];?></span>
				            </label>
				            <label>
				                <input class="checkbox" type="checkbox" name="permissions[]" value="p"><span><?php echo $jkl['g37'];?></span>
				            </label>
                            <label>
                                <input class="checkbox" type="checkbox" name="permissions[]" value="s"><span><?php echo $jkl['g66'];?></span>
                            </label>
				            </div>
                        </div>
                        <?php } ?>

                    	<div class="form-group<?php if (isset($errors["e5"]) || isset($errors["e6"])) echo " has-error";?>">
                    		<label class="control-label" for="pass"><?php echo $jkl['g2'];?></label>
                    		<input type="password" name="pass" class="form-control underlined" id="pass">
                    	</div>

                    	<div class="form-group">
                    		<label class="control-label" for="passc"><?php echo $jkl['g33'];?></label>
                    		<input type="password" name="passc" class="form-control underlined" id="passc">
                    	</div>

                    	<p><button type="submit" name="save" class="btn btn-success"><i class="fa fa-floppy-o"></i> <?php echo $jkl["g31"];?></button></p>
                    </form>
            	</div>
       		</div>
    	</div>
    </section>
</article>

<?php include "footer.php";?>