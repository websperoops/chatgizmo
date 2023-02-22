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
		  if (isset($errors["e6"])) echo $errors["e6"];
          if (isset($errors["e7"])) echo $errors["e7"];?>
	</div>
	<?php } ?>
    <section class="section">
        <form class="jak_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-block sameheight-item">
                    <div class="title-block">
                    	<div class="card-title-block"><h3 class="title"><?php echo $SECTION_TITLE;?></h3></div>

                        	<div class="form-group<?php if (isset($errors["e1"]) || isset($errors["e3"])) echo " has-error";?>">
                        		<label class="control-label" for="username"><?php echo $jkl['g25'];?></label>
                        		<input type="text" name="username" class="form-control underlined" id="username" value="<?php if (isset($_REQUEST["username"])) echo $_REQUEST["username"];?>">
                        	</div>

                        	<div class="form-group<?php if (isset($errors["e"]) || isset($errors["e4"])) echo " has-error";?>">
                        		<label class="control-label" for="email"><?php echo $jkl['g26'];?></label>
                        		<input type="text" name="email" class="form-control underlined" id="email" value="<?php if (isset($_REQUEST["email"])) echo $_REQUEST["email"];?>">
                        	</div>

                            <div class="form-group">
                                <label class="control-label" for="locationid"><?php echo $jkl['g65'];?></label>
                                <select name="locationid" id="locationid" class="form-control">
                                    <?php if (isset($locations) && !empty($locations)) foreach ($locations as $v) { ?>
                                    <option value="<?php echo $v["id"];?>"<?php if (isset($_REQUEST["locationid"]) && $_REQUEST["locationid"] == $v["id"]) echo " selected";?>><?php echo $v["title"];?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group<?php if (isset($errors["e7"])) echo " has-error";?>">
                                <label class="control-label" for="trial"><?php echo $jkl['g64'];?></label>
                                <input type="text" name="trial" class="form-control underlined" id="trial" value="<?php if (isset($_REQUEST["trial"])) echo ($_REQUEST["trial"] != "1980-05-06 00:00:00" ? JAK_base::jakTimesince($_REQUEST["trial"], "d.m.Y", "") : "");?>">
                            </div>

                            <div class="form-group<?php if (isset($errors["e6"])) echo " has-error";?>">
                                <label class="control-label" for="paidtill"><?php echo $jkl['g61'];?></label>
                                <input type="text" name="paidtill" class="form-control underlined" id="paidtill" value="<?php if (isset($_REQUEST["paidtill"])) echo JAK_base::jakTimesince($_REQUEST["paidtill"], "d.m.Y", "");?>">
                            </div>

                        	<div class="form-group<?php if (isset($errors["e4"]) || isset($errors["e5"])) echo " has-error";?>">
                        		<label class="control-label" for="pass"><?php echo $jkl['g2'];?></label>
                        		<input type="password" name="pass" class="form-control underlined" id="pass">
                        	</div>

                        	<div class="form-group">
                        		<label class="control-label" for="passc"><?php echo $jkl['g33'];?></label>
                        		<input type="password" name="passc" class="form-control underlined" id="passc">
                        	</div>

                            <div class="form-group form-check">
                                <input type="checkbox" class="form-check-input" id="send_login" name="send_login" value="1">
                                <label class="form-check-label" for="send_login"><?php echo $jkl['g239'];?></label>
                            </div>

                        	<p><button type="submit" name="save" class="btn btn-success"><i class="fa fa-floppy-o"></i> <?php echo $jkl["g31"];?></button></p>
                        </form>
                    </div>
            	</div>
       		</div>
    	</div>
    </section>
</article>

<?php include "footer.php";?>