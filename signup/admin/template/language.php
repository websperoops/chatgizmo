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

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g92'];?></label>
                                    <textarea rows="3" name="welcomemsg" class="form-control underlined"><?php echo $sett["welcomemsg"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="newtickettitle"><?php echo $jkl['g143'];?></label>
                                    <input type="text" name="newtickettitle" class="form-control underlined" id="newtickettitle" value="<?php echo $sett["newtickettitle"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g144'];?></label>
                                    <textarea rows="3" name="newticketmsg" class="form-control underlined"><?php echo $sett["newticketmsg"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g171'];?></label>
                                    <textarea rows="3" name="welcomedash" class="form-control underlined"><?php echo $sett["welcomedash"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g209'];?></label>
                                    <textarea rows="5" name="appboxes" class="form-control underlined"><?php echo $sett["appboxes"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g210'];?></label>
                                    <textarea rows="3" name="expiredmsgdash" class="form-control underlined"><?php echo $sett["expiredmsgdash"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="trialdate"><?php echo $jkl['g211'];?></label>
                                    <input type="text" name="trialdate" class="form-control underlined" id="trialdate" value="<?php echo $sett["trialdate"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g216'];?></label>
                                    <textarea rows="3" name="heldashpmsg" class="form-control underlined"><?php echo $sett["heldashpmsg"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g215'];?></label>
                                    <textarea rows="3" name="businesshours" class="form-control underlined"><?php echo $sett["businesshours"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g212'];?></label>
                                    <textarea rows="3" name="addopsmsg" class="form-control underlined"><?php echo $sett["addopsmsg"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="moreopmsg"><?php echo $jkl['g213'];?></label>
                                    <input type="text" name="moreopmsg" class="form-control underlined" id="moreopmsg" value="<?php echo $sett["moreopmsg"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g214'];?></label>
                                    <textarea rows="3" name="opwarnmsg" class="form-control underlined"><?php echo $sett["opwarnmsg"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="purchasedtitle"><?php echo $jkl['g217'];?></label>
                                    <input type="text" name="purchasedtitle" class="form-control underlined" id="purchasedtitle" value="<?php echo $sett["purchasedtitle"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="packageseltitle"><?php echo $jkl['g218'];?></label>
                                    <input type="text" name="packageseltitle" class="form-control underlined" id="packageseltitle" value="<?php echo $sett["packageseltitle"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="addoptitle"><?php echo $jkl['g219'];?></label>
                                    <input type="text" name="addoptitle" class="form-control underlined" id="addoptitle" value="<?php echo $sett["addoptitle"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="invoicetitle"><?php echo $jkl['g200'];?></label>
                                    <input type="text" name="invoicetitle" class="form-control underlined" id="invoicetitle" value="<?php echo $sett["invoicetitle"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g201'];?></label>
                                    <textarea rows="3" name="invoicecontent" class="form-control underlined"><?php echo $sett["invoicecontent"];?></textarea>
                                </div>

                            </div>
                            <div class="col-md-6">

                                <div class="form-group">
                                    <label class="control-label" for="subsctitle"><?php echo $jkl['g202'];?></label>
                                    <input type="text" name="subsctitle" class="form-control underlined" id="subsctitle" value="<?php echo $sett["subsctitle"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g203'];?></label>
                                    <textarea rows="3" name="subsctext" class="form-control underlined"><?php echo $sett["subsctext"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="failedtitle"><?php echo $jkl['g204'];?></label>
                                    <input type="text" name="failedtitle" class="form-control underlined" id="failedtitle" value="<?php echo $sett["failedtitle"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g205'];?></label>
                                    <textarea rows="3" name="failedtext" class="form-control underlined"><?php echo $sett["failedtext"];?></textarea>
                                </div>

                                <div class="form-group<?php if (isset($errors["e2"])) echo " has-error";?>">
                                    <label class="control-label" for="emailtitle"><?php echo $jkl['g82'];?></label>
                                    <input type="text" name="emailtitle" class="form-control underlined" id="emailtitle" value="<?php echo $sett["emailtitle"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="webhello"><?php echo $jkl['g84'];?></label>
                                    <input type="text" name="webhello" class="form-control underlined" id="webhello" value="<?php echo $sett["webhello"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g85'];?></label>
                                    <textarea rows="3" name="emailsignup" class="form-control underlined"><?php echo $sett["emailsignup"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g86'];?></label>
                                    <textarea rows="3" name="emailwelcome" class="form-control underlined"><?php echo $sett["emailwelcome"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g95'];?></label>
                                    <textarea rows="3" name="emailpass" class="form-control underlined"><?php echo $sett["emailpass"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g87'];?></label>
                                    <textarea rows="3" name="emailpaid" class="form-control underlined"><?php echo $sett["emailpaid"];?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g240'];?></label>
                                    <textarea rows="3" name="newclient" class="form-control underlined"><?php echo $sett["newclient"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g88'];?></label>
                                    <textarea rows="3" name="emailpaidlc3" class="form-control underlined"><?php echo $sett["emailpaidlc3"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g89'];?></label>
                                    <textarea rows="3" name="lc3confirm" class="form-control underlined"><?php echo $sett["lc3confirm"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g229'];?></label>
                                    <textarea rows="3" name="lc3update" class="form-control underlined"><?php echo $sett["lc3update"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g156'];?></label>
                                    <textarea rows="3" name="emailpaidhd3" class="form-control underlined"><?php echo $sett["emailpaidhd3"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g157'];?></label>
                                    <textarea rows="3" name="hd3confirm" class="form-control underlined"><?php echo $sett["hd3confirm"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g230'];?></label>
                                    <textarea rows="3" name="hd3update" class="form-control underlined"><?php echo $sett["hd3update"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g90'];?></label>
                                    <textarea rows="3" name="emailmoved" class="form-control underlined"><?php echo $sett["emailmoved"];?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="control-label"><?php echo $jkl['g91'];?></label>
                                    <textarea rows="3" name="emailexpire" class="form-control underlined"><?php echo $sett["emailexpire"];?></textarea>
                                </div>

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