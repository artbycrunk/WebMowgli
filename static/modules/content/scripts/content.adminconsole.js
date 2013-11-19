
var width_,height_,current_instance_id,fullscreen_mode=false,post_fix_string='-content_clone';
var current_id;

//var admin_resource_url = site_url+'static/admin/resources';
var img_more_path=admin_resource_url+'/scripts/tiny_mce/plugins/Custom/show_more_less_buttons/more.png'
var img_less_path=admin_resource_url+'/scripts/tiny_mce/plugins/Custom/show_more_less_buttons/less.png'
var save_btn_clicked=false;

$(document).ready(function(){
	/**
  * This file includes the JS code that will run on the website pages when user is logged in as administrator.
  * Includes in-place editor and Top sliding panel.
  */
	// @Lloyd Comment: WTF is this for ??
	//Top sliding panel
	$("#toppanel-container ul#button-list li a").click(function(){
		if($(this).attr('panel_status')=='closed') {
			$("div#toppanel div#panel").slideDown("slow");
			$(this).removeClass('open');
			$(this).addClass('close');
			$(this).attr('panel_status','opened');
		} // Expand Panel
		else {
			$("div#toppanel div#panel").slideUp("slow");
			$(this).removeClass('close');
			$(this).addClass('open');
			$(this).attr('panel_status','closed');
		} // Collapse Panel
	});

	//In-place editor
	//$('.border-container').hide();
	//$('#inplace-edit-btn').hide();
	
	//	$("div.editable-content").mouseenter(function() {
	//$("div.wm-editable-content").mouseenter(function() {
		// .position() uses position relative to the offset parent,
		//var pos = $(this).position();

		// .outerWidth() takes into account border and padding.
		//var width = $(this).outerWidth();

		//$(this).append('<div id="inplace-edit-btn" style="display: none;"><i class="icon-pencil icon-white"></i> Edit</div>');

		//$("#inplace-edit-btn").show()
		//var x = $(this).offset().left;
		//var y = $(this).offset().top;
		
		//$(".border-container").css('top', (y-10)).css('left', (x-10));
		//$(".border-container").width($(this).outerWidth()+10);
		//if(($(window).width()) < ($(this).outerWidth()+10)) {$(".border-container").width($(window).width());}
		//$(".border-container").height($(this).outerHeight(true)+10).show();
		
		//$("#inplace-edit-btn").css('top', 5).css('right', 5).show();
		
		
	//}).mouseleave(function(){
		//$('.border-container').hide();
		//$('#inplace-edit-btn').hide();
		//$('#inplace-edit-btn').remove();
	//});
		
	var edcontent_id = 0;
	
	$(".wm-editable-content").mouseenter(function() {
	
	var container = $(this);
	var containerID = this.id;
	//if($(".border-container").css("display","none"))
	//{
		//edcontent_id = container.id;
	//}
	wm.debug('containerID=' +containerID+"edcontent_id="+edcontent_id);
	if( containerID != edcontent_id )
	{
		edcontent_id = containerID;
		
		var containerMain = container;
		
		$(this).css("float", ($(this).children(":first").css("float")));
		//var containerChild = $(this+" :first-child");
		
		if(/*((container.outerWidth()) <= 26) &&*/ (container.children().length == 1))
		{
			container = container.children(":first");
			wm.debug('container is First child');
			if(container.is("a"))
			{
				var container_new = container.children(":first");
				if(container_new.length > 0)
				{
					container = container_new;
				}
				wm.debug('container is <a> child');
			}
			if(container.is("p"))
			{
				if (container.children().length <= 1)
				{
					container = container.children(":first");
				}
				wm.debug('container is <p> child');
			}
			if(container.is("object"))
			{
				container = container.find("embed");
			}
		}else{
			wm.debug('container is Main child');
		}		
		
		var x = container.offset().left;
		var y = container.offset().top;
		
		$(".border-container").css('top', (y-10)).css('left', (x-10));
		$(".border-container").width(container.outerWidth()+10);
		if(($(window).width()) < (container.outerWidth()+10)) {$(".border-container").width($(window).width());}
		$(".border-container").height(container.outerHeight(true)+10);
		
		if ($(".border-container").height() == 10)
		{
			$(".border-container").height(containerMain.parent().outerHeight(true)+10);
		}
		
		$(".border-container").show();
		
		wm.debug('height'+(container.outerHeight(true)));
		
		$("#inplace-edit-btn").css('top', 5).css('right', 5);
		
		$("#inplace-edit-btn").attr('container', containerID);
		
		$(".border-container").css("float", (container.children(":first").css("float")));
	}	
		$('.border-container').show();
		//$('#inplace-edit-btn').show();
	
		//transition effect
		//$('#mask').show();
	
	}).mouseleave(function(){
		//$('.border-container').hide();
		//$('#inplace-edit-btn').hide();
		
	});
	
	//$('#mask').hover(function() {
		//$('#mask').hide();
		//$('.border-container').hide();
	//})
	//$("body").hover(function() {
	//	$('.border-container').hide();
	//	$('#inplace-edit-btn').hide();
	//	$('#mask').hide();
	//});
	
	/*
	$('body').find('div').each(function (i) {
		$(this).mouseenter(function() {
			wm.debug('border-container exit');
			$('.border-container').hide();
		});
	});
	*/
	//$("#border-left").hover(function() {
	//	$('.border-container').hide();
	//});
	
	//$(".border-container").mouseenter(function() {
	//	wm.debug('border-container enter');
		//$(".border-container").css("pointer-events", "none");
		//$('.border-container').show();
		//$('#inplace-edit-btn').show();
	//})
	
	$(".border-container").mouseleave(function(){
		wm.debug('border-container exit');
		//$(".border-container").css("pointer-events", "visible");
		$('.border-container').hide();
		//$('#inplace-edit-btn').hide();
	});
	
	//$('#inplace-edit-btn').hover(function() {
	//		$('.border-container').show();
	//	    $('#inplace-edit-btn').show();
	//	}, function(){
	//		$('.border-container').hide();
	//	    $('#inplace-edit-btn').hide();
	//	});
	
	$('#inplace-edit-btn').live('click', function() {

		wm.debug('Content Editor : edit button clicked');
		show_mask();

		current_id = $("#inplace-edit-btn").attr('container');
		
		var currdiv = $("#"+current_id);
		
		//var currdiv = $(this).parent().get(0);
		//$('#inplace-edit-btn').remove();
		//current_id = currdiv.id;
		
		wm.debug('current_id: '+ currdiv);
		current_instance_id = current_id;
		var newId = current_instance_id + post_fix_string;
		if($('#'+newId).length == 0){
			//$(currdiv).after('<div id="'+newId+'" style="display:none">'+$(currdiv).html()+'</div>')
			$('body').prepend('<div id="'+newId+'" style="display:none">'+$(currdiv).html()+'</div>')
		}
		//alert(instance.id+','+$(instance).innerHTML)
		var elem = $(".border-container");
		var widthOffset = elem.css("width");
		var heightOffset = elem.css("height");
		width_= widthOffset, height_ = heightOffset;
		tinyMCE.execCommand("mceAddControl", true, newId);

	});


}); //END ready

