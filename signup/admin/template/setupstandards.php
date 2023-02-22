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
          if (isset($errors["e7"])) echo $errors["e7"];
          if (isset($errors["e8"])) echo $errors["e8"];
          if (isset($errors["e9"])) echo $errors["e9"];?>
	</div>
	<?php } ?>
    <section class="section">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-block sameheight-item">
                    <div class="title-block">
                    	<h3 class="title"><?php echo $SECTION_TITLE;?></h3>
                    </div>
                    <nav>
                      <div class="nav nav-tabs" id="nav-tab">
                        <a class="nav-item nav-link active" id="nav-settings-tab" data-toggle="tab" href="#nav-settings" aria-controls="nav-settings" aria-selected="true"><?php echo $jkl['g267'];?></a>
                        <a class="nav-item nav-link" id="nav-department-tab" data-toggle="tab" href="#nav-department" aria-controls="nav-department" aria-selected="false"><?php echo $jkl['g268'];?></a>
                        <a class="nav-item nav-link" id="nav-answers-tab" data-toggle="tab" href="#nav-answers" aria-controls="nav-answers" aria-selected="false"><?php echo $jkl['g276'];?></a>
                        <a class="nav-item nav-link" id="nav-widget-tab" data-toggle="tab" href="#nav-widget" aria-controls="nav-widget" aria-selected="false"><?php echo $jkl['g297'];?></a>
                      </div>
                    </nav>

                    <form class="jak_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">

                        <div class="tab-content mt-3" id="nav-tabContent">
                          <div class="tab-pane fade show active" id="nav-settings" aria-labelledby="nav-settings-tab">

                            <div class="form-group<?php if (isset($errors["e"])) echo " has-error";?>">
                                <label class="control-label" for="title"><?php echo $jkl['g42'];?></label>
                                <input type="text" name="title" class="form-control underlined" id="title" value="<?php echo $opsett["title"];?>">
                            </div>

                            <div class="form-group<?php if (isset($errors["e2"])) echo " has-error";?>">
                                <label class="control-label" for="lang"><?php echo $jkl['g282'];?></label>
                                <input type="text" name="lang" class="form-control underlined" id="lang" value="<?php echo $opsett["lang"];?>">
                                <input type="hidden" name="lang_old" value="<?php echo $opsett["lang"];?>">
                            </div>

                            <div class="form-group">
                                <label class="control-label" for="timezone_server"><?php echo $jkl["g301"];?></label>
                                <select name="timezone_server" class="form-control">
                                    <?php include_once "timezone.php";?>
                                </select>
                            </div>
                              
                            <div class="form-group">
                                <label class="control-label" for="dateformat"><?php echo $jkl['g280'];?></label>
                                <select name="dateformat" class="form-control">
                                    <option value="d.m.Y"<?php if ($opsett["dateformat"] == "d.m.Y") echo ' selected';?>><?php echo date("d.m.Y");?></option>
                                    <option value="m.d.y"<?php if ($opsett["dateformat"] == "m.d.y") echo ' selected';?>><?php echo date("m.d.y");?></option>
                                    <option value="F j, Y"<?php if ($opsett["dateformat"] == "F j, Y") echo ' selected';?>><?php echo date("F j, Y");?></option>
                                    <option value="Y-m-d"<?php if ($opsett["dateformat"] == "Y-m-d") echo ' selected';?>><?php echo date("Y-m-d");?></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="control-label" for="timeformat"><?php echo $jkl['g280'];?></label>
                                <select name="timeformat" class="form-control">
                                    <option value="g:i a"<?php if ($opsett["timeformat"] == "g:i a") echo ' selected';?>><?php echo date("g:i a");?></option>
                                    <option value="H:i:s"<?php if ($opsett["timeformat"] == "H:i:s") echo ' selected';?>><?php echo date("H:i:s");?></option>
                                    <option value="H:i"<?php if ($opsett["timeformat"] == "F j, Y") echo ' selected';?>><?php echo date("H:i");?></option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="control-label" for="avaheight"><?php echo $jkl['g283'];?></label>
                                <input type="number" min="100" max="250" name="avaheight" class="form-control underlined" id="avaheight" value="<?php echo $opsett["useravatheight"];?>">
                            </div>

                            <div class="form-group">
                                <label class="control-label" for="avawidth"><?php echo $jkl['g284'];?></label>
                                <input type="number" min="100" max="250" name="avawidth" class="form-control underlined" id="avawidth" value="<?php echo $opsett["useravatwidth"];?>">
                            </div>

                            <div class="form-group<?php if (isset($errors["e3"])) echo " has-error";?>">
                                <label class="control-label" for="smsmsg"><?php echo $jkl['g285'];?></label>
                                <input type="text" name="smsmsg" class="form-control underlined" id="smsmsg" value="<?php echo $opsett["tw_msg"];?>">
                            </div>

                          </div>
                          <div class="tab-pane fade" id="nav-department" aria-labelledby="nav-department-tab">

                            <h5><?php echo $jkl['g269'];?></h5>

                            <div class="form-group<?php if (isset($errors["e10"])) echo " has-error";?>">
                                <label class="control-label" for="chat_dep"><?php echo $jkl['g42'];?></label>
                                <input type="text" name="chat_dep" class="form-control underlined" id="chat_dep" value="<?php echo $opdep["title"];?>">
                            </div>

                            <div class="form-group<?php if (isset($errors["e11"])) echo " has-error";?>">
                                <label class="control-label" for="chat_dep_desc"><?php echo $jkl['g160'];?></label>
                                <input type="text" name="chat_dep_desc" class="form-control underlined" id="chat_dep_desc" value="<?php echo $opdep["description"];?>">
                            </div>

                        </div>  
                        <div class="tab-pane fade" id="nav-answers" aria-labelledby="nav-answers-tab">

                            <?php foreach ($answers as $a) { ?>

                            <div class="form-group">
                                <label class="control-label" for="answer_title_<?php echo $a["id"];?>"><?php echo $jkl['g42'];?></label>
                                <input type="text" name="answer_title_<?php echo $a["id"];?>" class="form-control underlined" id="answer_title_<?php echo $a["id"];?>" value="<?php echo $a["title"];?>">
                            </div>

                            <div class="form-group">
                                <label class="control-label" for="answer_lang"><?php echo $jkl['g282'];?></label>
                                <input type="text" name="answer_lang" class="form-control underlined" id="answer_lang" value="<?php echo $opsett["lang"];?>" disabled>
                            </div>

                            <div class="form-group">
                                <label class="control-label" for="answer_content_<?php echo $a["id"];?>"><?php echo $jkl['g135'];?></label>
                                <textarea rows="5" name="answer_content_<?php echo $a["id"];?>" class="form-control underlined"><?php echo $a["message"];?></textarea>
                                <small class="form-text text-muted">
                                    <?php echo $jkl["g296"];?>
                                </small>
                            </div>

                            <input type="hidden" name="answer_id[]" value="<?php echo $a["id"];?>">

                            <?php } ?>
                              
                        </div>
                        <div class="tab-pane fade" id="nav-widget" aria-labelledby="nav-widget-tab">

                                <h5><?php echo $jkl['g299'];?></h5>

                                <div class="form-group<?php if (isset($errors["e19"])) echo " has-error";?>">
                                    <label class="control-label" for="cw_title"><?php echo $jkl['g42'];?></label>
                                    <input type="text" name="cw_title" class="form-control underlined" id="cw_title" value="<?php echo $opchatwidget["title"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="cw_lang"><?php echo $jkl['g282'];?></label>
                                    <input type="text" name="cw_lang" class="form-control underlined" id="cw_lang" value="<?php echo $opsett["lang"];?>" disabled>
                                </div>

                                <div class="form-group<?php if (isset($errors["e20"])) echo " has-error";?>">
                                    <label class="control-label" for="cw_template"><?php echo $jkl['g286'];?></label>
                                    <input type="text" name="cw_template" class="form-control underlined" id="cw_template" value="<?php echo $opchatwidget["template"];?>">
                                </div>

                                <h5><?php echo $jkl['g300'];?></h5>

                                <div class="form-group<?php if (isset($errors["e22"])) echo " has-error";?>">
                                    <label class="control-label" for="gc_title"><?php echo $jkl['g42'];?></label>
                                    <input type="text" name="gc_title" class="form-control underlined" id="gc_title" value="<?php echo $opgroupchat["title"];?>">
                                </div>

                                <div class="form-group">
                                    <label class="control-label" for="gc_lang"><?php echo $jkl['g282'];?></label>
                                    <input type="text" name="gc_lang" class="form-control underlined" id="gc_lang" value="<?php echo $opsett["lang"];?>" disabled>
                                </div>

                                <div class="form-group">
                                <label class="control-label" for="gc_description"><?php echo $jkl['g160'];?></label>
                                <input type="text" name="gc_description" class="form-control underlined" id="gc_description" value="<?php echo $opgroupchat["description"];?>">
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