<?php
/*
 * This file is part of HTRM.
 * 
 * HTRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * HTRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with HTRM.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * Zachary Danger Campbell <zacharydangercampbell@gmail.com>
 */

class SSH_Agent {
	private $_host = null;
	private $_user = null;
	private $_pass = null;

	private $_connection = null;

	public function __construct() {
		//exit if the ssh2 lib isn't installed
		if(false == function_exists('ssh2_connect')) {
			exit("Function ssh2_connect doesn't exist!");
		}
	}

	public function connect($host, $user, $pass, $port = DEFAULT_SSH_PORT) {
		if(empty($host) || empty($user) || empty($pass) || empty($port)) {
			throw new Exception("One or more SSH credentials missing");
		}
		if($this->_connection = ssh2_connect($host, $port)) {
			if(false == ssh2_auth_password($this->_connection, $user, $pass)) {
				throw new Exception("SSH Authorization error!");	
			}
		} else {
			throw new Exception("SSH failed to connect.");
		}
	}

	public function exec($command) {
		$command_terminator = "__COMMAND_FINISHED__" . sha1(time().rand(0,9));
		$command .= ' ; echo "' . $command_terminator . '"';
		$time_start = time();
	
		$stream = ssh2_exec($this->_connection, $command);
		stream_set_blocking($stream, true);
		$data = null;
		//this loop tries reading the buffer unless it runs over a timeout limit
		while( true ) {
			$buffer = fread($stream, 4096);
			if(strpos($buffer, $command_terminator) !== false) {
				break;
			}
			$data .= $buffer;
			if( (time() - $time_start) > 20 ) {
				echo "failure! timeout over 20 seconds\n";
				break;
			}
		}
		return $data;
	}
}
?>