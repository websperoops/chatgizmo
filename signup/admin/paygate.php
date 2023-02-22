<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.1                   # ||
|| # ----------------------------------------- # ||
|| # Copyright 2022 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// Check if the file is accessed only via index.php if not stop the script from running
if (!defined('JAK_ADMIN_PREVENT_ACCESS')) die('You cannot access this file directly.');

// No access
if (!JAK_USERID || !jak_get_access("p", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) jak_redirect(BASE_URL);

// All the tables we need for this plugin
$errors = $success = array();
// Sub page available
$subpage = false;

// Delete Paygate
if ($page1 == "d") {

    if (isset($page2) && is_numeric($page2)) {


        // Delete the admin
        $jakdb->delete("payment_gateways", ["id" => $page2]);

        // We have deleted the user
        $_SESSION["successmsg"] = $jkl['g16'];
        jak_redirect($_SESSION['LCRedirect']);

    } else {

        // No permission
        $_SESSION["errormsg"] = $jkl['e5'];
        jak_redirect($_SESSION['LCRedirect']);

    }

}

// Edit Paygate
if ($page1 == "e") {

    $errors = array();
    $updatepass = false;

    if (JAK_USERID) {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (empty($_POST['title'])) {
                $errors['e'] = $jkl['e11'];
            }

            if (isset($_POST['paygateid']) && $_POST['paygateid'] != "bank" && empty($_POST['secretkey_one'])) {
                $errors['e1'] = $jkl['e11'];
            }

            if (!in_array($_POST['paygateid'], array("paystack","bank")) && empty($_POST['secretkey_two'])) {
                $errors['e2'] = $jkl['e11'];
            }

            if (isset($_POST['paygateid']) && $_POST['paygateid'] == "bank" && empty($_POST['bank_info'])) {
                $errors['e3'] = $jkl['e11'];
            }

            if (count($errors) == 0) {

                // We update the package
                $result = $jakdb->update("payment_gateways", ["locid" => $_POST['locationid'],
                    "paygateid" => $_POST['paygateid'],
                    "currency" => $_POST['currency'],
                    "title" => $_POST['title'],
                    "secretkey_one" => trim($_POST['secretkey_one']),
                    "secretkey_two" => trim($_POST['secretkey_two']),
                    "emailkey" => trim($_POST["emailkey"]),
                    "bank_info" => trim($_POST["bank_info"]),
                    "active" => trim($_POST['active']),
                    "sandbox" => trim($_POST['sandbox']),
                    "lastedit" => $jakdb->raw("NOW()")], ["id" => $page2]);

                // We say succesful
                $_SESSION["successmsg"] = $jkl['g16'];
                jak_redirect($_SESSION['LCRedirect']);

            // Output the errors
            } else {
                $errors = $errors;
            }

        }

        // Get one package
        $paygate = $jakdb->get("payment_gateways", "*", ["id" => $page2]);

        // Get all locations
        $locations = $jakdb->select("locations", ["id", "title"]);

        // Title and Description
        $SECTION_TITLE = $jkl['g311'];
        $SECTION_DESC = $jkl['g312'];

        // Call the template
        $template = 'editpaygate.php';

        $subpage = true;

    } else {
        // No permission
        $_SESSION["errormsg"] = $jkl['e12'];
        jak_redirect($_SESSION['LCRedirect']);
    }

}

