<?php include "header.php";?>

<div class="auth">
    <div class="auth-container">
        <div class="card">
            <header class="auth-header">
                <h1 class="auth-title">
			        Cloud Chat 3 - Admin
      			</h1>
      		</header>
            <div class="auth-content">
                <h3 class="text-center"><?php echo $jkl["g"];?></h3>
                <?php if (isset($errors) && !empty($errors)) { ?>
                <div class="alert alert-danger"><?php echo $errors;?></div>
                <?php } ?>
                <form id="login-form" action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post">
                    <div class="form-group">
                    	<label for="username"><?php echo $jkl["g1"];?></label>
                    	<input type="text" class="form-control underlined" name="username" id="username" placeholder="<?php echo $jkl["g1"];?>" required>
                    </div>
                    <div class="form-group">
                    	<label for="password"><?php echo $jkl["g2"];?></label>
                    	<input type="password" class="form-control underlined" name="password" id="password" placeholder="<?php echo $jkl["g2"];?>" required>
                    </div>
                    <div class="form-group">
                    	<label for="remember">
            				<input class="checkbox" id="remember" name="lcookies" type="checkbox"> 
        					<span><?php echo $jkl["g13"];?></span>
          				</label>
          				<a href="<?php echo JAK_rewrite::jakParseurl('rfp');?>" class="forgot-btn pull-right"><?php echo $jkl["g3"];?></a>
          			</div>
                    <div class="form-group">
                    	<button type="submit" class="btn btn-block btn-primary"><?php echo $jkl["g"];?></button>
                    </div>
                    <input type="hidden" name="action" value="login">
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php";?>