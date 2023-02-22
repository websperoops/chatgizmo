<!doctype html>
<html class="no-js" lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title> <?php echo $SECTION_TITLE;?> - Cloud Chat 3 :: Administration </title>
        <!-- Place favicon.ico in the root directory -->
        <!-- Theme initialization -->
        <link rel="stylesheet" href="<?php echo BASE_URL;?>css/app-blue.css">
        <link rel="stylesheet" href="<?php echo BASE_URL;?>css/vendor.css">
    </head>

    <body>
        <?php if (JAK_USERID) { ?>
        <div class="main-wrapper">
            <div class="app" id="app">
                <header class="header">
                    <div class="header-block header-block-collapse hidden-lg-up navbar-toggler" aria-expanded="false"> <button class="collapse-btn" id="sidebar-collapse-btn">
    			<i class="fa fa-bars"></i>
    		</button> </div>
                    <div class="header-block header-block-nav">
                        <ul class="nav nav-profile">
                            <li class="nav-item profile dropdown">
                                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false" id="adminmenu">
                                    <div class="img" style="background-image: url('<?php echo BASE_URL_ORIG;?>img/avatars<?php echo $jakuser->getVar("picture");?>')"> </div> <span class="name"><?php echo $JAK_WELCOME_NAME;?>
    			    </span> </a>
                                <div class="dropdown-menu profile-dropdown-menu" aria-labelledby="adminmenu">
                                    <a class="dropdown-item" href="<?php echo JAK_rewrite::jakParseurl('u', 'e', JAK_USERID);?>"> <i class="fa fa-user icon"></i> <?php echo $jkl['g19'];?></a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="<?php echo JAK_rewrite::jakParseurl('logout');?>"> <i class="fa fa-power-off icon"></i> <?php echo $jkl['g10'];?></a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </header>
                <aside class="sidebar">
                    <div class="sidebar-container">
                        <div class="sidebar-header">
                            <div class="brand">
                                <div class="logo"><img src="<?php echo BASE_URL_ORIG;?>img/admin_logo.png" alt="logo"></div> CC3 Admin</div>
                        </div>
                        <nav class="menu">
                            <ul class="nav flex-column metismenu" id="sidebar-menu">
                                <li<?php if ($page == "") echo ' class="active"';?>>
                                    <a href="<?php echo BASE_URL;?>"> <i class="fa fa-home"></i> <?php echo $jkl['g18'];?></a>
                                </li>
                                <?php if (jak_get_access("c", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) { ?>
                                <li<?php if ($page == "t") echo ' class="active"';?>>
                                    <a href="<?php echo JAK_rewrite::jakParseurl('t');?>"> <i class="fa fa-life-ring"></i> <?php echo $jkl['g129'];?></a>
                                </li>
                                <li<?php if ($page == "c") echo ' class="active"';?>>
                                    <a href="<?php echo JAK_rewrite::jakParseurl('c');?>"> <i class="fa fa-users"></i> <?php echo $jkl['g55'];?></a>
                                </li>
                                <li<?php if ($page == "lc3") echo ' class="active"';?>>
                                    <a href="<?php echo JAK_rewrite::jakParseurl('lc3');?>"> <i class="fa fa-comments-o"></i> <?php echo $jkl['g96'];?></a>
                                </li>
                                <li<?php if ($page == "hd3") echo ' class="active"';?>>
                                    <a href="<?php echo JAK_rewrite::jakParseurl('hd3');?>"> <i class="fa fa-ticket"></i> <?php echo $jkl['g102'];?></a>
                                </li>
                                <?php } if (jak_get_access("l", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) { ?>
                                <li<?php if ($page == "l") echo ' class="active"';?>>
                                    <a href="<?php echo JAK_rewrite::jakParseurl('l');?>"> <i class="fa fa-location-arrow"></i> <?php echo $jkl['g39'];?></a>
                                </li>
                                <?php } ?>
                                <li<?php if ($page == "u") echo ' class="active"';?>>
                                    <a href="<?php echo JAK_rewrite::jakParseurl('u');?>"> <i class="fa fa-user"></i> <?php echo $jkl['g22'];?></a>
                                </li>
                                <?php if (jak_get_access("s", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) { ?>
                                <li<?php if ($page == "s") echo ' class="active"';?>>
                                    <a href="<?php echo JAK_rewrite::jakParseurl('s');?>"> <i class="fa fa-cogs"></i> <?php echo $jkl['g60'];?></a>
                                </li>
                                <li<?php if ($page == "lt") echo ' class="active"';?>>
                                    <a href="<?php echo JAK_rewrite::jakParseurl('lt');?>"> <i class="fa fa-language"></i> <?php echo $jkl['g207'];?></a>
                                </li>
                                <?php } if (jak_get_access("p", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) { ?>
                                <li<?php if ($page == "su") echo ' class="active"';?>>
                                    <a href="<?php echo JAK_rewrite::jakParseurl('su');?>"> <i class="fa fa-clock-o"></i> <?php echo $jkl['g125'];?></a>
                                </li>
                                <li<?php if ($page == "p") echo ' class="active"';?>>
                                    <a href="<?php echo JAK_rewrite::jakParseurl('p');?>"> <i class="fa fa-money"></i> <?php echo $jkl['g106'];?></a>
                                </li>
                                <li<?php if ($page == "co") echo ' class="active"';?>>
                                    <a href="<?php echo JAK_rewrite::jakParseurl('co');?>"> <i class="fa fa-gift"></i> <?php echo $jkl['g182'];?></a>
                                </li>
                                <li<?php if ($page == "pa") echo ' class="active"';?>>
                                    <a href="<?php echo JAK_rewrite::jakParseurl('pa');?>"> <i class="fa fa-archive"></i> <?php echo $jkl['g155'];?></a>
                                </li>
                                <?php } if (jak_get_access("s", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) { ?>
                                <li<?php if ($page == "m") echo ' class="active"';?>>
                                    <a href="<?php echo JAK_rewrite::jakParseurl('m');?>"> <i class="fa fa-wrench"></i> <?php echo $jkl['g225'];?></a>
                                </li>
                                <?php } ?>
                            </ul>
                        </nav>
                    </div>
                </aside>
                <div class="sidebar-overlay" id="sidebar-overlay"></div>
                <?php } ?>