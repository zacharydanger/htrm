<?php
//Configure subversion credentials for this repository.
define('SVN_USER', 'user');
define('SVN_PASS', 'pass');

//Configure an email address output will be mailed to
define('UPDATE_EMAIL', 'email_address');

# Triggers are paths that when changed should update a remote working copy.
$TRIGGERS = array();

#Example Trigger definition:
$TRIGGERS[1] = array(
		//this is the path that when changed will trigger the remote connection
		'changed_path' => 'branches/release', 
		//remote host to connect to
		'host' => 'foobar.org',
		//path to working copy on remote host
		'path' => '/path/to/working/copy',
		//user name to use to connect to remote host
		'user' => 'username',
		//password for the user above on the remote host
		'pass' => 'password',
		//SSH port on the remote host
		'port' => 22
	);
	
#A second trigger would be defined the same way using $TRIGGER[2] = ...
?>