function myCustomOnInit() {
/*	var elem=$('.editable-content');
		width_=elem.width(),height_=elem.height();
		          //d('w: ' + width_);//d('h: ' + height_);*/
}

// Mask
//select all the a tag with name equal to modal
function show_mask(){
	//d('show_mask:');

	if($('#mask').length==0){
		$('body').prepend('<div id="mask"></div>');
		//if mask is clicked
		$('#mask').bind('click', function() {
			//d('hide mask:');
			//d('tinyMCE.activeEditor:'+tinyMCE.activeEditor);
			d('mask .. save_content >');
			pre_save_content(tinyMCE.activeEditor);
			closeTinyMCE(tinyMCE.activeEditor);

		});
	}

	//Get the screen height and width
	var maskHeight = $(document).height();
	var maskWidth = $(window).width();

	//Set height and width to mask to fill up the whole screen
	$('#mask').css({
		'width':maskWidth,
		'height':maskHeight
	});

	//transition effect
	$('#mask').fadeTo(1000,0.25);

	//Get the window height and width
	var winH = $(window).height();
	var winW = $(window).width();

}

tinyMCE.init({
	script_url : '',/* preloaded*/

	mode : "none",
	theme : "advanced",
	plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",

	// Theme options
	theme_advanced_buttons1 :
	//        "mySave,|,new,|,bold,italic,underline,strikethrough,|,undo,redo,|,justifyleft,justifycenter,justifyright,justifyfull,|,forecolor,backcolor,|,styleselect,fontselect,fontsizeselect,|,preview,code,|,fullscreen,|,show_more_less_buttons",
	"mySave,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,forecolor,backcolor,|,fontselect,fontsizeselect,|,preview,code,|,fullscreen,|,show_more_less_buttons",

	/*"save,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",*/
	/**/
	theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,link,unlink,anchor,image,cleanup,help,|,insertdate,inserttime",
	theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl",
	theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak",

	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : true,

	forced_root_block : false,
	force_p_newlines : false,
	remove_linebreaks : false,
	force_br_newlines : true,
	remove_trailing_nbsp : false,
	verify_html : false,

	// Example content CSS (should be your site CSS)
//	content_css : "",

	// Drop lists for link/image/media/template dialogs
	//        template_external_list_url : "lists/template_list.js",
	//        external_link_list_url : "lists/link_list.js",
	//        external_image_list_url : "lists/image_list.js",
	//        media_external_list_url : "lists/media_list.js",

	oninit : myCustomOnInit,
	setup : function(ed) {
		ed.onPostRender.add(function(ed, cm) {
			//Hide adv toolbars
			//d('onPostRender:'+ed.id);
			//d('fullscreen_mode:'+fullscreen_mode);

			if(!ed.getParam('fullscreen_is_enabled')){
				show_hide_toolbars('hide',ed.id);
				if(width_>465){
					$('iframe#'+ed.id+'_ifr').css('width',width_);
				}
				$('iframe#'+ed.id+'_ifr').css('height',height_);
				//$('iframe#'+ed.id+'_ifr').css('position','absolute');
				
				var elem = $(".border-container");
				var topOffset = elem.css("top");
				var leftOffset = elem.css("left");
				
				//$('iframe#'+ed.id+'_ifr').css('top',topOffset);
				//$('iframe#'+ed.id+'_ifr').css('left',leftOffset);
				
				//d('w: ' + width_);d('h: ' + height_);
				var tinyMCE_instance=$('span#'+ed.id+'_parent');//+' table#'+ed.id+'_tbl'
				tinyMCE_instance.addClass('tinyMCE-baseClass');
				var elem = $('#'+current_instance_id);
				
				var offset_= elem.offset();
				
				//alert(ed.id);
				//tinyMCE_instance.css('position','absolute');
				
				//tinyMCE_instance.css('top',topOffset);
				//tinyMCE_instance.css('left',leftOffset);
				
				tinyMCE_instance.css("top", topOffset);
				tinyMCE_instance.css("left", leftOffset);
				tinyMCE_instance.css("z-index", '10000');
				
				//tinyMCE_instance.offset({
					//top: topOffset,
					//left: leftOffset
					//top: offset_.top,
					//left: offset_.left
				//});
				tinyMCE_instance.show();
			//d('t: ' + offset_.top);d('l: ' +  offset_.left);
			//$('span#'+ed.id+'_parent').show(1500)//css('display','block')

			}/*fullscreen_mode*/
		}),
		// Add a custom button
		ed.addButton('mySave', {
			title : 'Save',
			image : admin_resource_url+'/scripts/tiny_mce/plugins/Custom/mySave/disk.png',
			onclick : function() {

				save_btn_clicked=true;
				// Add you own code to execute something on click
				d('save_btn_clicked>'+save_btn_clicked+', current_instance_id:'+current_instance_id);
				save_content(ed);
			}
		}),
		ed.addButton('show_more_less_buttons', {
			title : 'Show more buttons',
			image : img_more_path,
			onclick : function() {
				// Add you own code to execute something on click
				var a='a#'+ed.id+'_show_more_less_buttons img';
				if($(a).attr('src')==img_more_path){
					$(a).attr('src',img_less_path);
					show_hide_toolbars('show',ed.id);
				}
				else{
					$(a).attr('src',img_more_path);
					show_hide_toolbars('hide',ed.id);
				}
			}
		})
	}
});


