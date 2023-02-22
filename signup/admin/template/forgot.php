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
                <h3 class="text-center"><?php echo $jkl["g4"];?></h3>
                <?php if (isset($errors) && !empty($errors)) { ?>
                <div class="alert alert-danger"><?php echo $errors;?></div>
                <?php } ?>
                <form id="reset-form" action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post">
                    <div class="form-group">
                        <label for="email1"><?php echo $jkl["g17"];?></label>
                        <input type="text" class="form-control underlined" name="lsE" id="email1" placeholder="<?php echo $jkl["g17"];?>" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-block btn-primary"><?php echo $jkl["g15"];?></button>
                    </div>
                    <div class="form-group clearfix">
                        <a class="pull-left" href="<?php echo BASE_URL;?>"><?php echo $jkl["g14"];?></a>
                    </div>
                    <input type="hidden" name="action" value="forgot">
                </form>
            </div>
        </div>
    </div>
</div>
<?php include "footer.php";?>