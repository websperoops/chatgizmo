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
          if (isset($errors["e2"])) echo $errors["e2"];?>
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
                    		<label class="control-label" for="title"><?php echo $jkl['g42'];?></label>
                    		<input type="text" name="title" class="form-control underlined" id="title" value="<?php if (isset($_REQUEST["title"])) echo $_REQUEST["title"];?>">
                    	</div>

                        <div class="form-group">
                            <label class="control-label"><?php echo $jkl['g227'];?></label>
                            <div>
                                <label>
                                    <input class="radio" type="radio" name="lc3hd3" value="1"><span><?php echo $jkl['g77'];?></span>
                                </label>
                                <label>
                                    <input class="radio" type="radio" name="lc3hd3" value="0" checked><span><?php echo $jkl['g78'];?></span>
                                </label>
                            </div>
                        </div>

                    	<div class="form-group<?php if (isset($errors["e2"])) echo " has-error";?>">
                    		<label class="control-label" for="url"><?php echo $jkl['g43'];?></label>
                    		<input type="text" name="url" class="form-control underlined" id="url" value="<?php if (isset($_REQUEST["url"])) echo $_REQUEST["url"];?>">
                    	</div>

                    	<div class="form-group">
                    		<label class="control-label" for="db_host"><?php echo $jkl['g47'];?></label>
                    		<input type="text" name="db_host" class="form-control underlined" id="db_host" value="<?php if (isset($_REQUEST["db_host"])) echo $_REQUEST["db_host"];?>">
                    	</div>

                        <div class="form-group">
                            <label class="control-label" for="db_type"><?php echo $jkl['g48'];?></label>
                            <select name="db_type" class="form-control">
                                <option value="mysql"<?php if (isset($_REQUEST["db_type"]) && $_REQUEST["db_type"] == "mysql") echo " selected";?>>MySQL</option>
                                <option value="mariadb"<?php if (isset($_REQUEST["db_type"]) && $_REQUEST["db_type"] == "mariadb") echo " selected";?>>MariaDB</option>
                                <option value="sqlite"<?php if (isset($_REQUEST["db_type"]) && $_REQUEST["db_type"] == "sqlite") echo " selected";?>>SQLite</option>
                            </select>
                        </div>

                        <div class="form-group<?php if (isset($errors["e2"])) echo " has-error";?>">
                            <label class="control-label" for="db_port"><?php echo $jkl['g53'];?></label>
                            <input type="text" name="db_port" class="form-control underlined" id="db_port" value="<?php if (isset($_REQUEST["db_port"])) echo $_REQUEST["db_port"];?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="db_user"><?php echo $jkl['g49'];?></label>
                            <input type="text" name="db_user" class="form-control underlined" id="db_user" value="<?php if (isset($_REQUEST["db_user"])) echo $_REQUEST["db_user"];?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="db_pass"><?php echo $jkl['g50'];?></label>
                            <input type="text" name="db_pass" class="form-control underlined" id="db_pass" value="<?php if (isset($_REQUEST["db_pass"])) echo $_REQUEST["db_pass"];?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="db_name"><?php echo $jkl['g51'];?></label>
                            <input type="text" name="db_name" class="form-control underlined" id="db_name" value="<?php if (isset($_REQUEST["db_name"])) echo $_REQUEST["db_name"];?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="db_prefix"><?php echo $jkl['g52'];?></label>
                            <input type="text" name="db_prefix" class="form-control underlined" id="db_prefix" value="<?php if (isset($_REQUEST["db_prefix"])) echo $_REQUEST["db_prefix"];?>">
                        </div>

                    	<p><button type="submit" name="save" class="btn btn-success"><i class="fa fa-floppy-o"></i> <?php echo $jkl["g31"];?></button></p>
                    </form>
            	</div>
       		</div>
    	</div>
    </section>
</article>

<?php include "footer.php";?>