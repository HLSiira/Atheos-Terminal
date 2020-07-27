<?php
//FIXME Support of windows, use command 'cd' instead of 'pwd'

/*
    *  PHP Terminal Emulator by Fluidbyte <http://www.fluidbyte.net>
    *
    *  This software is released as-is with no warranty and is complete free
    *  for use, modification and redistribution
    */

require_once('../../common.php');

//////////////////////////////////////////////////////////////////
// Verify Session or Key
//////////////////////////////////////////////////////////////////
Common::checkSession();

//////////////////////////////////////////////////////////////////
// Globals
//////////////////////////////////////////////////////////////////
$project = Common::data("project", "session");

define('PASSWORD', 'terminal');
define('ROOT', Common::getWorkspacePath($project));
define('BLOCKED', 'ssh,telnet');

//////////////////////////////////////////////////////////////////
// Terminal Class
//////////////////////////////////////////////////////////////////
class Terminal {

	////////////////////////////////////////////////////
	// Properties
	////////////////////////////////////////////////////

	public $command = '';
	public $output = '';
	public $directory = '';
	public $command_exec = '';

	////////////////////////////////////////////////////
	// Constructor
	////////////////////////////////////////////////////

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

	////////////////////////////////////////////////////
	// Primary call
	////////////////////////////////////////////////////
	public function process($str) {
		debug($str);
		$cmd = $this->parseCommand($str);
		debug($cmd);
		$output = Common::execute($cmd);
		debug($output);
		return $output;
	}

	////////////////////////////////////////////////////
	// Parse command for special functions, blocks
	////////////////////////////////////////////////////
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

	////////////////////////////////////////////////////
	// Chnage Directory
	////////////////////////////////////////////////////
	public function changeDir($dir) {
		chdir($dir);
		// Store new directory
		$_SESSION['activeDir'][$_SESSION['project']] = exec('pwd');
	}
}

//////////////////////////////////////////////////////////////////
// Processing
//////////////////////////////////////////////////////////////////

$user = Common::data("user", "session");
$command = Common::data('command');

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
	"prompt" => "<span class=\"user\">$user</span>:<span class=\"path\">" . exec('pwd') . "</span>$ "
);

debug($output);

Common::sendJSON("success", $output);



?>