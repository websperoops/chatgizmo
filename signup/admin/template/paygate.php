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
    <form class="jak_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <section class="section">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-block sameheight-item">
                    <div class="title-block">
                        <h3 class="title"><?php echo $jkl['g305'];?></h3>
                    </div>

                        <div class="form-group<?php if (isset($errors["e"])) echo " has-error";?>">
                            <label class="control-label" for="currency"><?php echo $jkl['g68'];?></label>
                            <input type="text" name="currency" class="form-control underlined" id="currency" value="<?php echo $sett["currency"];?>">
                        </div>

                        <div class="form-group<?php if (isset($errors["e1"])) echo " has-error";?>">
                            <label class="control-label" for="trialdays"><?php echo $jkl['g69'];?></label>
                            <input type="text" name="trialdays" class="form-control underlined" id="trialdays" value="<?php echo $sett["trialdays"];?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="addops"><?php echo $jkl['g128'];?> (<?php echo $sett["currency"];?>)</label>
                            <input type="number" min="0" name="addops" class="form-control underlined" id="addops" value="<?php echo $sett["addops"];?>">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="exchangekey"><?php echo $jkl['g321'];?></label>
                            <input type="text" name="exchangekey" class="form-control underlined" id="exchangekey" value="<?php echo $sett["exchangekey"];?>">
                            <p class="text-muted"><?php echo $jkl['g322'];?></p>
                        </div>

                        <p><button type="submit" name="save" class="btn btn-success"><i class="fa fa-floppy"></i> <?php echo $jkl["g31"];?></button></p>
                </div>
            </div>
        </div>
    </section>
    <section class="section">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-block sameheight-item">
                    <div class="title-block">
                        <h3 class="title"><?php echo $jkl['g306'];?></h3>
                    </div>

                        <div class="form-group">
                        <label class="control-label" for="jak_stripe_secret"><?php echo $jkl["g108"];?></label>
                        <input type="text" name="jak_stripe_secret" class="form-control" value="<?php echo $sett["stripe_secret_key"];?>">
                    </div>

                    <div class="form-group">
                        <label class="control-label" for="jak_stripe_publish"><?php echo $jkl["g109"];?></label>
                        <input type="text" name="jak_stripe_publish" class="form-control" value="<?php echo $sett["stripe_publish_key"];?>">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="jak_paypal_client"><?php echo $jkl["g110"];?></label>
                        <input type="text" name="jak_paypal_client" class="form-control" value="<?php echo $sett["paypal_client"];?>">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="jak_paypal_secret"><?php echo $jkl["g325"];?></label>
                        <input type="text" name="jak_paypal_secret" class="form-control" value="<?php echo $sett["paypal_secret"];?>">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="jak_sandbox"><?php echo $jkl["g307"];?></label>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" name="jak_sandbox" value="1"<?php if ($sett["sandbox_mode"] == 1) { ?> checked="checked"<?php } ?>> <?php echo $jkl["g77"];?>
                                    <span class="circle">
                                        <span class="check"></span>
                                    </span>
                                </label>
                            </div>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" name="jak_sandbox" value="0"<?php if ($sett["sandbox_mode"] == 0) { ?> checked="checked"<?php } ?>> <?php echo $jkl["g78"];?>
                                    <span class="circle">
                                        <span class="check"></span>
                                    </span>
                                </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="jak_yookassa_id"><?php echo $jkl["g326"];?></label>
                        <input type="text" name="jak_yookassa_id" class="form-control" value="<?php echo $sett["yookassa_id"];?>">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="jak_yookassa_secret"><?php echo $jkl["g327"];?></label>
                        <input type="text" name="jak_yookassa_secret" class="form-control" value="<?php echo $sett["yookassa_secret"];?>">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="jak_paystack_secret"><?php echo $jkl["g328"];?></label>
                        <input type="text" name="jak_paystack_secret" class="form-control" value="<?php echo $sett["paystack_secret"];?>">
                    </div>
                    <!-- <div class="form-group">
                        <label class="control-label" for="jak_twoco"><?php echo $jkl['g251'];?></label>
                        <input type="text" name="jak_twoco" class="form-control" id="jak_twoco" value="<?php echo $sett["twoco"];?>">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="jak_twoco_secret"><?php echo $jkl['g252'];?></label>
                        <input type="password" name="jak_twoco_secret" class="form-control" id="jak_twoco_secret" value="<?php echo $sett["twoco_secret"];?>">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="jak_authorize_id"><?php echo $jkl['g329'];?></label>
                        <input type="text" name="jak_authorize_id" class="form-control" id="jak_authorize_id" value="<?php echo $sett["authorize_id"];?>">
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="jak_authorize_key"><?php echo $jkl['g330'];?></label>
                        <input type="password" name="jak_authorize_key" class="form-control" id="jak_authorize_key" value="<?php echo $sett["authorize_key"];?>">
                    </div> -->
                    <div class="form-group">
                        <label class="control-label" for="jak_bank_info"><?php echo $jkl["g331"];?></label>
                        <textarea name="bank_info" class="form-control"><?php echo $sett["bank_info"];?></textarea>
                    </div>

                        <p><button type="submit" name="save" class="btn btn-success"><i class="fa fa-floppy"></i> <?php echo $jkl["g31"];?></button></p>
                </div>
            </div>
        </div>
    </section>
    </form>
    <div class="pull-right">
        <p><a class="btn btn-primary" href="<?php echo JAK_rewrite::jakParseurl('p', 'n');?>"><i class="fa fa-plus-square"></i> <?php echo $jkl['g309'];?></a></p>
    </div>
    <div class="clearfix"></div>
    <section class="section">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-block">
                        <div class="card-title-block"><h3 class="title"><?php echo $jkl['g306'];?></h3></div>
                        <section class="example">
                            <div class="table-flip-scroll">
                                <table class="table table-striped table-bordered table-hover table-responsive flip-content w-100 d-block d-md-table">
                                    <thead class="flip-header">
                                        <tr>
                                            <th><?php echo $jkl['g56'];?></th>
                                            <th><?php echo $jkl['g65'];?></th>
                                            <th><?php echo $jkl['g42'];?></th>
                                            <th><?php echo $jkl['g316'];?></th>
                                            <th><?php echo $jkl['g307'];?></th>
                                            <th><?php echo $jkl['g117'];?></th>
                                            <th><?php echo $jkl['g44'];?></th>
                                            <th><?php echo $jkl['g308'];?></th>
                                            <th><i class="fa fa-edit"></i></th>
                                            <th><i class="fa fa-trash-o"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (isset($gateways) && !empty($gateways)) foreach ($gateways as $v) { ?>
                                            <tr>
                                                <td><?php echo $v["id"];?></td>
                                                <td><a href="<?php echo JAK_rewrite::jakParseurl('l', 'e', $v["locid"]);?>"><?php echo $v["locid"];?></a></td>
                                                <td><a href="<?php echo JAK_rewrite::jakParseurl('p', 'e', $v["id"]);?>"><?php echo $v["title"];?></a></td>
                                                <td><?php echo $v["currency"];?></td>
                                                <td><i class="fa fa-<?php echo ($v["sandbox"] ? 'check' : 'times');?>"></i></td>
                                                <td><i class="fa fa-<?php echo ($v["active"] ? 'check' : 'times');?>"></i></td>
                                                <td><?php echo ($v["lastedit"] ? JAK_base::jakTimesince($v["lastedit"], $sett["dateformat"], $sett["timeformat"]) : "-");?></td>
                                                <td><?php echo ($v["created"] ? JAK_base::jakTimesince($v["created"], $sett["dateformat"], $sett["timeformat"]) : "-");?></td>
                                                <td><a href="<?php echo JAK_rewrite::jakParseurl('p', 'e', $v["id"]);?>"><i class="fa fa-edit"></i></a></td>
                                                <td><a href="<?php echo JAK_rewrite::jakParseurl('p', 'd', $v["id"]);?>"><i class="fa fa-trash-o"></i></a></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>
</article>

<?php include "footer.php";?>