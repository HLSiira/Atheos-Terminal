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

$path = POST("path");
$activeUser = SESSION("user");

switch ($action) {

	//////////////////////////////////////////////////////////////////////////80
	// Probe File Contents
	//////////////////////////////////////////////////////////////////////////80
	case 'open': ?>
		<label class="title"><i class="fas fa-terminal"></i>Terminal</label>
		<div id="terminal">
			<div class="scanline"></div>
			<div class="container">
				<div id="output"></div>
				<div id="command">
					<span id="prompt"><?php echo("<span class=\"user\">$activeUser</span>:<span class=\"path\">$path</span>$")  ?></span>
					<span id="terminal_input" contenteditable="true"></span>
				</div>
			</div>
		</div>
		<?php
		break;

	//////////////////////////////////////////////////////////////////////////80
	// Default: Invalid Action
	//////////////////////////////////////////////////////////////////////////80
	default:
		Common::sendJSON("E401i");
		break;
}