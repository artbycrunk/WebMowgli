function tinymce_init( is_saveButton ) {

	wm.debug('Initializing TinyMce');

	// BUG: @Lloyd :
	// OBSERVATION : the MCE loads correctly if the 'editor_selector' is the last one defined in this document
	// Eg. if editor_selector = tinymce_standard is the last one defined in this document
	// then --> if any view file has tinymce_standard in menitoned, ONLY then will mce load correctly for all types.
	// if tinymce_standard is NOT requested, and any other type is requested, the issue of advanced.fontdefault,  advanced.block . . .etc occurs

	// ------------------------------------------

	// Bug: @Lloyd :
	// Observation : do following steps
	//	- open dashboard
	//	- click manage posts
	//	- click add posts
	//	- click manage posts
	//	- click add posts
	//	- type in editor
	//	- click out
	//	- Error thrown ( NS_ERROR_UNEXPECTED: Component returned failure code: 0x8000ffff (NS_ERROR_UNEXPECTED) [nsIDOMHTMLDocument.implementation] )
	//
	// Possible solutions : http://www.tinymce.com/forum/viewtopic.php?pid=22977
	//

	// load save button if param passed
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
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,

		onchange_callback : mce_onchange_callback,
		init_instance_callback : mce_init_instance_callback,

		// Skin options
		skin : mce_skin,
		skin_variant : mce_skin_variant
	});

	tinyMCE.init({

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
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,

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
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,

		onchange_callback : mce_onchange_callback,
		init_instance_callback : mce_init_instance_callback,

		// Skin options
		skin : mce_skin,
		skin_variant : mce_skin_variant
	});

}

function myCustomInitInstance(inst) {

//	inst.setContent("");
	wm.debug("in myCustomInitInstance @ tinymce.functions.jquery.js");
}

// // Depricated due to clash with some other module level functions
// // refer mceSaveToTextArea() for original function
//function submitHandler(inst){
//
//	wm.debug("in submitHandler @ tinymce.functions.jquery.js");
//
//	tinyMCE.triggerSave();
//}

function mceSaveToTextArea(inst){

	tinyMCE.triggerSave();

	wm.debug("in mceSaveToTextArea @ tinymce.functions.jquery.js");
}