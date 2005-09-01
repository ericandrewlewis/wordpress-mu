/* Import plugin specific language pack */
tinyMCE.importPluginLanguagePack('wordpress', '');

function TinyMCE_wordpress_initInstance(inst) {
	if (!tinyMCE.settings['wordpress_skip_plugin_css'])
		tinyMCE.importCSS(inst.getDoc(), tinyMCE.baseURL + "/plugins/wordpress/wordpress.css");
}

function TinyMCE_wordpress_getControlHTML(control_name) {
    switch (control_name) {
        case "wordpress":
            return '<img id="{$editor_id}_wordpress_more" src="{$pluginurl}/images/more.gif" title="More..." width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mcewordpressmore\');" /><img id="{$editor_id}_wordpress_page" src="{$pluginurl}/images/page.gif" title="...Page..." width="20" height="20" class="mceButtonNormal" onmouseover="tinyMCE.switchClass(this,\'mceButtonOver\');" onmouseout="tinyMCE.restoreClass(this);" onmousedown="tinyMCE.restoreAndSwitchClass(this,\'mceButtonDown\');tinyMCE.execInstanceCommand(\'{$editor_id}\',\'mcewordpresspage\');" />';
    }

    return "";
}

function TinyMCE_wordpress_parseAttributes(attribute_string) {
	var attributeName = "";
	var attributeValue = "";
	var withInName;
	var withInValue;
	var attributes = new Array();
	var whiteSpaceRegExp = new RegExp('^[ \n\r\t]+', 'g');

	if (attribute_string == null || attribute_string.length < 2)
		return null;

	withInName = withInValue = false;

	for (var i=0; i<attribute_string.length; i++) {
		var chr = attribute_string.charAt(i);

		if ((chr == '"' || chr == "'") && !withInValue)
			withInValue = true;
		else if ((chr == '"' || chr == "'") && withInValue) {
			withInValue = false;

			var pos = attributeName.lastIndexOf(' ');
			if (pos != -1)
				attributeName = attributeName.substring(pos+1);

			attributes[attributeName.toLowerCase()] = attributeValue.substring(1).toLowerCase();

			attributeName = "";
			attributeValue = "";
		} else if (!whiteSpaceRegExp.test(chr) && !withInName && !withInValue)
			withInName = true;

		if (chr == '=' && withInName)
			withInName = false;

		if (withInName)
			attributeName += chr;

		if (withInValue)
			attributeValue += chr;
	}

	return attributes;
}

function TinyMCE_wordpress_execCommand(editor_id, element, command, user_interface, value) {
	function getAttrib(elm, name) {
		return elm.getAttribute(name) ? elm.getAttribute(name) : "";
	}

    // Handle commands
    switch (command) {
			case "mcewordpressmore":
				var name = "";
				var template = new Array();
				var inst = tinyMCE.getInstanceById(editor_id);
				var focusElm = inst.getFocusElement();
	
				// Is selection a image
				if (focusElm != null && focusElm.nodeName.toLowerCase() == "img") {
					name = getAttrib(focusElm, 'name');
	
					if (name != 'mce_plugin_wordpress_more') // Not a wordpress
						return true;
	
					action = "update";
				}
	
				html = ''
	      	+ '<img src="' + (tinyMCE.getParam("theme_href") + "/images/spacer.gif") + '" '
					+ ' width="100%" height="10px" '
					+ 'alt="More..." title="More..." class="mce_plugin_wordpress_more" name="mce_plugin_wordpress_more" />';
	      tinyMCE.execCommand("mceInsertContent",true,html);
				tinyMCE.selectedInstance.repaint();
				return true;
			case "mcewordpresspage":
				var name = "";
				var template = new Array();
				var inst = tinyMCE.getInstanceById(editor_id);
				var focusElm = inst.getFocusElement();
	
				// Is selection a image
				if (focusElm != null && focusElm.nodeName.toLowerCase() == "img") {
					name = getAttrib(focusElm, 'name');
	
					if (name != 'mce_plugin_wordpress_page') // Not a wordpress
						return true;
	
					action = "update";
				}
	
				html = ''
	      	+ '<img src="' + (tinyMCE.getParam("theme_href") + "/images/spacer.gif") + '" '
					+ ' width="100%" height="10px" '
					+ 'alt="More..." title="More..." class="mce_plugin_wordpress_page" name="mce_plugin_wordpress_page" />';
	      tinyMCE.execCommand("mceInsertContent",true,html);
				tinyMCE.selectedInstance.repaint();
				return true;
   }

   // Pass to next handler in chain
   return false;
}

