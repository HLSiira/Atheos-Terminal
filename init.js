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

(function(global) {

	var atheos = global.atheos,
		amplify = global.amplify;

	amplify.subscribe('system.loadExtra', () => atheos.terminal.init());

	var self = null;

	atheos.terminal = {

		path: atheos.path + 'plugins/Terminal/',
		terminal: null,

		command: null,
		screen: null,
		output: null,

		// Command History
		command_history: [],
		command_counter: -1,
		history_counter: -1,

		init: function() {
			self = this;
			self.terminal = self.path + 'terminal.php';
		},

		open: function() {
			var callback = function() {
				self.command = oX('#command input');
				self.screen = oX('#terminal');
				self.screen.on('mousedown, mouseup', self.checkFocus);
				self.output = oX('#terminal>#output');

				self.command.focus();
				self.command.on('change, keydown, paste, input', self.listener);
			};

			// atheos.modal.load(800, this.path + 'dialog.php');
			atheos.modal.load(800, atheos.dialog, {
				target: 'Terminal',
				action: 'open',
				path: this.path,
				callback
			});
			atheos.common.hideOverlay();
		},
		
		mouseDown: false,
		checkFocus: function(e) {
			if(e.type === 'mousedown') {
				self.mouseDown = true;
				setTimeout(function() {
					if(!self.mouseDown) {
						self.command.focus();
					}
				}, 200);
			} else {
				self.mouseDown = false;
			}
		},

		listener: function(e) {
			var code = (e.keyCode ? e.keyCode : e.which);
			var command = self.command.value();
			switch (code) {
				// Enter key, process command
				case 13:
					if (command == 'clear') {
						self.clear();
					} else {
						self.command_history[++self.command_counter] = command;
						self.history_counter = self.command_counter;
						self.execute();
						self.command.value('Processing...');
						self.command.focus();
					}
					break;
					// Up arrow, reverse history
				case 38:
					if (self.history_counter >= 0) {
						self.command(self.command_history[self.history_counter--]);
					}
					break;
					// Down arrow, forward history
				case 40:
					if (self.history_counter <= self.command_counter) {
						self.command(self.command_history[++self.history_counter]);
					}
					break;
			}
		},

		execute: function() {
			var command = self.command.value();
			echo({
				url: self.terminal,
				data: {
					command: command
				},
				success: function(data) {
					self.command.value('');
					self.command.focus();
					switch (data) {
						case '[CLEAR]':
							self.clear();
							break;
						case '[CLOSED]':
							self.clear();
							self.execute();
							window.parent.codiad.modal.unload();
							break;
						case '[AUTHENTICATED]':
							self.command_history = [];
							self.command_counter = -1;
							self.history_counter = -1;
							self.clear();
							break;
						case 'Enter Password:':
							self.clear();
							self.display('Authentication Required', data);
							self.command.css({
								'color': '#333'
							});
							break;
						default:
							self.display(command, data);
					}
				}
			});
		},

		display: function(command, data) {
			self.output.append('<pre class="command">' + command + '</pre><pre class="data">' + data + '</pre>');
			// self.screen.scrollTop(self.output.height());
		},



		clear: function() {
			self.output.html('');
			self.command.val('');
		}

	};
})(this);