// Create new location
if ($page1 == "n") {

    $errors = array();
    $updatepass = false;

    if (JAK_USERID) {

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (empty($_POST['title'])) {
                $errors['e'] = $jkl['e11'];
            }

            if (isset($_POST['paygateid']) && $_POST['paygateid'] != "bank" && empty($_POST['secretkey_one'])) {
                $errors['e1'] = $jkl['e11'];
            }

            if (!in_array($_POST['paygateid'], array("paystack","bank")) && empty($_POST['secretkey_two'])) {
                $errors['e2'] = $jkl['e11'];
            }

            if (isset($_POST['paygateid']) && $_POST['paygateid'] == "bank" && empty($_POST['bank_info'])) {
                $errors['e3'] = $jkl['e11'];
            }

            if (count($errors) == 0) {

                // We insert the package
                $jakdb->insert("payment_gateways", ["locid" => $_POST['locationid'],
                    "paygateid" => $_POST['paygateid'],
                    "currency" => $_POST['currency'],
                    "title" => $_POST['title'],
                    "secretkey_one" => trim($_POST['secretkey_one']),
                    "secretkey_two" => trim($_POST['secretkey_two']),
                    "emailkey" => trim($_POST["emailkey"]),
                    "bank_info" => trim($_POST["bank_info"]),
                    "active" => trim($_POST['active']),
                    "sandbox" => trim($_POST['sandbox']),
                    "lastedit" => $jakdb->raw("NOW()"),
                    "created" => $jakdb->raw("NOW()")]);

                $lastid = $jakdb->id();

                // We say succesful
                $_SESSION["successmsg"] = $jkl['g16'];
                jak_redirect(JAK_rewrite::jakParseurl('p', 'e', $lastid));

            // Output the errors
            } else {
                $errors = $errors;
            }

        }

        // Get all locations
        $locations = $jakdb->select("locations", ["id", "title"]);

        // Title and Description
        $SECTION_TITLE = $jkl['g309'];
        $SECTION_DESC = $jkl['g310'];

        // Call the template
        $template = 'addpaygate.php';

        $subpage = true;

    } else {
        // User with ID1 cannot be deleted, as well yourself.
        $_SESSION["errormsg"] = $jkl['e12'];
        jak_redirect($_SESSION['LCRedirect']);
    }

}

if (!$subpage) {

    // Let's go on with the script
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $jkp = $_POST;

        if (empty($_POST['currency'])) {
            $errors['e'] = $jkl['e11'];
        }

        if (empty($_POST['trialdays']) || !is_numeric($_POST['trialdays'])) {
            $errors['e1'] = $jkl['e15'];
        }

        if (count($errors) == 0) {

            // Update the fields
            $jakdb->update("settings", ["used_value" => $_POST['currency']], ["varname" => "currency"]);
            $jakdb->update("settings", ["used_value" => $_POST['trialdays']], ["varname" => "trialdays"]);
            $jakdb->update("settings", ["used_value" => $_POST['addops']], ["varname" => "addops"]);
            $jakdb->update("settings", ["used_value" => $_POST['exchangekey']], ["varname" => "exchangekey"]);
            // $jakdb->update("settings", ["used_value" => $_POST['subdowngrade']], ["varname" => "subdowngrade"]);
            $jakdb->update("settings", ["used_value" => $_POST['jak_stripe_secret']], ["varname" => "stripe_secret_key"]);
            $jakdb->update("settings", ["used_value" => $_POST['jak_stripe_publish']], ["varname" => "stripe_publish_key"]);
            $jakdb->update("settings", ["used_value" => $_POST['jak_paypal_client']], ["varname" => "paypal_client"]);
            $jakdb->update("settings", ["used_value" => $_POST['jak_paypal_secret']], ["varname" => "paypal_secret"]);
            $jakdb->update("settings", ["used_value" => $_POST['jak_sandbox']], ["varname" => "sandbox_mode"]);
            $jakdb->update("settings", ["used_value" => $_POST['jak_yookassa_id']], ["varname" => "yookassa_id"]);
            $jakdb->update("settings", ["used_value" => $_POST['jak_yookassa_secret']], ["varname" => "yookassa_secret"]);
            $jakdb->update("settings", ["used_value" => $_POST['jak_paystack_secret']], ["varname" => "paystack_secret"]);
            // $jakdb->update("settings", ["used_value" => $_POST['jak_twoco']], ["varname" => "twoco"]);
            // $jakdb->update("settings", ["used_value" => $_POST['jak_twoco_secret']], ["varname" => "twoco_secret"]);
            // $jakdb->update("settings", ["used_value" => $_POST['jak_authorize_id']], ["varname" => "authorize_id"]);
            // $jakdb->update("settings", ["used_value" => $_POST['jak_authorize_key']], ["varname" => "authorize_key"]);
            $jakdb->update("settings", ["used_value" => $_POST['bank_info']], ["varname" => "bank_info"]);

            $_SESSION["successmsg"] = $jkl['g16'];
            jak_redirect($_SESSION['LCRedirect']);

        } else {
            $errors = $errors;
        }
        
    }

    // Get all packages
    $gateways = $jakdb->select("payment_gateways", ["id", "locid", "paygateid", "title", "currency", "sandbox", "active", "lastedit", "created"]);

    // Title and Description
    $SECTION_TITLE = $jkl['g106'];
    $SECTION_DESC = $jkl['g107'];

    // Call the template
    $template = 'paygate.php';
}

?>