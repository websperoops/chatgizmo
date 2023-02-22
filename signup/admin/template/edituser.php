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
                    		<input type="text" name="name" class="form-control underlined" id="name" value="<?php echo $user["name"];?>">
                    	</div>

                    	<div class="form-group<?php if (isset($errors["e2"]) || isset($errors["e3"])) echo " has-error";?>">
                    		<label class="control-label" for="username"><?php echo $jkl['g25'];?></label>
                    		<input type="text" name="username" class="form-control underlined" id="username" value="<?php echo $user["username"];?>">
                    	</div>

                    	<div class="form-group<?php if (isset($errors["e1"]) || isset($errors["e4"])) echo " has-error";?>">
                    		<label class="control-label" for="email"><?php echo $jkl['g26'];?></label>
                    		<input type="text" name="email" class="form-control underlined" id="email" value="<?php echo $user["email"];?>">
                    	</div>

                        <div class="form-group">
                            <label class="control-label" for="jak_lang"><?php echo $jkl["g246"];?></label>
                            <select name="jak_lang" class="form-control">
                                <option value="<?php echo JAK_LANG;?>"><?php echo $jkl["g247"];?></option>
                                <?php if (isset($lang_files) && is_array($lang_files)) foreach($lang_files as $lf) { ?><option value="<?php echo $lf;?>"<?php if ($user["language"] == $lf) { ?> selected="selected"<?php } ?>><?php echo ucwords($lf);?></option><?php } ?>
                            </select>
                        </div>

                    	<div class="row">
                    		<div class="col-md-11">
                    			<div class="form-group">
                                    <label class="control-label" for="jak_lang"><?php echo $jkl["g248"];?></label>
                    				<select name="avatar" id="avatar" class="form-control">
										<option value="/standard.png"<?php if ($user["picture"] == "/standard.png") echo " selected";?>>Avatar 1</option>
										<option value="/avatar.png"<?php if ($user["picture"] == "/avatar.png") echo " selected";?>>Avatar 2</option>
										<option value="/avatar2.png"<?php if ($user["picture"] == "/avatar2.png") echo " selected";?>>Avatar 3</option>
										<option value="/avatar3.png"<?php if ($user["picture"] == "/avatar3.png") echo " selected";?>>Avatar 4</option>
										<option value="/avatar4.png"<?php if ($user["picture"] == "/avatar4.png") echo " selected";?>>Avatar 5</option>
									</select>
								</div>
                    		</div>
                    		<div class="col-md-1">
                    			<p><img id="avatarc" class="img-rounded" src="<?php echo BASE_URL_ORIG;?>img/avatars<?php echo $jakuser->getVar("picture");?>" alt="avatar" width="80"></p>
                    		</div>
                    	</div>

                    	<?php if (JAK_USERID == 1) { ?>
                    	<div class="form-group">
                    		<label class="control-label"><?php echo $jkl['g34'];?></label>
	                        <div>
	                        <label>
				                <input class="checkbox" type="checkbox" name="permissions[]" value="c"<?php if (in_array("c", explode(',', $user["permissions"] ?? ''))) { ?> checked="checked"<?php } ?>><span><?php echo $jkl['g35'];?></span>
				            </label>
				            <label>
				                <input class="checkbox" type="checkbox" name="permissions[]" value="l"<?php if (in_array("l", explode(',', $user["permissions"] ?? ''))) { ?> checked="checked"<?php } ?>><span><?php echo $jkl['g36'];?></span>
				            </label>
				            <label>
				                <input class="checkbox" type="checkbox" name="permissions[]" value="p"<?php if (in_array("p", explode(',', $user["permissions"] ?? ''))) { ?> checked="checked"<?php } ?>><span><?php echo $jkl['g37'];?></span>
				            </label>
                            <label>
                                <input class="checkbox" type="checkbox" name="permissions[]" value="s"<?php if (in_array("s", explode(',', $user["permissions"] ?? ''))) { ?> checked="checked"<?php } ?>><span><?php echo $jkl['g66'];?></span>
                            </label>
				            </div>
                        </div>
                        <?php } ?>

                    	<div class="form-group<?php if (isset($errors["e5"]) || isset($errors["e6"])) echo " has-error";?>">
                    		<label class="control-label" for="pass"><?php echo $jkl['g32'];?></label>
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