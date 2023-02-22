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
    if (isset($errors["e4"])) echo $errors["e4"];?>
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
                            <label class="control-label" for="locationid"><?php echo $jkl['g65'];?></label>
                            <select name="locationid" id="locationid" class="form-control">
                                <?php if (isset($locations) && !empty($locations)) foreach ($locations as $v) { ?>
                                 <option value="<?php echo $v["id"];?>"<?php if (isset($_REQUEST["locationid"]) && $_REQUEST["locationid"] == $v["id"]) echo " selected";?>><?php echo '('.$v["id"].') '.$v["title"];?></option>
                                <?php } ?>
                            </select>
                        </div>

                    	<div class="form-group<?php if (isset($errors["e"])) echo " has-error";?>">
                    		<label class="control-label" for="title"><?php echo $jkl['g42'];?></label>
                    		<input type="text" name="title" class="form-control underlined" id="title" value="<?php if (isset($_REQUEST["title"])) echo $_REQUEST["title"];?>">
                    	</div>

                        <div class="form-group">
                            <label class="control-label"><?php echo $jkl['g160'];?></label>
                            <textarea rows="3" name="desc" class="form-control underlined"><?php if (isset($_REQUEST["desc"])) echo $_REQUEST["desc"];?></textarea>
                        </div>

                        <div class="form-group<?php if (isset($errors["e1"]) || isset($errors["e2"])) echo " has-error";?>">
                            <label class="control-label" for="code"><?php echo $jkl['g188'];?></label>
                            <input type="text" name="code" class="form-control underlined" id="code" value="<?php if (isset($_REQUEST["code"])) echo $_REQUEST["code"];?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="freepackageid"><?php echo $jkl['g194'];?></label>
                            <select name="freepackageid" id="freepackageid" class="form-control">
                                <option value="0"><?php echo $jkl['g195'];?></option>
                                <?php if (isset($packages) && !empty($packages)) foreach ($packages as $p) { ?>
                                 <option value="<?php echo $p["id"];?>"<?php if (isset($_REQUEST["freepackageid"]) && $_REQUEST["freepackageid"] == $p["id"]) echo " selected";?>><?php echo $p["title"].' ('.$p["locationid"].') / '.$p["amount"].' '.$p["currency"];?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="discount"><?php echo $jkl['g189'];?></label>
                            <div class="input-group">
                                <input type="number" min="1" max="100" name="discount" class="form-control underlined" id="discount" value="<?php echo (isset($_REQUEST["discount"]) ? $_REQUEST["discount"] : 10);?>">
                                <span class="input-group-addon" id="basic-addon2">%</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="used"><?php echo $jkl['g190'];?></label>
                            <input type="number" min="0" name="used" class="form-control underlined" id="used" value="<?php echo (isset($_REQUEST["used"]) ? $_REQUEST["used"] : 0);?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="total"><?php echo $jkl['g191'];?></label>
                            <input type="number" min="1" name="total" class="form-control underlined" id="total" value="<?php echo (isset($_REQUEST["total"]) ? $_REQUEST["total"] : 100);?>">
                        </div>

                        <div class="form-group<?php if (isset($errors["e3"])) echo " has-error";?>">
                            <label class="control-label" for="validfrom"><?php echo $jkl['g193'];?></label>
                            <input type="text" name="validfrom" class="form-control underlined" id="validfrom">
                        </div>

                        <div class="form-group<?php if (isset($errors["e4"])) echo " has-error";?>">
                            <label class="control-label" for="validtill"><?php echo $jkl['g152'];?></label>
                            <input type="text" name="validtill" class="form-control underlined" id="validtill">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="products"><?php echo $jkl['g196'];?></label>
                            <select name="products[]" id="products" class="form-control" multiple>
                                <option value="0"><?php echo $jkl['g136'];?></option>
                                <?php if (isset($packages) && !empty($packages)) foreach ($packages as $p) { ?>
                                 <option value="<?php echo $p["id"];?>"<?php if (isset($_REQUEST["products"]) && $_REQUEST["products"] == $p["id"]) echo " selected";?>><?php echo $p["title"].' ('.$p["locationid"].') / '.$p["amount"].' '.$p["currency"];?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="control-label"><?php echo $jkl['g117'];?></label>
                            <div>
                                <label>
                                    <input class="radio" type="radio" name="active" value="1" checked><span><?php echo $jkl['g77'];?></span>
                                </label>
                                <label>
                                    <input class="radio" type="radio" name="active" value="0"><span><?php echo $jkl['g78'];?></span>
                                </label>
                            </div>
                        </div>

                    	<p><button type="submit" name="save" class="btn btn-success"><i class="fa fa-floppy-o"></i> <?php echo $jkl["g31"];?></button></p>
                    </form>
            	</div>
       		</div>
    	</div>
    </section>
</article>

<?php include "footer.php";?>