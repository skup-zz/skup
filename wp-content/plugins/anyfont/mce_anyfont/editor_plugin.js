(function() {

    tinymce.create('tinymce.plugins.AnyFontPlugin', {
		/**
		 * Initializes the plugin
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('mceAnyFont', function() {
				var title = document.getElementById('title').value;
				ed.windowManager.open({
					file : url + '/dialog.php?text='+escape(ed.selection.getContent({format : 'text'})),
					width : 400,
					height : 145,
					inline : 1
				}, {
					plugin_url : url,
					postTitle: title
				});
			});

			// Register button
			ed.addButton('anyfont', {
				title : "Insert text using AnyFont styles",
				cmd : 'mceAnyFont',
				image : url + '/insert-text.gif'
			});

			// Add a node change handler, selects the button in the UI when a image is selected
// 			ed.onNodeChange.add(function(ed, cm, n) {
// 				cm.setActive('anyfont', n.nodeName == 'IMG');
// 			});
		},


		/**
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'AnyFont plugin',
				author : 'Ryan Peel',
				authorurl : 'http://2amlife.com',
				infourl : 'http://2amlife.com/projects/anyfont',
				version : "0.2"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('anyfont', tinymce.plugins.AnyFontPlugin);
})();
