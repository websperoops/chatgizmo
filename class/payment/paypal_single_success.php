<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 1.2                   # ||
|| # ----------------------------------------- # ||
|| # Copyright 2021 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

use JAKWEB\JAKsql;

if (!file_exists('../../config.php')) die('[paypal_single_success.php] config.php not exist.');
require_once '../../config.php';
require_once '../../'.JAK_OPERATOR_LOC.'/include/admin.function.php';
require_once __DIR__.'/Checkout-PHP-SDK-develop/vendor/autoload.php';
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;

?>
<!DOCTYPE html>
<html>
    <head>
    
    </head>
    <body>
    <?php
        
        $token = $_GET['token'];
        $payer_id = $_GET['PayerID'];
        $redirect = $_GET['redirect'];

        $ppgetURL = New JAK_rewrite($redirect);

        // We are not using apache so take the ugly urls
        $ppurl = $ppgetURL->jakGetseg(0);
        $ppurl1 = $ppgetURL->jakGetseg(1);
        $ppurl2 = $ppgetURL->jakGetseg(2);
        $ppurl3 = $ppgetURL->jakGetseg(3);
        $ppurl4 = $ppgetURL->jakGetseg(4);
        $ppurl5 = $ppgetURL->jakGetseg(5);
        $pppage = ($ppurl ? jak_url_input_filter($ppurl) : '');
        $pppage1 = ($ppurl1 ? jak_url_input_filter($ppurl1) : '');
        $pppage2 = ($ppurl2 ? jak_url_input_filter($ppurl2) : '');
        $pppage3 = ($ppurl3 ? jak_url_input_filter($ppurl3) : '');
        $pppage4 = ($ppurl4 ? jak_url_input_filter($ppurl4) : '');
        $pppage5 = ($ppurl5 ? jak_url_input_filter($ppurl5) : '');

        // now we need the rest of the URL http://clouddesk3:8888/class/payment/paypal_single_success.php?redirect=http://clouddesk3:8888/operator/extend/success/MTojOjI6IzoxOiM6OiM6MjEuMDc6IzowLjAyNjAwNDAwIDE2MjQxMDE5Mzk6IzpUaGUgQ29tcGxldGUtMjojOjA6Izox&token=94013146RB3859909&PayerID=NYAPD38JQ29S2
        $pppage3d = base64_url_decode($pppage5);

        // userid, packageid, paygateid, coupon, amount, time, planid (title, packageid, interval, week, month, year), subscribed, different currency
        $custom = explode(":#:", $pppage3d);

        // Now if we have multi site we have fully automated process
        if (!empty(JAKDB_MAIN_NAME) && JAK_MAIN_LOC && JAK_MAIN_OP) {
    
            // Database connection to the main site
            $jakdb1 = new JAKsql([
                // required
                'database_type' => JAKDB_MAIN_DBTYPE,
                'database_name' => JAKDB_MAIN_NAME,
                'server' => JAKDB_MAIN_HOST,
                'username' => JAKDB_MAIN_USER,
                'password' => JAKDB_MAIN_PASS,
                'charset' => 'utf8',
                'port' => JAKDB_MAIN_PORT,
                'prefix' => JAKDB_MAIN_PREFIX,
                             
                // [optional] driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
                'option' => [PDO::ATTR_CASE => PDO::CASE_NATURAL]
                ]);

            $paga = $jakdb1->get("payment_gateways", ["secretkey_one", "secretkey_two", "emailkey", "sandbox"], ["AND" => ["id" => $custom[2], "active" => 1]]);

        }

        $clientId = $paga["secretkey_one"];
        $clientSecret = $paga["secretkey_two"];
        if($paga["sandbox"]){
            $environment = new SandboxEnvironment($clientId, $clientSecret);
        }
        else{
            $encvironment = new ProductionEnvironment($clientId, $clientSecret);
        }
        $client = new PayPalHttpClient($environment);
        $request = new OrdersCaptureRequest($token);
        $request->prefer('return=representation');
        try {
            // Call API with your client and get a response for your call
            $response = $client->execute($request);
            
            // If call returns body in response, you can get the deserialized version from the result attribute of the response
            header("Location: ".$redirect);
            exit();
        }catch (HttpException $ex) {
            echo $ex->statusCode;
            print_r($ex->getMessage());
        }
    ?>
    <h2>Please wait while we process your payment...</h2>
    </body>
</html>