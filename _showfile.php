<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 2.6                   # ||
|| # ----------------------------------------- # ||
|| # Copyright 2021 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

if (!file_exists('config.php')) die('[_showfile.php] config.php not exist');
require_once 'config.php';

$thedata = explode(":#:", jak_encrypt_decrypt($_GET["i"], false));

jak_load_external_file($thedata[0], $thedata[1], $thedata[2]);

?>