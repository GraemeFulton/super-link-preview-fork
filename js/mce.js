//TinyMCE Integration
(function() {
	var plugin_mce = 'superlinkpreview';
	var plugin_title = 'Super Link Preview';
	var plugin_shortcode = 'link-preview';
	
	tinymce.create('tinymce.plugins.' + plugin_mce, {
		init: function(editor, url) {
			editor.addButton(plugin_mce, {
				title: 'Link with Preview',
				image: url.replace('/js', '/images') + '/icon.png',
				onclick: function() {
	
					editor.windowManager.open( {
						title: 'Insert Link with Preview',
						body: [
						{
							type: 'textbox',
							name: 'url',
							label: 'Website URL'
						},
						{
							type: 'checkbox',
							name: 'forceshot',
							label: 'Force screenshot'
						},
						],
						onsubmit: function( e ) {
							editor.insertContent( '[' + plugin_shortcode + ' url="' + e.data.url + '"' + (e.data.forceshot ? ' forceshot="true"' : '') + ']');
						}

					});
				}
			});
		},
		createControl: function(n, cm) {
			return null;
		},
		getInfo: function() {
			return {
				longname: plugin_title,
				author: 'Daniele Perilli',
				authorurl: 'https://www.behance.net/danieleperilli'
			};
		}
	});
	tinymce.PluginManager.add(plugin_mce, tinymce.plugins.superlinkpreview);
})();