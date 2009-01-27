#!/usr/bin/php
<?php
require_once('config.php');
require_once('classes/SSH_Agent.php');
require_once('classes/SVN_Agent.php');

$argument_count = $argc;
$arguments = $argv;

//kill it if we don't have enough arguments.
if(intval($argument_count) < 3) {
	echo "ERROR: Invalid argument count.\n";
	echo "Correct usage: htrm <repo_path> <revision>\n\n";
	exit;
}

$repository = $arguments[1];
$revision = $arguments[2];

$svnlook_author = 'svnlook author ' . escapeshellcmd($repository) . ' -r ' . $revision;
$svnlook_log = 'svnlook log ' . escapeshellcmd($repository) . ' -r ' . $revision;
$svnlook_dirs = "svnlook dirs-changed $repository -r $revision";

$changed_dirs = array();
exec($svnlook_dirs, $changed_dirs);

$update_indexes = array();

//TODO: make this suck less
echo "...matching triggers...\n";
foreach($TRIGGERS as $i => $t) {
	foreach($changed_dirs as $dir) {
		if(strpos($dir, $t['changed_path']) !== false) {
			echo "trigger match - " . $t['changed_path'] . "\n";
			$update_indexes[] = $i;
		}
	}
}
$update_indexes = array_unique($update_indexes);

foreach($update_indexes as $i) {
	$t = $TRIGGERS[$i];
	$subject = "HTRM: remote checkout - " . $t['user'] . "@" . $t['host'] . ":" . $t['path'];
	$SVN = new SVN_Agent(SVN_USER, SVN_PASS, $t['path']);
	$SVN->setupSSH($t['host'], $t['user'], $t['pass'], $t['port']);
	$output = $SVN->svnUpdate();
	mail(UPDATE_EMAIL, $subject, $output);
}
?>