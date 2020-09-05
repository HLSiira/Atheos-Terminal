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

	amplify.subscribe('system.loadExtra', () => atheos.Terminal.init());

	var self = null;

	atheos.Terminal = {

		path: atheos.path + 'plugins/Terminal/',
		activeDir: null,
		controller: null,

		command: null,
		screen: null,
		prompt: null,
		output: null,


		// Command History
		command_history: [],
		command_counter: -1,
		history_counter: -1,

		init: function() {
			self = this;
			self.controller = self.path + 'terminal.php';
			oX('#terminal', true).on('mousedown, mouseup', self.checkFocus);
			// oX('#command input', true).on('change, keydown, paste, input', self.listener);
			oX('#terminal_input', true).on('change, keydown, paste, input', self.listener);
		},

		open: function() {
			self.activeDir = self.activeDir || atheos.project.current.path;
			var callback = function() {
				// self.command = oX('#command input');
				self.command = oX('#terminal_input');
				self.screen = oX('#terminal');
				self.output = oX('#terminal #output');
				self.prompt = oX('#prompt');
				self.command.focus();
			};

			atheos.modal.load(800, {
				target: 'Terminal',
				action: 'open',
				path: self.activeDir,
				callback
			});
			atheos.common.hideOverlay();
		},

		mouseDown: false,
		checkFocus: function(e) {
			if (e.type === 'mousedown') {
				self.mouseDown = true;
				setTimeout(function() {
					if (!self.mouseDown) {
						self.command.focus();
					}
				}, 200);
			} else {
				self.mouseDown = false;
			}
		},

		listener: function(e) {
			var code = (e.keyCode ? e.keyCode : e.which);
			// var command = self.command.value();
			var command = self.command.text();
			switch (code) {
				// Enter key, process command
				case 13:
					if (command == 'clear') {
						self.clear();
					} else {
						self.command_history[++self.command_counter] = command;
						self.history_counter = self.command_counter;
						self.execute(command);
						self.command.text('Processing...');
						self.command.focus();
					}
					break;
					// Up arrow, reverse history
				case 38:
					if (self.history_counter >= 0) {
						self.command.text(self.command_history[self.history_counter--]);
					}
					break;
					// Down arrow, forward history
				case 40:
					if (self.history_counter <= self.command_counter) {
						self.command.text(self.command_history[++self.history_counter]);
					}
					break;
			}
		},

		execute: function(command) {
			echo({
				url: self.controller,
				data: {
					command: command
				},
				success: function(reply) {
					self.command.empty();
					self.command.focus();

					let data = reply.data;

					switch (data) {
						case '[CLEAR]':
							self.clear();
							break;
						case '[EXIT]':
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
							break;
						default:
							self.display(command, data);
					}

					self.prompt.html(reply.prompt);


				}
			});
		},

		display: function(command, data) {
			self.output.append('<div class="command">' + self.prompt.html() + command + '</div><pre class="data">' + data + '</pre>');
			var element = oX('#terminal .container').el;
			element.scrollTop = element.scrollHeight - element.clientHeight;
		},

		clear: function() {
			self.output.html('');
			self.command.empty();
		}

	};
})(this);