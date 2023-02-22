<?php include_once 'header.php';?>

<article class="content forms-page">
    <div class="title-block">
        <h1 class="title"><i class="fa fa-wrench"></i> <?php echo $SECTION_TITLE;?></h1>
        <p class="title-description"><?php echo $SECTION_DESC;?></p>
    </div>

	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'];?>">

		<div class="row">
		<div class="col-md-6">

			<div class="card">
				<div class="card-block">
				<div class="card-header">
					<h4 class="card-title"> <?php echo $jkl["g249"];?></h4>
				</div>
				<div class="card-body">

					<div class="alert alert-info">
                  <span><?php echo $licmsg;?></span>
                </div>
                <?php if ($verify_response['status'] != true) { ?>
	              <div class="form-group">
							   <label for="jaklic"><?php echo $jkl["g250"];?></label>
							   <input type="text" name="jak_lic" id="jaklic" class="form-control<?php if (isset($errors["e1"])) echo " is-invalid";?>" autocomplete="off">
						    </div>

						<div class="form-group">
							<label for="jaklicusr"><?php echo $jkl["g251"];?></label>
							<input type="text" name="jak_licusr" id="jaklicusr" class="form-control<?php if (isset($errors["e2"])) echo " is-invalid";?>" autocomplete="off">
						</div>
						<button type="submit" name="regLicense" class="btn btn-success"><?php echo $jkl["g255"];?></button>
                	<?php } else { ?>
                		<button type="submit" name="deregLicense" class="btn btn-danger"><?php echo $jkl["g252"];?></button>
                	<?php } ?>

				</div>
			</div>
			</div>
			<div class="card">
				<div class="card-block">
        	<div class="card-header">
					<h4 class="card-title"> <?php echo $jkl["g224"];?></h4>
        		</div>
        		<div class="card-body">
        			<?php if ($verify_response['status']) { $update_data = $jaklic->check_update();?>
        			<div class="alert alert-info">
        				<span><?php echo $update_data['message'];?></span>
        			</div>
        			<?php if ($update_data['status']) { ?>
					<p><?php echo $update_data['changelog'];?></p><?php 
                	$update_id = null;
                	$has_sql = null;
                	$version = null;
                	if (!empty($_POST['update_id'])) {
                  	$update_id = strip_tags(trim($_POST["update_id"]));
                  	$has_sql = strip_tags(trim($_POST["has_sql"]));
                  	$version = strip_tags(trim($_POST["version"]));
                  	?>
                  	<div class="progress-container progress-primary mb-3">
                      <span class="progress-badge"><?php echo $jkl['g253'];?></span>
                      <div class="progress">
                        <div class="progress-bar progress-bar-success" role="progressbar" id="updprog" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                          <span class="progress-value" id="updprogval">0%</span>
                        </div>
                      </div>
                    </div>
                    <?php
                  	$jaklic->download_update($_POST['update_id'], $_POST['has_sql'], $_POST['version']);
        			} else { ?>
        			<input type="hidden" class="form-control" value="<?php echo $update_data['update_id'];?>" name="update_id">
                    <input type="hidden" class="form-control" value="<?php echo $update_data['has_sql'];?>" name="has_sql">
                    <input type="hidden" class="form-control" value="<?php echo $update_data['version'];?>" name="version">
                    <button type="submit" name="updSoftware" class="btn btn-success"><?php echo $jkl["g254"];?></button>
                	<?php } } } ?>
        		</div>
        	</div>
        </div>
		</div>
		<div class="col-md-6">

			<div class="card">
        <div class="card-block">
          <div class="card-header">
            <h4 class="card-title"> <?php echo $jkl["g223"];?></h4>
          </div>
          <div class="card-body">
						<button type="submit" name="optimize" class="btn btn-success"><?php echo $jkl["g223"];?></button>
					</div>
				</div>
			</div>

		</div>
	</div>
	</form>
	
</article>

<?php include_once 'footer.php';?>