function show_hide_toolbars(mode,id){
	//d('show_hide_toolbars:'+mode+','+id);
	//span#1-content_clone_parent table#1-content_clone_tbl div#1-content_clone_toolbargroup table#1-content_clone_toolbar
	//
	//d(a+'2'+','+$('span#1-content_clone_parent table#1-content_clone_tbl div#1-content_clone_toolbargroup table#1-content_clone_toolbar2').attr('class'))
	var a='span#'+id+'_parent'+' table#'+id+'_tbl'+' div#'+id+'_toolbargroup'+' table#'+id+'_toolbar';


	if(mode=='hide'){
		$(a+'2').addClass(mode);
		$(a+'3').addClass(mode);
		$(a+'4').addClass(mode);
	}
	else{
		$(a+'2').removeClass('hide');
		$(a+'3').removeClass('hide');
		$(a+'4').removeClass('hide');
	}
	d($(a+'2').attr('class'));
}


var content_box,updated_content;
function closeTinyMCE(ed){
	tinyMCE.execCommand("mceRemoveControl", true, (ed.id));
	$('#mask').hide();

	d( 'tinymce instance closed - ' + ed.id );
}

function pre_save_content(ed){
	if(confirm('Would you like to save the changes you have made?')){
		save_content(ed);
	}
}

