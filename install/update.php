<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.1.1                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2023 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

if (!file_exists('../config.php')) die('[update.php] config.php not found');
require_once '../config.php';

/* NO CHANGES FROM HERE */
if (!file_exists('../class/class.jaklic.php')) die('It looks like the boat has been reported as missing.');

// Set successfully to zero
$succesfully = 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>

        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Cloud Chat 3 - Update Wizard</title>

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
                        <h1>Cloud Chat 3 <strong>Update</strong> Wizard</h1>
                        <div class="description">
                            <p>
                                This will guide you through the update for Cloud Chat 3. Make sure to follow the steps carefully.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="row justify-content-center">
                    <div class="col-sm-10 form-box">
                      <div class="f1">

                        <h3>Ready for new features?</h3>
                        <p>Update your Cloud Chat 3 to receive new features and fix some bugs.</p>
                        <div class="f1-steps">
                          <div class="f1-progress">
                              <div class="f1-progress-line" data-now-value="15" data-number-of-steps="4" style="width: 15%;"></div>
                          </div>
                            <div class="f1-step active">
                              <div class="f1-step-icon"><i class="far fa-hdd"></i></div>
                                <p>backup</p>
                            </div>
                          <div class="f1-step">
                            <div class="f1-step-icon"><i class="far fa-hdd"></i></div>
                            <p>backup</p>
                          </div>
                          <div class="f1-step">
                            <div class="f1-step-icon"><i class="far fa-hdd"></i></div>
                            <p>backup</p>
                          </div>
                            <div class="f1-step">
                            <div class="f1-step-icon"><i class="fa fa-database"></i></div>
                            <p>update</p>
                          </div>
                        </div>

                        <fieldset>
                          <h4>Files, Database Backup</h4>
                          <p>Do you have an up to date backup of your files and database? This is important!<br>Before you run the database update, have you replaced all necessary old files and folders with the new ones?</p>
                          <div class="f1-buttons">
                            <button type="button" class="btn btn-next">Next</button>
                          </div>
                        </fieldset>
                        
                        <fieldset>
                          <h4>Custom Modificaitons?</h4>
                          <p>Do you have any custom modifications? Did you backup the files and your database?</p>
                          <div class="f1-buttons">
                            <button type="button" class="btn btn-previous">Previous</button>
                            <button type="button" class="btn btn-next">Next</button>
                          </div>
                        </fieldset>

                        <fieldset>
                          <h4>Backup?</h4>
                          <p>Do you have a backup? Yes, ok hit the Database Update Button and follow the on screen instruction.</p>
                          <div class="f1-buttons">
                            <button type="button" class="btn btn-previous">Previous</button>
                            <button type="button" class="btn btn-next">Database Update</button>
                          </div>
                        </fieldset>

                        <fieldset>
                          <h4>Install Database</h4>
                                <div id="database_updating">
                                  <p class="text-center"><i class="fa fa-database fa-spin fa-5x"></i></p>
                                </div>
                                <div id="database_success" style="display: none">
                                  <div class="alert alert-success">
                                    <p>Batteries recharged! CC3 has been updated succesfully, please delete the <strong>install</strong> directory and login into your <a href="../<?php echo JAK_OPERATOR_LOC;?>/">operator</a> panel with your super operator account.</p>
                                    <p><strong>!!! IMPORTANT !!!</strong> You will first need to go to your maintance section and enter your license number and license username (usually your envato username), after verifying the license you will need to go to your settings and re-enter all necessary details.</p>
                                  </div>
                                </div>
                                <div id="database_already" style="display: none">
                                  <div class="alert alert-info">
                                    Batteries already recharged, database has been updated previously and is up to date. Please delete the <strong>install</strong> directory and login into your <a href="../<?php echo JAK_OPERATOR_LOC;?>/">operator</a> panel. Enjoy!!!
                                  </div>
                                </div>
                                <div id="database_failure" style="display: none">
                                  <div class="alert alert-danger">
                                    Uh oh, there was a spark in the engine room. Database failure, please try again.
                                  </div>
                                </div>
                                <div class="f1-buttons">
                                    <button type="button" class="btn btn-previous">Previous</button>
                                </div>
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
        <script src="assets/js/scripts_u.js"></script>
        
        <!--[if lt IE 10]>
            <script src="assets/js/placeholder.js"></script>
        <![endif]-->

    </body>

</html>