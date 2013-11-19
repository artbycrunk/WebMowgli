//-----------------------------------------------------------------------//
//-------------------- Namespace : editor --------------------------------//
//-----------------------------------------------------------------------//

wm.editor = (function( window, undefined ){

	var obj = {};

	/**
	 * Initialize Admin Panel Block
	 **/
	
	obj.init = function (is_saveButton){
	
		wm.debug('Initializing TinyMce');
		
		var saveButton = ( is_saveButton == true ) ? "save, |," : '';

		var mce_theme = "advanced";
		var mce_mode = "specific_textareas";

		var mce_onchange_callback = "mceSaveToTextArea";
		var mce_init_instance_callback = "myCustomInitInstance";

		var mce_skin = "o2k7"; //  default
		var mce_skin_variant = "silver";

		tinyMCE.init({

			editor_selector : "tinymce_simple",

			theme : mce_theme,
			mode : mce_mode,

			plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

			theme_advanced_buttons1 : saveButton + "undo,redo, |, bold, italic, underline, strikethrough, |, bullist, numlist, |,forecolor, backcolor, |, formatselect,fontselect",
			theme_advanced_buttons2: "",
			theme_advanced_buttons3: "",
			theme_advanced_buttons4: "",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",

			onchange_callback : mce_onchange_callback,
			init_instance_callback : mce_init_instance_callback,

			// Skin options
			skin : mce_skin,
			skin_variant : mce_skin_variant
		});

		/*tinyMCE.init({

			editor_selector : "tinymce_advanced",

			theme : mce_theme,
			mode : mce_mode,

			plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

			// Theme options
			theme_advanced_buttons1 : saveButton + "newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect",
			theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
			theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
			theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak",

			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			//theme_advanced_statusbar_location : "bottom",

			onchange_callback : mce_onchange_callback,
			init_instance_callback : mce_init_instance_callback,

			// Skin options
			skin : mce_skin,
			skin_variant : mce_skin_variant
		});

		tinyMCE.init({

			editor_selector : "tinymce_standard",

			theme : mce_theme,
			mode : mce_mode,

			plugins : "autolink,lists,spellchecker,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

			theme_advanced_buttons1 : saveButton + "undo,redo, |, bold, italic, underline, strikethrough, |, bullist, numlist, |,forecolor, backcolor, |, formatselect,fontselect",
			theme_advanced_buttons2: "cut, copy, paste, |, outdent,indent, |, justifyleft,justifycenter,justifyright,justifyfull, |, link, unlink, |, image, media, |, blockquote, |, cleanup, spellchecker, |, code, fullscreen",
			theme_advanced_buttons3: "tablecontrols",
			theme_advanced_buttons4: "",

			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",

			onchange_callback : mce_onchange_callback,
			init_instance_callback : mce_init_instance_callback,

			// Skin options
			skin : mce_skin,
			skin_variant : mce_skin_variant
		});*/
	}
	
	obj.CustomInitInstance = function (inst){
		//	inst.setContent("");
		wm.debug("in myCustomInitInstance @ tinymce.functions.jquery.js");
	}
	
	obj.mceSaveToTextArea = function (inst){

		tinyMCE.triggerSave();
		wm.debug("in mceSaveToTextArea @ tinymce.functions.jquery.js");
	}
	
	return obj;

})( window );