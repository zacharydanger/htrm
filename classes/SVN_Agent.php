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
 * 
 * Thanks to Vic <vmc@leftnode.com> for contributing the 'svn update' output parsing.
 */

require_once 'SSH_Agent.php';

class SVN_Agent {
	private $_ssh_agent = null;
	private $_svn_user = null;
	private $_svn_pass = null;
	private $_svn_dir = './';

	public function __construct($svn_user, $svn_pass, $svn_dir = './') {
		$this->_svn_user = escapeshellarg($svn_user);
		$this->_svn_pass = escapeshellarg($svn_pass);
		$this->_svn_dir = escapeshellarg($svn_dir);
	}

	public function setupSSH($host, $user, $pass, $port = 22) {
		$this->_ssh_agent = new SSH_Agent();
		$this->_ssh_agent->connect($host, $user, $pass, $port);
	}

	public function svnStatus() {
		if(true == is_object($this->_ssh_agent)) {
			$command = "svn st -u --show-updates --username " . $this->_svn_user . " --password " . $this->_svn_pass . " " . $this->_svn_dir;
			$results = $this->_ssh_agent->exec($command);
			if(empty($results)) {
				$results = 'No output: Changes were not detected.';
			}
			
			return $results;
		}
	}

	/**
	 * Legacy method from web-based HTRM.
	 */
	public function svnInfo() {
		if(true == is_object($this->_ssh_agent)) {
			$command = "svn info " . $this->_svn_dir;
			return $this->_ssh_agent->exec($command);
		}
	}
	
	/**
	 * Calls update on the remote host.
	 * 
	 * TODO: Cleanup the output for non-HTML output.
	 */
	public function svnUpdate() {
		if(true == is_object($this->_ssh_agent)) {
			$update_command = "svn up --username " . $this->_svn_user . " --password " . $this->_svn_pass . " -rhead " . $this->_svn_dir;
			$update_response = $this->_ssh_agent->exec($update_command);

			return $update_response;

			/* Commented old code from the original web-based HTRM
			$formatted_response = '';
			$outputs = explode("\n", $update_response);
			array_pop($outputs);
			array_pop($outputs); //doing it twice...
			foreach ( $outputs as $op ) {
				$os = explode(' ', str_replace('    ', ' ', $op) );

				$action = trim($os[0]);
				$file_name = trim($os[1]);
				
				$response = NULL;
				$response_type = 'unknown';
				
				switch ( $action ) {
					case 'U': {
						$response = 'File <strong>' . $file_name . '</strong> updated successfully!';
						$response_type = 'updated';
						break;
					}
					
					case 'A': {
						$response = 'File <strong>' . $file_name . '</strong> added and updated successfully!';
						$response_type = 'added';
						break;
					}
					
					case 'D': {
						$response = 'File <strong>' . $file_name . '</strong> deleted successfully!';
						$response_type = 'deleted';
						break;
					}
					
					case 'C': {
						$response = 'File <strong>' . $file_name . '</strong> FAILED to be updated. There was a conflict! Resolve immediately!';
						$response_type = 'conflict';
						break;
					}
					
					case 'M': {
						$response = 'File <strong>' . $file_name . '</strong> merged and updated successfully!';
						$response_type = 'merged';
						break;
					}
					
					default: {
						$response = 'Unknown file <strong>' . $file_name . '</strong>. Perhaps this needs to be added to the repository first. SVN said: ' . $action;
						$response_type = 'unknown';
						break;
					}
				}
				$formatted_response .= '<li class="' . $response_type . '">' . $response . '</li>';
			}
			return $formatted_response;
			//*/
		}
	}
}
?>