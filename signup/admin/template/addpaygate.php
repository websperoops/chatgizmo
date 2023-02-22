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

                        <div class="form-group">
                            <label class="control-label" for="locationid"><?php echo $jkl['g65'];?></label>
                            <select name="locationid" id="locationid" class="form-control">
                                <?php if (isset($locations) && !empty($locations)) foreach ($locations as $v) { ?>
                                 <option value="<?php echo $v["id"];?>"<?php if (isset($_REQUEST["locationid"]) && $_REQUEST["locationid"] == $v["id"]) echo " selected";?>><?php echo $v["title"];?></option>
                                <?php } ?>
                            </select>
                        </div>

                    	<div class="form-group<?php if (isset($errors["e"])) echo " has-error";?>">
                    		<label class="control-label" for="title"><?php echo $jkl['g42'];?></label>
                    		<input type="text" name="title" class="form-control underlined" id="title" value="<?php if (isset($_REQUEST["title"])) echo $_REQUEST["title"];?>">
                    	</div>

                        <div class="form-group">
                            <label class="control-label" for="currency"><?php echo $jkl['g316'];?></label>
                            <input type="text" name="currency" class="form-control underlined" maxlength="3" id="title" value="<?php if (isset($_REQUEST["currency"])) echo $_REQUEST["currency"];?>">
                            <p class="text-muted"><?php echo $jkl['g317'];?></p>
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="paygateid"><?php echo $jkl['g313'];?></label>
                            <select name="paygateid" id="paygateid" class="form-control">
                                 <option value="stripe"<?php if (isset($_REQUEST["paygateid"]) && $_REQUEST["paygateid"] == 'stripe') echo " selected";?>>Stripe</option>
                                 <option value="paypal"<?php if (isset($_REQUEST["paygateid"]) && $_REQUEST["paygateid"] == 'paypal') echo " selected";?>>Paypal</option>
                                 <!-- <option value="verifone"<?php if (isset($_REQUEST["paygateid"]) && $_REQUEST["paygateid"] == 'verifone') echo " selected";?>>2checkout (Verifone)</option> -->
                                 <option value="yoomoney"<?php if (isset($_REQUEST["paygateid"]) && $_REQUEST["paygateid"] == 'yoomoney') echo " selected";?>>YooMoney</option>
                                 <!-- <option value="authorize"<?php if (isset($_REQUEST["paygateid"]) && $_REQUEST["paygateid"] == 'authorize') echo " selected";?>>Authorize.net</option> -->
                                 <option value="paystack"<?php if (isset($_REQUEST["paygateid"]) && $_REQUEST["paygateid"] == 'paystack') echo " selected";?>>Paystack</option>
                                 <option value="bank"<?php if (isset($_REQUEST["paygateid"]) && $_REQUEST["paygateid"] == 'bank') echo " selected";?>><?php echo $jkl['g331'];?></option>
                            </select>
                        </div>

                        <div class="form-group<?php if (isset($errors["e1"])) echo " has-error";?>">
                            <label class="control-label" for="secretkey_one"><?php echo $jkl['g314'];?></label>
                            <input type="text" name="secretkey_one" class="form-control underlined" id="secretkey_one" value="<?php if (isset($_REQUEST["secretkey_one"])) echo $_REQUEST["secretkey_one"];?>">
                        </div>

                        <div class="form-group<?php if (isset($errors["e2"])) echo " has-error";?>">
                            <label class="control-label" for="secretkey_two"><?php echo $jkl['g315'];?></label>
                            <input type="text" name="secretkey_two" class="form-control underlined" id="secretkey_two" value="<?php if (isset($_REQUEST["secretkey_two"])) echo $_REQUEST["secretkey_two"];?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="emailkey"><?php echo $jkl['g320'];?></label>
                            <input type="text" name="emailkey" class="form-control underlined" id="emailkey" value="<?php if (isset($_REQUEST["emailkey"])) echo $_REQUEST["emailkey"];?>">
                        </div>

                        <div class="form-group<?php if (isset($errors["e3"])) echo " has-error";?>">
                            <label class="control-label" for="bank_info"><?php echo $jkl["g331"];?></label>
                            <textarea name="bank_info" class="form-control"><?php if (isset($_REQUEST["bank_info"])) echo $_REQUEST["bank_info"];?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="control-label"><?php echo $jkl['g307'];?></label>
                            <div>
                                <label>
                                    <input class="radio" type="radio" name="sandbox" value="1"><span><?php echo $jkl['g77'];?></span>
                                </label>
                                <label>
                                    <input class="radio" type="radio" name="sandbox" value="0" checked><span><?php echo $jkl['g78'];?></span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label"><?php echo $jkl['g117'];?></label>
                            <div>
                                <label>
                                    <input class="radio" type="radio" name="active" value="1"><span><?php echo $jkl['g77'];?></span>
                                </label>
                                <label>
                                    <input class="radio" type="radio" name="active" value="0" checked><span><?php echo $jkl['g78'];?></span>
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