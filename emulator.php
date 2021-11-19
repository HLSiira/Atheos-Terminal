<?php
//////////////////////////////////////////////////////////////////////////////80
// Atheos Terminal
//////////////////////////////////////////////////////////////////////////////80
// Copyright (c) 2020 Liam Siira (liam@siira.io), distributed as-is and without
// warranty under the MIT License. See [root]/docs/LICENSE.md for more.
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

$path = SESSION("project");
$user = SESSION("user");

?>
<!doctype html>

<head>
	<meta charset="utf-8">
	<title>Terminal</title>
	<link rel="stylesheet" href="assets/standalone.css">
	<link rel="stylesheet" href="assets/reset.css">
	<link rel="stylesheet" href="screen.css">
</head>

<body>


	<div id="terminal">
		<div class="scanline"></div>
		<div class="container">
			<div id="output"></div>
			<div id="command">
				<span id="prompt"><?php echo("<span class=\"user\">$user</span>:<span class=\"path\">$path</span>$") ?></span>
				<span id="terminal_input" contenteditable="true"></span>
			</div>
		</div>
	</div>

	<script src="../../modules/echo.js"></script>
	<script src="../../modules/onyx.js"></script>
	<script src="assets/standalone.js"></script>

</body>
</html>