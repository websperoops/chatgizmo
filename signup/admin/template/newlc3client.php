<?php include "header.php";?>

<article class="content forms-page">
    <div class="title-block">
        <h1 class="title"><?php echo $SECTION_TITLE;?></h1>
        <p class="title-description"><?php echo $SECTION_DESC;?></p>
    </div>
    <?php if ($errors) { ?>
	<div class="alert alert-danger">
	<?php if (isset($errors["e"])) echo $errors["e"];
		  if (isset($errors["e1"])) echo $errors["e1"];?>
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
                    	<div class="form-group">
                            <label class="control-label" for="userid"><?php echo $jkl['g151'];?></label>
                            <select name="userid" id="userid" class="form-control">
                                <?php if (isset($clients) && !empty($clients)) foreach ($clients as $v) { ?>
                                <option value="<?php echo $v["id"];?>"<?php if (isset($_REQUEST["userid"]) && $_REQUEST["userid"] == $v["id"]) echo " selected";?>><?php echo $v["username"].' ('.$v["email"].')';?></option>
                                        <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                                <label class="control-label" for="locationid"><?php echo $jkl['g65'];?></label>
                                <select name="locationid" id="locationid" class="form-control">
                                    <option value="0"><?php echo $jkl['g236'];?></option>
                                    <?php if (isset($locations) && !empty($locations)) foreach ($locations as $v) { ?>
                                    <option value="<?php echo $v["id"];?>"<?php if (isset($_REQUEST["locationid"]) && $_REQUEST["locationid"] == $v["id"]) echo " selected";?>><?php echo $v["title"];?></option>
                                    <?php } ?>
                                </select>
                            </div>

                        <div class="form-group<?php if (isset($errors["e1"])) echo " has-error";?>">
                            <label class="control-label" for="validtill"><?php echo $jkl['g152'];?></label>
                            <input type="text" name="validtill" class="form-control underlined" id="validtill">
                        </div>

                    	<p><button type="submit" name="save" class="btn btn-success"><i class="fa fa-floppy-o"></i> <?php echo $jkl["g31"];?></button></p>
                    </form>
            	</div>
       		</div>
    	</div>
    </section>
</article>

<?php include "footer.php";?>