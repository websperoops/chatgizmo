<?php include "header.php";?>

<article class="content forms-page">
    <div class="title-block">
        <h1 class="title"><?php echo $SECTION_TITLE;?></h1>
        <p class="title-description"><?php echo $SECTION_DESC;?></p>
    </div>
    <?php if ($errors) { ?>
	<div class="alert alert-danger">
	<?php if (isset($errors["e"])) echo $errors["e"];?>
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
                                 <option value="<?php echo $v["id"];?>"<?php if ($package["locationid"] == $v["id"]) echo " selected";?>><?php echo $v["title"];?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="paygate"><?php echo $jkl['g306'];?></label>
                            <select name="paygate[]" id="paygate" class="form-control" multiple>
                                <?php if (isset($paygate) && !empty($paygate)) foreach ($paygate as $p) { ?>
                                 <option value="<?php echo $p["id"];?>"<?php if (isset($paysel) && !empty($paysel)) foreach ($paysel as $ps) { if ($ps == $p["id"]) echo " selected";}?>><?php echo $p["title"];?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group<?php if (isset($errors["e"])) echo " has-error";?>">
                            <label class="control-label" for="title"><?php echo $jkl['g42'];?></label>
                            <input type="text" name="title" class="form-control underlined" id="title" value="<?php echo $package["title"];?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label"><?php echo $jkl['g160'];?></label>
                            <textarea rows="3" name="desc" class="form-control underlined"><?php echo $package["description"];?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="previmg"><?php echo $jkl['g161'];?></label>
                            <input type="text" name="previmg" class="form-control underlined" id="previmg" value="<?php echo $package["previmg"];?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="amount"><?php echo $jkl['g121'];?></label>
                            <div class="input-group">
                                <input type="number" min="1" name="amount" class="form-control underlined" id="amount" value="<?php echo $package["amount"];?>">
                                <span class="input-group-addon" id="basic-addon2"><?php echo $sett["currency"];?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="validfor"><?php echo $jkl['g174'];?></label>
                            <select name="validfor" id="validfor" class="form-control">
                                 <option value="7"<?php if ($package["validfor"] == 7) echo " selected";?>><?php echo $jkl['g175'];?></option>
                                 <option value="14"<?php if ($package["validfor"] == 14) echo " selected";?>><?php echo $jkl['g176'];?></option>
                                 <option value="30"<?php if ($package["validfor"] == 30) echo " selected";?>><?php echo $jkl['g177'];?></option>
                                 <option value="90"<?php if ($package["validfor"] == 90) echo " selected";?>><?php echo $jkl['g178'];?></option>
                                 <option value="180"<?php if ($package["validfor"] == 180) echo " selected";?>><?php echo $jkl['g179'];?></option>
                                 <option value="365"<?php if ($package["validfor"] == 365) echo " selected";?>><?php echo $jkl['g180'];?></option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="chatwidgets"><?php echo $jkl['g256'];?></label>
                            <input type="number" min="1" name="chatwidgets" class="form-control underlined" id="chatwidgets" value="<?php echo $package["chatwidgets"];?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="groupchats"><?php echo $jkl['g257'];?></label>
                            <input type="number" min="0" name="groupchats" class="form-control underlined" id="groupchats" value="<?php echo $package["groupchats"];?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label"><?php echo $jkl['g258'];?></label>
                            <div>
                                <label>
                                    <input class="radio" type="radio" name="operatorchat" value="1"<?php if ($package["operatorchat"] == 1) echo " checked";?>><span><?php echo $jkl['g77'];?></span>
                                </label>
                                <label>
                                    <input class="radio" type="radio" name="operatorchat" value="0"<?php if ($package["operatorchat"] == 0) echo " checked";?>><span><?php echo $jkl['g78'];?></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="operators"><?php echo $jkl['g113'];?></label>
                            <input type="number" min="1" name="operators" class="form-control underlined" id="operators" value="<?php echo $package["operators"];?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="departments"><?php echo $jkl['g114'];?></label>
                            <input type="number" min="0" name="departments" class="form-control underlined" id="departments" value="<?php echo $package["departments"];?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="activechats"><?php echo $jkl['g163'];?></label>
                            <input type="number" min="1" name="activechats" class="form-control underlined" id="activechats" value="<?php echo $package["activechats"];?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="chathistory"><?php echo $jkl['g197'];?></label>
                            <input type="number" min="1" name="chathistory" class="form-control underlined" id="chathistory" value="<?php echo $package["chathistory"];?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label"><?php echo $jkl['g162'];?></label>
                            <div>
                                <label>
                                    <input class="radio" type="radio" name="files" value="1"<?php if ($package["files"] == 1) echo " checked";?>><span><?php echo $jkl['g77'];?></span>
                                </label>
                                <label>
                                    <input class="radio" type="radio" name="files" value="0"<?php if ($package["files"] == 0) echo " checked";?>><span><?php echo $jkl['g78'];?></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label"><?php echo $jkl['g172'];?></label>
                            <div>
                                <label>
                                    <input class="radio" type="radio" name="copyfree" value="1"<?php if ($package["copyfree"] == 1) echo " checked";?>><span><?php echo $jkl['g77'];?></span>
                                </label>
                                <label>
                                    <input class="radio" type="radio" name="copyfree" value="0"<?php if ($package["copyfree"] == 0) echo " checked";?>><span><?php echo $jkl['g78'];?></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label"><?php echo $jkl['g164'];?></label>
                            <div>
                                <label>
                                    <input class="radio" type="radio" name="islc3" value="1"<?php if ($package["islc3"] == 1) echo " checked";?>><span><?php echo $jkl['g77'];?></span>
                                </label>
                                <label>
                                    <input class="radio" type="radio" name="islc3" value="0"<?php if ($package["islc3"] == 0) echo " checked";?>><span><?php echo $jkl['g78'];?></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label"><?php echo $jkl['g165'];?></label>
                            <div>
                                <label>
                                    <input class="radio" type="radio" name="ishd3" value="1"<?php if ($package["ishd3"] == 1) echo " checked";?>><span><?php echo $jkl['g77'];?></span>
                                </label>
                                <label>
                                    <input class="radio" type="radio" name="ishd3" value="0"<?php if ($package["ishd3"] == 0) echo " checked";?>><span><?php echo $jkl['g78'];?></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label"><?php echo $jkl['g181'];?></label>
                            <div>
                                <label>
                                    <input class="radio" type="radio" name="multipleuse" value="1"<?php if ($package["multipleuse"] == 1) echo " checked";?>><span><?php echo $jkl['g77'];?></span>
                                </label>
                                <label>
                                    <input class="radio" type="radio" name="multipleuse" value="0"<?php if ($package["multipleuse"] == 0) echo " checked";?>><span><?php echo $jkl['g78'];?></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label"><?php echo $jkl['g206'];?></label>
                            <div>
                                <label>
                                    <input class="radio" type="radio" name="isfree" value="1"<?php if ($package["isfree"] == 1) echo " checked";?>><span><?php echo $jkl['g77'];?></span>
                                </label>
                                <label>
                                    <input class="radio" type="radio" name="isfree" value="0"<?php if ($package["isfree"] == 0) echo " checked";?>><span><?php echo $jkl['g78'];?></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label"><?php echo $jkl['g117'];?></label>
                            <div>
                                <label>
                                    <input class="radio" type="radio" name="active" value="1"<?php if ($package["active"] == 1) echo " checked";?>><span><?php echo $jkl['g77'];?></span>
                                </label>
                                <label>
                                    <input class="radio" type="radio" name="active" value="0"<?php if ($package["active"] == 0) echo " checked";?>><span><?php echo $jkl['g78'];?></span>
                                </label>
                                <label>
                                    <input class="radio" type="radio" name="active" value="2"<?php if ($package["active"] == 2) echo " checked";?>><span><?php echo $jkl['g302'];?></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="supackage" name="supackage" value="1"<?php if ($package["supackage"] == 1) echo " checked";?>>
                            <label class="form-check-label" for="supackage"><?php echo $jkl['g243'];?></label>
                        </div>

                    	<p><button type="submit" name="save" class="btn btn-success"><i class="fa fa-floppy-o"></i> <?php echo $jkl["g31"];?></button></p>
                    </form>
            	</div>
       		</div>
    	</div>
    </section>
</article>

<?php include "footer.php";?>