function save_content(ed){
	//if(confirm('Would you like to save the changes you have made?')){

	d('save_content>id:'+current_id+', ed: ' + ed.id+','+tinyMCE.activeEditor.id);
	//alert('#'+(ed.id).substr(0,(ed.id).length-post_fix_string.length)+'\n>>'+$('#'+(ed.id).substr(0,(ed.id).length-post_fix_string.length)).attr('content_id')+'\n\n>>'+ed.getContent());
	//alert(encodeURI(ed.getContent()))

	content_box='#'+(ed.id).substr(0,(ed.id).length-post_fix_string.length);
	//updated_content=(ed.getContent()).substr(3,(ed.getContent()).length-7);//remove <p> tags
	updated_content=ed.getContent();
	d('>'+ed.id);
	$.ajax({
		url: $('#'+current_instance_id).attr('post_url'),
		type: 'POST',
		//data:'id='+(ed.id).substr(0,(ed.id).length-post_fix_string.length)+'&content='+encodeURI(updated_content),
		data:'id='+ current_id +'&content='+encodeURIComponent(updated_content),
		success: function(data, textStatus, XMLHttpRequest) {
			//                                alert(data);
			d("content saved successfully");
			makeChanges(ed);

		//closeTinyMCE(ed);

		},
		error:  function(XMLHttpRequest, textStatus, errorThrown) {
			//$('.result').html(textStatus);
			//Show error message above the area.
			if(errorThrown != 'Unauthorized')
			{
				alert('ERROR: A problem occured. Could not save data');
			}
		}
	});
	//} else {
	if(save_btn_clicked){
	//Dont save changes. Let user continue editing
	} else {
	//Dont save changes. UNLOAD TinyMCE instance.
	//                        tinyMCE.execCommand("mceRemoveControl", true, (ed.id));
	//                        $('#mask').hide();

	//closeTinyMCE(ed);
	}
	//Reset
	save_btn_clicked=false;
// }
}



function makeChanges(ed){
	$(content_box).html(updated_content);
	$(content_box+post_fix_string).html(updated_content);
	$(content_box+post_fix_string).remove();
//tinyMCE.get(ed.id).hide();

}