function TinyMCE_wordpress_cleanup(type, content) {
	switch (type) {
	
		case "insert_to_editor":
			var startPos = 0;

			// Parse all <!--more--> tags and replace them with images
			while ((startPos = content.indexOf('<!--more-->', startPos)) != -1) {
				// Insert image
				var contentAfter = content.substring(startPos + 11);
				content = content.substring(0, startPos);
	      content += '<img src="' + (tinyMCE.getParam("theme_href") + "/images/spacer.gif") + '" ';
				content += ' width="100%" height="10px" ';
				content += 'alt="More..." title="More..." class="mce_plugin_wordpress_more" name="mce_plugin_wordpress_more" />';
				content += contentAfter;

				startPos++;
			}
			var startPos = 0;

			// Parse all <!--page--> tags and replace them with images
			while ((startPos = content.indexOf('<!--nextpage-->', startPos)) != -1) {
				// Insert image
				var contentAfter = content.substring(startPos + 15);
				content = content.substring(0, startPos);
	      content += '<img src="' + (tinyMCE.getParam("theme_href") + "/images/spacer.gif") + '" ';
				content += ' width="100%" height="10px" ';
				content += 'alt="...Next Page..." title="...Next Page..." class="mce_plugin_wordpress_page" name="mce_plugin_wordpress_page" />';
				content += contentAfter;

				startPos++;
			}
			break;

		case "get_from_editor":
			// Parse all img tags and replace them with <!--more-->
			var startPos = -1;
			while ((startPos = content.indexOf('<img', startPos+1)) != -1) {
				var endPos = content.indexOf('/>', startPos);
				var attribs = TinyMCE_wordpress_parseAttributes(content.substring(startPos + 4, endPos));

				if (attribs['name'] == "mce_plugin_wordpress_more") {
					endPos += 2;
	
					var embedHTML = '<!--more-->';
	
					// Insert embed/object chunk
					chunkBefore = content.substring(0, startPos);
					chunkAfter = content.substring(endPos);
					content = chunkBefore + embedHTML + chunkAfter;
				}
				if (attribs['name'] == "mce_plugin_wordpress_page") {
					endPos += 2;
	
					var embedHTML = '<!--nextpage-->';
	
					// Insert embed/object chunk
					chunkBefore = content.substring(0, startPos);
					chunkAfter = content.substring(endPos);
					content = chunkBefore + embedHTML + chunkAfter;
				}
			}
			break;
	}

	// Pass through to next handler in chain
	return content;
}

function TinyMCE_wordpress_handleNodeChange(editor_id, node, undo_index, undo_levels, visual_aid, any_selection) {
	function getAttrib(elm, name) {
		return elm.getAttribute(name) ? elm.getAttribute(name) : "";
	}

	tinyMCE.switchClassSticky(editor_id + '_wordpress_more', 'mceButtonNormal');
	tinyMCE.switchClassSticky(editor_id + '_wordpress_page', 'mceButtonNormal');

	if (node == null)
		return;

	do {
		if (node.nodeName.toLowerCase() == "img" && getAttrib(node, 'name').indexOf('mce_plugin_wordpress_more') == 0)
			tinyMCE.switchClassSticky(editor_id + '_wordpress_more', 'mceButtonSelected');
		if (node.nodeName.toLowerCase() == "img" && getAttrib(node, 'name').indexOf('mce_plugin_wordpress_page') == 0)
			tinyMCE.switchClassSticky(editor_id + '_wordpress_page', 'mceButtonSelected');
	} while ((node = node.parentNode));

	return true;
}