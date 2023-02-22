<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.1.1                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2023 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

/* NO CHANGES FROM HERE */
if (file_exists('../include/db.php')) die('Installation Wizard is protected, please delete the include/db.php file first.');
if (file_exists('../signup/include/db.php')) die('Installation Wizard is protected, please delete the signup/include/db.php file first.');
if (!file_exists('../class/class.jaklic.php')) die('It looks like the boat has been reported as missing.');

// Get the ls DB class
require_once '../class/class.db.php';

// Change for 3.0.3
use JAKWEB\JAKsql;

// Absolute Path
define('DIR_APPLICATION', str_replace('\'', '/', realpath(dirname(__FILE__))) . '/');
define('DIR_jakweb', str_replace('\'', '/', realpath(DIR_APPLICATION . '../')) . '/');

// Errors is array
$errors = array();
// Show form
$show_form = true;

// Test for the config.php File

if (@file_exists('../config.php')) {
  
  $data_file = '<strong style="color:green">config.php available</strong>';
} else {
  
  $data_file = '<strong style="color:red">config.php not available!</strong>';
}

// Test the minimum PHP version
$php_version = PHP_VERSION;
$php_big = '';
if (version_compare($php_version, '7.0') < 0) {
  $result_php = '<strong style="color:red">You need a higher version of PHP (min. PHP 7.0)!</strong>';
} else {
  
  if (version_compare($php_version, '8.2') > 0) $php_big = '<br><strong style="color:red">The software has not been tested on your php version yet.</strong>';

  // We also give feedback on whether we're running in safe mode
  $result_safe = '<strong style="color:green">PHP Version: '.$php_version.'</strong>';
  if (@ini_get('safe_mode') || strtolower(@ini_get('safe_mode')) == 'on') {
    $result_safe .= ', <strong style="color:red">Safe Mode activated</strong>.';
  } else {
    $result_safe .= '<strong style="color:green">, Safe Mode deactivated.</strong>';
  }
  
  $result_safe .= $php_big;
}

$dircc = DIR_jakweb."/cache";
$writecc = false;
// Now really check
      if (file_exists($dircc) && is_dir($dircc))
      {
        if (@is_writable($dircc))
        {
          $writecc = true;
        }
        $existscc = true;
      }

      @$passedcc['files'] = ($existscc && $passedcc['files']) ? true : false;

      @$existscc = ($existscc) ? '<strong style="color:green">Found folder (cache)</strong>' : '<strong style="color:red">Folder not found! (cache), </strong>';
      @$writecc = ($writecc) ? '<strong style="color:green">permission set</strong> (cache), ' : (($existscc) ? '<strong style="color:red">permission not set (check guide)!</strong> (cache), ' : ''); 

// Check if the files directory is writeable      
$dirc = DIR_jakweb."/files";
$writec = false;
// Now really check
      if (file_exists($dirc) && is_dir($dirc))
      {
        if (@is_writable($dirc))
        {
          $writec = true;
        }
        $existsc = true;
      }

      @$passedc['files'] = ($existsc && $passedc['files']) ? true : false;

      @$existsc = ($existsc) ? '<strong style="color:green">Found folder</strong> (files)' : '<strong style="color:red">Folder not found!</strong> (files)';
      @$writec = ($writec) ? '<strong style="color:green">permission set</strong> (files)' : (($existsc) ? '<strong style="color:red">permission not set!</strong> (files)' : '');
      
// GD Graphics Support

if (!extension_loaded("gd")) {
  $gd_data = '<strong style="color:orange">GD-Libary not available</strong>';
} else {
  $gd_data = '<strong style="color:green">GD-Libary available</strong>';
}

// Zlip for auto updater
if (!extension_loaded('curl')) {
  $curl_data = '<strong style="color:orange">cURL is not available, some features like SMS and Push notifications do not work.</strong>';
} else {
  $curl_data = '<strong style="color:green">cURL is available, IP/GEO, Push Notifications and SMS should now work!</strong>';
}

