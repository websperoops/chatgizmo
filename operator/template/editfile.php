<?php include_once 'header.php';?>

<div class="content">

<?php if ($errors) { ?>
<div class="alert alert-danger">
<?php if (isset($errors["e"])) echo $errors["e"];
	  if (isset($errors["e1"])) echo $errors["e1"];?>
</div>
<?php } ?>

<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data">
<div class="card">
<div class="card-header">
  <h3 class="card-title"><i class="fa fa-edit"></i> <?php echo $jkl["g47"];?></h3>
</div><!-- /.box-header -->
<div class="card-body">

	<?php if (file_exists(CLIENT_UPLOAD_DIR.$JAK_FORM_DATA["path"])) { if (getimagesize(CLIENT_UPLOAD_DIR.$JAK_FORM_DATA["path"])) { ?>
	<p><img class="img-thumbnail img-fluid" src="<?php echo BASE_URL_ORIG;?>_showfile.php?i=<?php echo jak_encrypt_decrypt($JAK_FORM_DATA["path"].':#:'.$JAK_FORM_DATA["orig_name"].':#:'.$JAK_FORM_DATA["mime_type"]);?>" alt="<?php echo $JAK_FORM_DATA["name"];?>"></p>
	<?php } else { ?>
	<p><a class="btn btn-secondary" href="<?php echo BASE_URL_ORIG;?>_showfile.php?i=<?php echo jak_encrypt_decrypt($JAK_FORM_DATA["path"].':#:'.$JAK_FORM_DATA["orig_name"].':#:'.$JAK_FORM_DATA["mime_type"]);?>"><i class="fa fa-download fa-2x"></i> <?php echo $JAK_FORM_DATA["orig_name"];?></a></p>
	<?php } } else { echo $jkl['i16']; } ?>

	<p><a href="javascript:void(0)" data-clipboard-target="#copy-img" class="btn btn-primary btn-sm clipboard"><i class="fa fa-clipboard"></i></a></p>

	<div class="alert alert-info"><span id="copy-img"><?php echo BASE_URL_ORIG;?>_showfile.php?i=<?php echo jak_encrypt_decrypt($JAK_FORM_DATA["path"].':#:'.$JAK_FORM_DATA["orig_name"].':#:'.$JAK_FORM_DATA["mime_type"]);?></span></div>

	<div class="form-group">
		<label for="name"><?php echo $jkl["g53"];?></label>
		<input type="text" name="name" id="name" class="form-control<?php if (isset($errors["e"])) echo " is-invalid";?>" value="<?php echo $JAK_FORM_DATA["name"];?>">
	</div>

	<div class="form-group">
		<label for="desc"><?php echo $jkl["g52"];?></label>
		<textarea name="description" id="desc" rows="5" class="form-control"><?php echo $JAK_FORM_DATA["description"];?></textarea>
	</div>

</div>
<div class="card-footer">
	<a href="<?php echo JAK_rewrite::jakParseurl('files');?>" class="btn btn-default"><?php echo $jkl["g103"];?></a>
	<button type="submit" name="save" class="btn btn-primary"><?php echo $jkl["g38"];?></button>
</div>
</div>

</form>

</div>
		
<?php include_once 'footer.php';?>