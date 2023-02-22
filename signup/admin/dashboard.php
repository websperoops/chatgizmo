<?php

/*===============================================*\
|| ############################################# ||
|| # JAKWEB.CH / Version 3.1.1                 # ||
|| # ----------------------------------------- # ||
|| # Copyright 2023 JAKWEB All Rights Reserved # ||
|| ############################################# ||
\*===============================================*/

// Check if the file is accessed only via index.php if not stop the script from running
if (!defined('JAK_ADMIN_PREVENT_ACCESS')) die('You cannot access this file directly.');

// No access
if (!JAK_USERID) jak_redirect(BASE_URL);

// Reset some vars if no access
$clients = $advclients = $income = $locations = $unconfirmed = $lc3install = "-";

// User has client access
if (jak_get_access("c", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) {

	// total clients
	$clients = $jakdb->count("users");

	// total unconfirmed clients
	$unconfirmed = $jakdb->count("users", ["confirm[!]" => 0]);

	// total advanced clients
	$lc3clients = $jakdb->count("advaccess", ["lc3hd3" => 1]);

	// Open Live Chat 3 installations
	$hd3clients = $jakdb->count("advaccess", ["lc3hd3" => 2]);

	// Get the last 6 clients
	$lastclients = $jakdb->select("users", ["id", "username", "email", "signup"], ["ORDER" => ["id" => "DESC"], "LIMIT" => 6]);

	// Get new tickets
	$tickets = $jakdb->select("support_tickets", ["id", "username", "subject", "content", "sent"], ["status" => 3, "ORDER" => ["sent" => "DESC"], "LIMIT" => 10]);

	if (JAK_MAX_CLIENTS != 0) {
		$totalu = $jakdb->count("users", ["active" => 1]);
	}

}

// User has location access
if (jak_get_access("l", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) {

	// total clients
	$locations = $jakdb->count("locations");

}

// User has payment access
if (jak_get_access("p", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) {

	// total advanced clients
	$income = 0;
	$income = $jakdb->sum("subscriptions", ["amount"], ["AND" => ["freeplan" => 0, "success" => 1]]);
	$income = ($income ? round($income, 2) : 0);

	// Get his payments
	$subscriptions = $jakdb->select("subscriptions", ["[>]users" => ["userid" => "opid"], "[>]packages" => ["packageid" => "id"]], ["subscriptions.id", "subscriptions.amount", "subscriptions.paidfor", "subscriptions.paidhow", "subscriptions.paidwhen", "subscriptions.paidtill", "subscriptions.freeplan", "subscriptions.success", "users.id(userid)", "packages.title"], ["ORDER" => ["subscriptions.id" => "DESC"], "LIMIT" => 10]);
}

// Title and Description
$SECTION_TITLE = $jkl['g18'];
$SECTION_DESC = "";

// Call the template
$template = 'dashboard.php';

?>