// Zlip for auto updater
if (!extension_loaded('zlib') && !ini_get('allow_url_fopen')) {
  $zip_data = '<strong style="color:orange">Zlib-Library not available and/or allow_url_fopen is disabled. Auto Updater will not work.</strong>';
} else {
  $zip_data = '<strong style="color:green">Zlib-Library available and allow_url_fopen is enabled. Sweet, update to future versions possible with a click. Enjoy the integrated Auto Updater.</strong>';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Cloud Chat 3 - Installation Wizard</title>

        <!-- CSS -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,100,300,500">
        <link rel="stylesheet" href="../css/bootstrap.css">
        <link rel="stylesheet" href="../css/fontawesome.css">
        <link rel="stylesheet" href="assets/css/form-elements.css">
        <link rel="stylesheet" href="assets/css/style.css">

        <!--[if lt IE 9]>
        <script src="https://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <script src="js/respond_ie.js"></script>
        <![endif]-->

    </head>
<body>

        <!-- Top content -->
        <div class="top-content">
            <div class="container">
                
                <div class="row justify-content-center">
                    <div class="col-sm-8 text">
                        <h1>Cloud Chat 3 <strong>Installation</strong> Wizard</h1>
                        <div class="description">
                            <p>
                                This will guide you through the installation for Cloud Chat 3. Make sure to follow the steps carefully.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="row justify-content-center">
                    <div class="col-sm-10 form-box">
                      <div class="f1">

                        <h3>Ready for the future?</h3>
                        <p>Install the most advanced Live Chat solution on the market.</p>
                        <div class="f1-steps">
                          <div class="f1-progress">
                              <div class="f1-progress-line" data-now-value="10" data-number-of-steps="5" style="width: 10%;"></div>
                          </div>
                          <div class="f1-step active">
                              <div class="f1-step-icon"><i class="fa fa-info"></i></div>
                              <p>info</p>
                          </div>
                          <div class="f1-step">
                              <div class="f1-step-icon"><i class="fa fa-server"></i></div>
                              <p>connect</p>
                          </div>
                          <div class="f1-step">
                            <div class="f1-step-icon"><i class="fa fa-check-circle"></i></div>
                            <p>check</p>
                          </div>
                          <div class="f1-step">
                            <div class="f1-step-icon"><i class="fa fa-database"></i></div>
                            <p>database</p>
                          </div>
                            <div class="f1-step">
                            <div class="f1-step-icon"><i class="fa fa-user"></i></div>
                            <p>account</p>
                          </div>
                        </div>

                        <fieldset>
                          <h4>Ready to install Cloud Chat 3?</h4>
                          <p>Have you read the installation manual, if not please do so.</p>
                          <p class="text-center"><img src="assets/img/read_manual.png" alt="read the f... manual" class="img-fluid img-thumbnail"></p>
                          <p>Now all set? Let's start the engine and connect to your databases.</p>
                          <div class="f1-buttons">
                            <button type="button" class="btn btn-next">Setup Database Connections</button>
                          </div>
                        </fieldset>

                        <fieldset>
                          <div id="form-success" style="display: none">
                            <div class="alert alert-success">
                              <p><h4>Database connections succesful!</h4></p>
                            </div>
                          </div>
                          <div id="form-elementsdb">
                            <p><h4>Let's connect to your databases</h4>If you have not created the two necessary databases it is now time to do so. If you have it already, let's proceed.</p>
                            <div id="form-error" style="display: none">
                            <div class="alert alert-danger">
                              Some fields are not correct, please fix it.<br>
                              <span id="error_msg"></span>
                            </div>
                          </div>
                          <form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post" class="form-dbconn">
                            <h4>Database 1 Information (Server)</h4>
                            <div class="form-group">
                                <label for="f1-dbhost1">Database Host 1</label>
                                <input type="text" name="f1-dbhost1" placeholder="localhost" class="f1-dbhost1 form-control" id="f1-dbhost1">
                            </div>
                            <div class="form-group">
                                <label for="f1-dbport1">Database Port 1</label>
                                <input type="text" name="f1-dbport1" placeholder="3306" class="f1-dbport1 form-control" id="f1-dbport1">
                            </div>
                            <div class="form-group">
                                <label for="f1-dbuser1">Database User 1</label>
                                <input type="text" name="f1-dbuser1" placeholder="" class="f1-dbuser1 form-control" id="f1-dbuser1">
                            </div>
                            <div class="form-group">
                                <label for="f1-dbpass1">Database User Password 1</label>
                                <input type="password" name="f1-dbpass1" placeholder="" class="f1-dbpass1 form-control" id="f1-dbpass1">
                            </div>
                            <div class="form-group">
                                <label for="f1-dbname1">Database Name 1</label>
                                <input type="text" name="f1-dbname1" placeholder="Database 1" class="f1-dbname1 form-control" id="f1-dbname1">
                            </div>
                            <h4>Database 2 Information (Administration Panel)</h4>
                            <p>Cannot be the same as above, you will need two databases</p>
                            <div class="form-group">
                                <label for="f1-dbhost2">Database Host 2</label>
                                <input type="text" name="f1-dbhost2" placeholder="localhost" class="f1-dbhost2 form-control" id="f1-dbhost2">
                            </div>
                            <div class="form-group">
                                <label for="f1-dbport2">Database Port 2</label>
                                <input type="text" name="f1-dbport2" placeholder="3306" class="f1-dbport2 form-control" id="f1-dbport2">
                            </div>
                            <div class="form-group">
                                <label for="f1-dbuser2">Database User 2</label>
                                <input type="text" name="f1-dbuser2" placeholder="" class="f1-dbuser2 form-control" id="f1-dbuser2">
                            </div>
                            <div class="form-group">
                                <label for="f1-dbpass2">Database User Password 2</label>
                                <input type="password" name="f1-dbpass2" placeholder="" class="f1-dbpass2 form-control" id="f1-dbpass2">
                            </div>
                            <div class="form-group">
                                <label for="f1-dbname2">Database Name 2</label>
                                <input type="text" name="f1-dbname2" placeholder="Database 2" class="f1-dbname2 form-control" id="f1-dbname2">
                            </div>
                            <h4>Additional Information</h4>
                            <div class="form-group">
                                <label for="f1-cc3domain">Your domain</label>
                                <input type="text" name="f1-cc3domain" placeholder="<?php echo $_SERVER['HTTP_HOST'];?>" class="f1-cc3domain form-control" id="f1-cc3domain">
                                <small class="form-text text-muted">Define your site url, for example: jakweb.ch (<a href="https://jakweb.ch/faq/a/98/full-site-domain">FAQ - Full Site Domain</a>)</small>
                            </div>
                            <div class="form-group">
                                <label for="f1-upath">Upload Path</label>
                                <input type="text" name="f1-upath" placeholder="/home/domain/myuploadfolder" class="f1-upath form-control" id="f1-upath">
                                <small class="form-text text-muted">File Upload directory, absolute path. Read more about here: (<a href="https://jakweb.ch/faq/a/189/file-upload-for-cloud-chat-3">FAQ - Upload Path</a>)</small>
                            </div>
                            <div class="form-group">
                              <label for="f1-ssl">SSL Protected Domain</label>
                              <select name="f1-ssl" id="f1-ssl" class="form-control">
                                <option value="1" selected>Yes</option>
                                <option value="0">No</option>
                              </select>
                              <small class="form-text text-muted">You might not be able to login without an SSL protected Domain: (<a href="https://jakweb.ch/faq/a/253/cannot-login-after-install">FAQ - Login Issues</a>)</small>
                            </div>
                            <div class="form-group">
                              <label for="f1-rewrite">Apache / NGINX Rewrite Enabled</label>
                              <select name="f1-rewrite" id="f1-rewrite" class="form-control">
                                <option value="1">Yes</option>
                                <option value="0" selected>No</option>
                              </select>
                              <small class="form-text text-muted">Make sure your Server is ready before enabling this option: (<a href="https://jakweb.ch/faq/a/145/rewrite-or-pretty-url-s">FAQ - Rewrite</a>)</small>
                            </div>
                          </div>

                          <div class="f1-buttons">
                              <button type="button" class="btn btn-previous">Previous</button>
                              <button type="button" class="btn btn-next" id="checkSRV" style="display:none"><i class="fa fa-chevron-square-right"></i> Check your Server</button>
                              <button type="submit" class="btn btn-submit" id="saveDB"><i class="fa fa-save"></i> Save</button>
                          </div>
                          </form>
                        </fieldset>
                        
                        <fieldset>
                          <h4>Check your Engine</h4>
                          <table class="table table-striped">
                            <tr>
                              <td><strong>What we check</strong></td>
                              <td><strong>Result</strong></td>
                            </tr>
                            <tr>
                              <td>config.php:</td>
                              <td><?php echo $data_file;?></td>
                            </tr>
                            <tr>
                              <td>PHP Version and Safe Mode:</td>
                              <td><?php echo @$result_php?> <?php echo $result_safe;?></td>
                            </tr>
                            <tr>
                              <td valign="top">Folders:</td>
                              <td><?php echo $writecc.$writec;?></td>
                            </tr>
                            <tr>
                              <td>GD Library Support:</td>
                              <td><?php echo $gd_data;?></td>
                            </tr>
                            <tr>
                              <td>cURL Support:</td>
                              <td><?php echo $curl_data;?></td>
                            </tr>
                            <tr>
                              <td>Zlib Library and allow_url_fopen Support (optional):</td>
                              <td><?php echo $zip_data;?></td>
                            </tr>
                            <tr>
                              <td><strong>Important</strong></td>
                              <td>We cannot check every bit on your server, therefore we cannot guarantee if your server meets the minimum requirements with this basic check.</td>
                            </tr>
                          </table>
                          <div class="f1-buttons">
                            <button type="button" class="btn btn-previous">Previous</button>
                            <button type="button" class="btn btn-next"><i class="fa fa-chevron-square-right"></i> Install the Databases</button>
                          </div>
                        </fieldset>

                            <fieldset>
                                <h4>Install Database</h4>
                                <div id="database_installing">
                                  <p class="text-center"><i class="fa fa-database fa-spin fa-5x"></i></p>
                                </div>
                                <div id="database_success" style="display: none">
                                  <div class="alert alert-success">
                                    Batteries fully charged, database installed. Please get on board.
                                  </div>
                                </div>
                                <div id="database_already" style="display: none">
                                  <div class="alert alert-info">
                                    Batteries already full, database has been installed previously. Please get on board.
                                  </div>
                                </div>
                                <div id="database_failure" style="display: none">
                                  <div class="alert alert-danger">
                                    Uh oh, there was a spark in the engine room. Database failure, please try again.
                                  </div>
                                </div>
                                <div class="f1-buttons">
                                    <button type="button" class="btn btn-previous">Previous</button>
                                    <button type="button" class="btn btn-next"><i class="fa fa-chevron-square-right"></i> Get on Board</button>
                                </div>
                            </fieldset>

                            <fieldset>
                                <div id="form-success-signup" style="display: none">
                                  <div class="alert alert-success">
                                    <p><h4>Welcome on board of Cloud Chat 3</h4>CC3 has been installed succesfully, please delete the <strong>install</strong> directory.</p>
                                    <p>You can now login into your <a href="../signup/admin">Administration Panel</a> and of course your <a href="../operator/">Operator</a> panel. Enjoy!!!</p>
                                    <p>In case you have any questions or problems, please check our <a href="https://jakweb.ch/faq">FAQ</a> or <a href="https://jakweb.ch/profile">create a support ticket</a> on our website.<br>Your JAKWEB - Team.</p>
                                  </div>
                                </div>
                                <div id="form-elements-signup">
                                  <h4>Create your Account</h4>
                                  <div id="form-error-signup" style="display: none">
                                  <div class="alert alert-danger">
                                    Some fields are not correct, please fix it.<br>
                                    <span id="error_msg_signup"></span>
                                  </div>
                                </div>
                                <form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="post" class="form-onboard">
                                  <div class="form-group">
                                      <label for="f1-onumber">Order Number / Purchase Code</label>
                                      <input type="text" name="f1-onumber" placeholder="Order Number / Purchase Code" class="f1-onumber form-control" id="f1-onumber" autocomplete="off">
                                  </div>
                                  <div class="form-group">
                                      <label for="f1-envname">Envato Username</label>
                                      <input type="text" name="f1-envname" placeholder="Envato Usernname..." class="f1-envname form-control" id="f1-envname" autocomplete="off">
                                  </div>
                                  <div class="form-group">
                                      <label for="f1-title">Site Title</label>
                                      <input type="text" name="f1-name" placeholder="Cloud Chat 3" class="f1-title form-control" id="f1-title">
                                  </div>
                                  <div class="form-group">
                                      <label for="f1-name">Name</label>
                                      <input type="text" name="f1-name" placeholder="Name..." class="f1-name form-control" id="f1-name">
                                  </div>
                                  <div class="form-group">
                                      <label for="f1-username">Username</label>
                                      <input type="text" name="f1-username" placeholder="Username..." class="f1-username form-control" id="f1-username">
                                  </div>
                                  <div class="form-group">
                                      <label for="f1-email">Email</label>
                                      <input type="text" name="f1-email" placeholder="Email..." class="f1-email form-control" id="f1-email">
                                  </div>
                                  <div class="form-group">
                                    <label for="f1-password">Password</label>
                                    <input type="password" name="f1-password" placeholder="Password..." class="f1-password form-control" id="f1-password">
                                  </div>
                                </div>
                                <div class="f1-buttons">
                                    <button type="button" class="btn btn-previous">Previous</button>
                                    <button type="submit" class="btn btn-submit" id="onBoard"><i class="fa fa-paper-plane"></i> Submit</button>
                                </div>
                                </form>
                            </fieldset>
                      
                      </div>
                    </div>
                </div>

                <footer>
  <p>Copyright 2023 by <a href="https://jakweb.ch">Cloud Chat 3 - JAKWEB</a></p>
</footer>
                    
            </div>
        </div>


        <!-- Javascript -->
        <script src="../js/jquery.js"></script>
        <script src="../js/functions.js"></script>
        <script src="assets/js/jquery.backstretch.min.js"></script>
        <script src="assets/js/scripts.js"></script>
        
        <!--[if lt IE 10]>
            <script src="assets/js/placeholder.js"></script>
        <![endif]-->

    </body>

</html>