<?php
$GLOBALS["clientID"] = "";
$GLOBALS["secretID"] = "";
$GLOBALS["botToken"] = "";

$GLOBALS["scopes"] = "identify+guilds+guilds.members.read";
$GLOBALS["redirectURI"] = "http://bsfr-tournament.hyldrazolxy.fr/login";

$GLOBALS["baseURI"] = "https://discord.com";

$GLOBALS["rolesAuthorized"] = array(
    "938517352562188308", // Moderator
    "980071927144120360", // Coordinator
    "938517574495375411", // Coach
    "938517544103456808", // Analyst
    "980072184892506133", // Captain
    "938517604237201428", // Team WC 2023
    "938517625263251528"  // Candidate
);

$GLOBAL["accuracy"]     = array("True ACC",     "Standard ACC",      "High ACC");
$GLOBAL["midspeed"]     = array("Low Midspeed", "Standard Midspeed", "High Midspeed");
$GLOBAL["technical"]    = array("Low Tech",     "Standard Tech",     "High Tech");
$GLOBAL["speed"]        = array("Low Speed",    "Standard Speed",    "High Speed");