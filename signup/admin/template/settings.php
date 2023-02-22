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
    <?php } if ($success) { ?>
    <div class="alert alert-success">
        <?php if (isset($success["e"])) echo $success["e"];?>
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

                        <div class="row">
                            <div class="col-md-6">

                                <div class="form-group<?php if (isset($errors["e"])) echo " has-error";?>">
                                    <label class="control-label" for="title"><?php echo $jkl['g42'];?></label>
                                    <input type="text" name="title" class="form-control underlined" id="title" value="<?php echo $sett["title"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="webaddress"><?php echo $jkl['g83'];?></label>
                                    <input type="text" name="webaddress" class="form-control underlined" id="webaddress" value="<?php echo $sett["webaddress"];?>">
                                </div>

                                <div class="form-group<?php if (isset($errors["e1"])) echo " has-error";?>">
                                    <label class="control-label" for="emailaddress"><?php echo $jkl['g17'];?></label>
                                    <input type="text" name="emailaddress" class="form-control underlined" id="emailaddress" value="<?php echo $sett["emailaddress"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="dateformat"><?php echo $jkl['g280'];?></label>
                                    <select name="dateformat" class="form-control">
                                        <option value="d.m.Y"<?php if ($sett["dateformat"] == "d.m.Y") echo ' selected';?>><?php echo date("d.m.Y");?></option>
                                        <option value="m.d.y"<?php if ($sett["dateformat"] == "m.d.y") echo ' selected';?>><?php echo date("m.d.y");?></option>
                                        <option value="F j, Y"<?php if ($sett["dateformat"] == "F j, Y") echo ' selected';?>><?php echo date("F j, Y");?></option>
                                        <option value="Y-m-d"<?php if ($sett["dateformat"] == "Y-m-d") echo ' selected';?>><?php echo date("Y-m-d");?></option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="timeformat"><?php echo $jkl['g281'];?></label>
                                    <select name="timeformat" class="form-control">
                                        <option value="g:i a"<?php if ($sett["timeformat"] == "g:i a") echo ' selected';?>><?php echo date("g:i a");?></option>
                                        <option value="H:i:s"<?php if ($sett["timeformat"] == "H:i:s") echo ' selected';?>><?php echo date("H:i:s");?></option>
                                        <option value="H:i"<?php if ($sett["timeformat"] == "H:i") echo ' selected';?>><?php echo date("H:i");?></option>
                                    </select>
                                </div>

                            </div>
                            <div class="col-md-6">

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g70'];?></label>
                                    <div>
                                        <label>
                                            <input class="radio" type="radio" name="smtp" value="1"<?php if ($sett["smtp"] == 1) echo " checked";?>><span><?php echo $jkl['g71'];?></span>
                                        </label>
                                        <label>
                                            <input class="radio" type="radio" name="smtp" value="0"<?php if ($sett["smtp"] == 0) echo " checked";?>><span><?php echo $jkl['g72'];?></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group<?php if (isset($errors["e2"])) echo " has-error";?>">
                                    <label class="control-label" for="smtphost"><?php echo $jkl['g73'];?></label>
                                    <input type="text" name="smtphost" class="form-control underlined" id="db_port" value="<?php echo $sett["smtphost"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="smtpauth"><?php echo $jkl['g74'];?></label>
                                    <input type="text" name="smtpauth" class="form-control underlined" id="smtpauth" value="<?php echo $sett["smtpauth"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="smtpprefix"><?php echo $jkl['g75'];?></label>
                                    <input type="text" name="smtpprefix" class="form-control underlined" id="smtpprefix" value="<?php echo $sett["smtpprefix"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g76'];?></label>
                                    <div>
                                        <label>
                                            <input class="radio" type="radio" name="smtpalive" value="1"<?php if ($sett["smtpalive"] == 1) echo " checked";?>><span><?php echo $jkl['g77'];?></span>
                                        </label>
                                        <label>
                                            <input class="radio" type="radio" name="smtpalive" value="0"<?php if ($sett["smtpalive"] == 0) echo " checked";?>><span><?php echo $jkl['g78'];?></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="smtpport"><?php echo $jkl['g79'];?></label>
                                    <input type="text" name="smtpport" class="form-control underlined" id="smtpport" value="<?php echo $sett["smtpport"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="smtpusername"><?php echo $jkl['g80'];?></label>
                                    <input type="text" name="smtpusername" class="form-control underlined" id="smtpusername" value="<?php echo $sett["smtpusername"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="smtppass"><?php echo $jkl['g81'];?></label>
                                    <input type="password" name="smtppass" class="form-control underlined" id="smtppass" value="<?php echo $sett["smtppass"];?>">
                                </div>

                            </div>
                        </div>

                        <p><button type="submit" name="save" class="btn btn-success"><i class="fa fa-floppy-o"></i> <?php echo $jkl["g31"];?></button> <button type="submit" name="testMail" class="btn btn-primary" id="sendTM"><i id="loader" class="fa fa-spinner fa-pulse"></i> <i class="fa fa-envelope-o"></i> <?php echo $jkl["g93"];?></button></p>
                    </form>
                </div>
            </div>
        </div>
    </section>
</article>

<?php include "footer.php";?>