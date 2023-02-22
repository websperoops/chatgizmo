<?php include "header.php";?>

<article class="content responsive-tables-page">
    <div class="title-block">
        <h1 class="title"><?php echo $SECTION_TITLE;?></h1>
        <p class="title-description"><?php echo $SECTION_DESC;?></p>
    </div>
    <?php if (isset($page1) && $page1 != 'r') { ?>
    <?php if ($errors) { ?>
    <div class="alert alert-danger">
    <?php if (isset($errors["e"])) echo $errors["e"];
          if (isset($errors["e1"])) echo $errors["e1"];?>
    </div>
    <?php } if ($success) { ?>
    <div class="alert alert-success">
        <?php if (isset($success["e"])) echo $success["e"];?>
    </div>
    <?php } ?>
    <section class="section">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-block">
                        <div class="card-title-block"><h3 class="title"><?php echo $jkl['g131'];?></h3></div>
                        <section class="example">
                            <form class="jak_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

                                <?php if (isset($page1) && $page1 != 'a') { ?>
                                <div class="form-group">
                                    <label class="control-label" for="userid"><?php echo $jkl['g133'];?></label>
                                    <select name="userid[]" id="userid[]" class="form-control" multiple>
                                        <option value="0"<?php if (isset($_REQUEST["userid"]) && $_REQUEST["userid"] == 0) echo " selected";?>><?php echo $jkl['g136'];?></option> 
                                        <?php if (isset($clients) && !empty($clients)) foreach ($clients as $v) { ?>
                                        <option value="<?php echo $v["id"];?>"<?php if (isset($_REQUEST["userid"]) && $_REQUEST["userid"] == $v["id"]) echo " selected";?>><?php echo $v["username"].' ('.$v["email"].')';?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <?php } else { ?>
                                <div class="form-group">
                                    <label class="control-label" for="userid"><?php echo $jkl['g133'];?></label>
                                    <input type="text" name="userid" class="form-control" value="<?php echo $usr["username"].' ('.$usr["email"].')';?>" disabled>
                                </div>
                                <?php } ?>

                                <div class="form-group<?php if (isset($errors["e"])) echo " has-error";?>">
                                    <label class="control-label" for="subject"><?php echo $jkl['g134'];?></label>
                                    <input type="text" name="subject" class="form-control underlined" id="subject" value="<?php if (isset($_REQUEST["subject"])) echo $_REQUEST["subject"]; if (isset($ticket["subject"])) echo 'Re: '.$ticket["subject"];?>">
                                </div>

                                <div class="form-group<?php if (isset($errors["e1"])) echo " has-error";?>">
                                    <label class="control-label" for="content"><?php echo $jkl['g135'];?></label>
                                    <textarea rows="5" name="content" class="form-control underlined"><?php if (isset($_REQUEST["content"])) echo $_REQUEST["content"]; if (isset($ticket["content"])) echo "\n\n------------------------ ".JAK_base::jakTimesince($ticket["sent"], $sett["dateformat"], $sett["timeformat"])." ------------------------\n\n".$ticket["content"];?></textarea>
                                    <small id="contentHelp" class="form-text text-muted"><?php echo $jkl['g137'];?></small>
                                </div>

                                <p><button type="submit" name="save" class="btn btn-success"><i class="fa fa-floppy-o"></i> <?php echo $jkl["g31"];?></button></p>

                                <input type="hidden" name="ticketid" value="<?php echo (isset($page2) ? $page2 : 0);?>">

                            </form>
                        </section>
                    </div>
                </div>
            </div>
        </div>
        <?php } if (isset($page1) && $page1 == "r") { ?>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-block">
                        <div class="card-title-block"><h3 class="title"><?php echo $ticket["subject"];?></h3></div>
                        <section class="example">
                            <p><?php echo nl2br($ticket["content"], false);?></p>
                        </section>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-block">
                        <div class="card-title-block"><h3 class="title"><?php echo $SECTION_TITLE;?></h3>
                    </div>
                    <section class="example">
                        <div class="table-flip-scroll">
                            <table class="table table-striped table-bordered table-responsive table-hover flip-content w-100 d-block d-md-table" id="dynamic-data">
                                <thead class="flip-header">
                                    <tr>
                                        <th><?php echo $jkl['g56'];?></th>
                                        <th><?php echo $jkl['g134'];?></th>
                                        <th><?php echo $jkl['g25'];?></th>
                                        <th><?php echo $jkl['g140'];?></th>
                                        <th><?php echo $jkl['g138'];?></th>
                                        <th><?php echo $jkl['g141'];?></th>
                                        <th><i class="fa fa-edit"></i></th>
                                        <th><i class="fa fa-file-text-o"></i></th>
                                        <th><i class="fa fa-trash-o"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </section>
            	</div>
       		</div>
    	</div>
    </section>
</article>

<?php include "footer.php";?>