<?php
//////////////////////////////////////////////////////////////////////////////80
// Atheos Terminal
//////////////////////////////////////////////////////////////////////////////80
// Copyright (c) 2020 Liam Siira (liam@siira.io), distributed as-is and without
// warranty under the MIT License. See [root]/license.md for more.
// This information must remain intact.
//////////////////////////////////////////////////////////////////////////////80
// Copyright (c) 2013 Codiad & Kent Safranski
// Source: https://github.com/Fluidbyte/Codiad-Terminal
//////////////////////////////////////////////////////////////////////////////80
require_once('../../common.php');

//////////////////////////////////////////////////////////////////////////////80
// Verify Session or Key
//////////////////////////////////////////////////////////////////////////////80
Common::checkSession();

//////////////////////////////////////////////////////////////////////////////80
// Globals
//////////////////////////////////////////////////////////////////////////////80
$project = SESSION("project");

define('PASSWORD', 'terminal');
define('ROOT', Common::getWorkspacePath($project));
define('BLOCKED', 'ssh,telnet');

//////////////////////////////////////////////////////////////////////////////80
// Terminal Class
//////////////////////////////////////////////////////////////////////////////80

class Terminal {

	//////////////////////////////////////////////////////////////////////////80
	// Constructor
	//////////////////////////////////////////////////////////////////////////80
	public function __construct() {
		if (!isset($_SESSION['activeDir']) || !isset($_SESSION['activeDir'][$_SESSION['project']]) || empty($_SESSION['activeDir'][$_SESSION['project']])) {
			if (ROOT === '') {
				$output = Common::execute('pwd');
				$_SESSION['activeDir'] = array([$_SESSION['project']] => $output);
			} else {
				$this->changeDir(ROOT);
			}
		} else {
			$this->changeDir($_SESSION['activeDir'][$_SESSION['project']]);
		}
	}

	//////////////////////////////////////////////////////////////////////////80
	// Primary call
	//////////////////////////////////////////////////////////////////////////80
	public function process($str) {
		$cmd = $this->parseCommand($str);
		$output = Common::execute($cmd);
		return $output;
	}

	//////////////////////////////////////////////////////////////////////////80
	// Parse command for special functions, blocks
	//////////////////////////////////////////////////////////////////////////80
	public function parseCommand($str) {

		// Explode command
		$command_parts = explode(" ", $str);

		// Handle 'cd' command
		if (in_array('cd', $command_parts)) {
			$cd_key = array_search('cd', $command_parts);
			$cd_key++;
			
			$dir = $command_parts[$cd_key];
			
			$this->changeDir($dir);
			// Remove from command
			$str = str_replace("cd $dir", "", $str);
		}

		// Replace text editors with cat
		$editors = array('vim', 'vi', 'nano');
		$str = preg_replace('/^('.join('|', $editors).')/', 'cat', trim($str));

		// Handle blocked commands
		$blocked = explode(',', BLOCKED);
		if (in_array($command_parts[0], $blocked)) {
			$str = 'echo ERROR: Command not allowed';
		}

		// Update exec command
		return $str . " 2>&1";
	}

	//////////////////////////////////////////////////////////////////////////80
	// Chnage Directory
	//////////////////////////////////////////////////////////////////////////80
	public function changeDir($dir) {
		chdir($dir);
		// Store new directory
		$_SESSION['activeDir'][$_SESSION['project']] = exec('pwd');
	}
}

//////////////////////////////////////////////////////////////////////////////80
// Processing
//////////////////////////////////////////////////////////////////////////////80

$activeUser = SESSION("user");
$command = POST('command');

if (strtolower($command === 'exit')) {
	$_SESSION['term_auth'] = false;
	$output = '[EXIT]';

} else if (!isset($_SESSION['term_auth']) || $_SESSION['term_auth'] !== true) {
	if ($command === PASSWORD) {
		$_SESSION['term_auth'] = true;
		$output = '[AUTHENTICATED]';
	} else {
		$output = 'Enter Password:';
	}

} else {
	$Terminal = new Terminal();
	$output = '';
	$command = explode("&&", $command);
	foreach ($command as $c) {
		$output .= $Terminal->process($c);
	}
}

$output = array(
	"data" => htmlentities($output),
	"dir" => htmlentities(exec('pwd')),
	"prompt" => "<span class=\"user\">$activeUser</span>:<span class=\"path\">" . exec('pwd') . "</span>$ "
);

Common::sendJSON("success", $output);

?>