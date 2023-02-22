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
        <div class="row">
            <div class="col-md-12">
            <?php if (isset($subscriptions) && !empty($subscriptions)) { ?>
                <div class="card">
                    <div class="card-block">
                        <div class="card-title-block"><h3 class="title"><?php echo $jkl['g125'];?></h3></div>
                        <section class="example">
                            <div class="table-flip-scroll">
                                <table class="table table-striped table-bordered table-hover flip-content">
                                    <thead class="flip-header">
                                        <tr>
                                            <th><?php echo $jkl['g56'];?></th>
                                            <th><?php echo $jkl['g65'];?></th>
                                            <th><?php echo $jkl['g121'];?></th>
                                            <th><?php echo $jkl['g122'];?></th>
                                            <th><?php echo $jkl['g127'];?></th>
                                            <th><?php echo $jkl['g123'];?></th>
                                            <th><?php echo $jkl['g61'];?></th>
                                            <th><?php echo $jkl['g124'];?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach($subscriptions as $v) { ?>
                                        <tr>
                                            <td><?php echo $v["id"];?></td>
                                            <td><?php echo $v["locationid"];?></td>
                                            <td><?php echo $v["amount"].' '.$sett["currency"];?></td>
                                            <td><?php echo $v["paidhow"];?></td>
                                            <td><?php echo (!empty($v["paidfor"]) ? $v["paidfor"] : $v["title"]);?></td>
                                            <td><?php echo ($v["paidwhen"] ? JAK_base::jakTimesince($v["paidwhen"], $sett["dateformat"], $sett["timeformat"]) : "-");?></td>
                                            <td><?php echo ($v["paidtill"] ? JAK_base::jakTimesince($v["paidtill"], $sett["dateformat"], $sett["timeformat"]) : "-");?></td>
                                            <td><?php echo ($v["success"] ? '<i class="fa fa-check"></i>' : '<i class="fa fa-exclamation-triangle"></i>');?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </div>
                <?php } else { ?>
                <div class="alert alert-info"><?php echo $jkl['e23'];?></div>
                <?php } ?>
            </div>
        </div>
        <form class="jak_form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
        <div class="row">
            <div class="col-md-12">
            <?php if (isset($packages) && !empty($packages)) { ?>
                <div class="card">
                    <div class="card-block">
                        <div class="card-title-block"><h3 class="title"><?php echo $jkl['g235'];?></h3></div>
                        <section class="example">
                            <div class="table-flip-scroll">
                                <table class="table table-striped table-bordered table-hover flip-content">
                                    <thead class="flip-header">
                                        <tr>
                                            <th><?php echo $jkl['g236'];?></th>
                                            <th><?php echo $jkl['g65'];?></th>
                                            <th><?php echo $jkl['g160'];?></th>
                                            <th><?php echo $jkl['g121'];?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach($packages as $p) { ?>
                                        <tr>
                                            <td><input type="radio" name="jak_package" value="<?php echo $p["id"];?>"></td>
                                            <td><?php echo $p["title"];?></td>
                                            <td><?php echo $p["description"];?></td>
                                            <td><?php echo $p["amount"].' '.$sett["currency"];?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                                <p class="text-muted"><?php echo $jkl['g237'];?></p>
                            </div>
                        </section>
                    </div>
                </div>
                <?php } else { ?>
                <div class="alert alert-info"><?php echo $jkl['e23'];?></div>
                <?php } ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card card-block sameheight-item">
                    <div class="title-block">
                    	<div class="card-title-block"><h3 class="title"><?php echo $SECTION_TITLE;?></h3></div>

                        	<div class="form-group<?php if (isset($errors["e1"]) || isset($errors["e3"])) echo " has-error";?>">
                        		<label class="control-label" for="username"><?php echo $jkl['g25'];?></label>
                        		<input type="text" name="username" class="form-control underlined" id="username" value="<?php echo $client["username"];?>">
                        	</div>

                        	<div class="form-group<?php if (isset($errors["e"]) || isset($errors["e4"])) echo " has-error";?>">
                        		<label class="control-label" for="email"><?php echo $jkl['g26'];?></label>
                        		<input type="text" name="email" class="form-control underlined" id="email" value="<?php echo $client["email"];?>">
                        	</div>

                            <div class="form-group">
                                <label class="control-label" for="locationid"><?php echo $jkl['g65'];?></label>
                                <select name="locationid" id="locationid" class="form-control">
                                    <?php if (isset($locations) && !empty($locations)) foreach ($locations as $v) { ?>
                                    <option value="<?php echo $v["id"];?>"<?php if ($client["locationid"] == $v["id"]) echo " selected";?>><?php echo $v["title"];?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="form-group<?php if (isset($errors["e7"])) echo " has-error";?>">
                                <label class="control-label" for="trial"><?php echo $jkl['g64'];?></label>
                                <input type="text" name="trial" class="form-control underlined" id="trial" value="<?php echo ($client["trial"] != "1980-05-06 00:00:00" ? JAK_base::jakTimesince($client["trial"], "d.m.Y", "") : "");?>">
                            </div>

                            <div class="form-group<?php if (isset($errors["e6"])) echo " has-error";?>">
                                <label class="control-label" for="paidtill"><?php echo $jkl['g61'];?></label>
                                <input type="text" name="paidtill" class="form-control underlined" id="paidtill" value="<?php echo JAK_base::jakTimesince($client["paidtill"], "d.m.Y", "");?>">
                            </div>

                        	<div class="form-group<?php if (isset($errors["e4"]) || isset($errors["e5"])) echo " has-error";?>">
                        		<label class="control-label" for="pass"><?php echo $jkl['g32'];?></label>
                        		<input type="password" name="pass" class="form-control underlined" id="pass">
                        	</div>

                        	<div class="form-group">
                        		<label class="control-label" for="passc"><?php echo $jkl['g33'];?></label>
                        		<input type="password" name="passc" class="form-control underlined" id="passc">
                        	</div>

                            <div class="form-group">
                                <label class="control-label" for="extraop"><?php echo $jkl['g147'];?></label>
                                <input type="number" name="extraop" class="form-control underlined" id="extraop" min="-5" max="5">
                            </div>

                           <!-- <div class="form-group">
                                <label class="control-label"><?php echo $jkl['g242'];?></label>
                                <div>
                                    <label>
                                        <input class="radio" type="radio" name="islc3hd3" value="0" checked><span>Cloud Chat 3</span>
                                    </label>
                                    <label>
                                        <input class="radio" type="radio" name="islc3hd3" value="1"><span>Live Chat 3</span>
                                    </label>
                                    <label>
                                        <input class="radio" type="radio" name="islc3hd3" value="2"><span>HelpDesk 3</span>
                                    </label>
                                </div>
                            </div> -->

                        	<p><button type="submit" name="save" class="btn btn-success"><i class="fa fa-floppy-o"></i> <?php echo $jkl["g31"];?></button></p>
                            <input type="hidden" name="oldlocationid" value="<?php echo $client["locationid"];?>">
                            <input type="hidden" name="opid" value="<?php echo $client["opid"];?>">
                            <input type="hidden" name="oldpaid" value="<?php echo strtotime($client["paidtill"]);?>">
                        </form>
                    </div>
            	</div>
       		</div>
    	</div>
    </section>
</article>

<?php include "footer.php";?>