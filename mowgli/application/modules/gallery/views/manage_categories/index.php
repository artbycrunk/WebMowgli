<script language="javascript">var module_resource_uri='{module:resource}';</script>

<script language="javascript" src="{module:resource}/scripts/gallery.adminconsole.js"></script>
<!--<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
jquery-ui.min.js
-->
<!--<script src="{module:resource}/scripts/jquery-ui-1.8.16.custom.min.js"></script>
-->
<!-- prettyloader.js && prettyloader.css COMMENTED BY LLOYD
<link rel="stylesheet" href="{admin:resource}/scripts/prettyLoader/css/prettyLoader.css" type="text/css" media="screen" charset="utf-8" />
<script src="{admin:resource}/scripts/prettyLoader/js/jquery.prettyLoader.js" type="text/javascript" charset="utf-8"></script>
-->


<!-- <script type="text/javascript" language="javascript" src="http://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js"></script> -->
<!--<script type="text/javascript" language="javascript" src="{admin:resource}/scripts/swfobject.js"></script>-->
<?php echo load_resources('js', 'swfobject'); ?>


<!--<script type="text/javascript" language="javascript" src="{admin:resource}/scripts/uploadify_ci/jquery.uploadify.v2.1.0.min.js"></script>-->
<?php echo load_resources('js', 'uploadify'); ?>
<!--<link rel="stylesheet" type="text/css" href="{admin:resource}/scripts/uploadify_ci/uploadify.css" />-->
<?php echo load_resources('css', 'uploadify'); ?>


<script type="text/javascript">
	var globalResourcePath='{admin:resource}';
	var preLoadImagesList = [
		'/scripts/prettyLoader/images/prettyLoader/prettyLoader.png',
		'/scripts/prettyLoader/images/prettyLoader/prettyLoader.gif',
		'/scripts/prettyLoader/images/prettyLoader/ajax-loader.gif'
	];


	//Uploadify
	var arg_upload_target_name,arg_upload_target_id; // Set when 'Upload' button is clicked during runtime. set in uploadImages()

</script>


<!-- From Uploadify View -->
<script type="text/javascript">

	/////////////////////////Uploadify
	var t;

	var AdminConsoleUploadifyController = {
		FLAG_onAllComplete:false,
		count: {
			tot_uploaded_count:'',
			tot_upload_queue_items:''
		},
		uploadify_configuration :	{
			/* DEFAULTS
		'controller':'<?php echo site_url('admin/gallery/upload_save_image'); ?>',
		'dump_path':'/ENCUBE_ADMIN/upload/',
		'fileExt': '*.jpg;*.jpeg;*.gif;*.png;*.tif',
		'buttonImg'   : '/uploadify/button.jpg',
		'sizeLimit'   : 102400,//Has to be lower than upload_max_filesize
		'width'       : 11111111111*/
		},

		init: function() {
		},

		getConfiguration: function() {
		},

		setConfiguration: function() {
		},

		startUpload: function(upload_target_name,upload_target_id) {
			var self = this;
			self.count.tot_uploaded_count=0;
			self.count.tot_upload_queue_items=$('#upload_queue').children('div.uploadifyQueueItem').size();
			wm.debug('[startUpload] self.count.tot_upload_queue_items:'+self.count.tot_upload_queue_items)
			self.uploadify_configuration.upload_target_name = upload_target_name;
			self.uploadify_configuration.upload_target_id = upload_target_id;


		},


		init_uploadify: function() {
			var self = this;
			wm.debug('inside init_uploadify> '+self.uploadify_configuration.queueID)
			$("#upload").uploadify({
				/*				uploader: '<?php //echo site_url(); ?>js/uploadify/uploadify.swf',
				script: '<?php //echo site_url(); ?>js/uploadify/uploadify.php',//The files present in {admin:resource}/scripts/uploadify
				cancelImg: '<?php //echo site_url(); ?>js/uploadify/cancel.png',*/
				uploader: '{admin:resource}/scripts/uploadify_ci/uploadify.swf',
				script: '{admin:resource}/scripts/uploadify_ci/uploadify.php',//The files present in {admin:resource}/scripts/uploadify
				cancelImg: '{admin:resource}/scripts/uploadify_ci/cancel.png',
				folder: self.uploadify_configuration.dump_path,
				fileExt	  : self.uploadify_configuration.fileExt,
				buttonImg   : self.uploadify_configuration.buttonImg,
				rollover    : self.uploadify_configuration.rollover,
				sizeLimit   : self.uploadify_configuration.sizeLimit,
				width       : self.uploadify_configuration.width,
				height	  : self.uploadify_configuration.height,
				queueID	  : self.uploadify_configuration.queueID,
				scriptAccess: self.uploadify_configuration.scriptAccess,
				multi: self.uploadify_configuration.multi,
				'onError' : function (a, b, c, d) {
					//wm.debug(a+','+b+','+c+','+d)
					if (d.status == 404)
						alert('Could not find upload script.');
					else if (d.type === "HTTP")
						alert('error '+d.type+": "+d.status);
					else if (d.type ==="File Size")
						alert(c.name+' '+d.type+' Limit: '+Math.round(d.sizeLimit/1024)+'KB');
					else
						alert('error '+d.type+": "+d.text);
				},
				'onOpen'      : function(event,ID,fileObj) {
					//$('#uploader_message').text('The upload is beginning for ' + fileObj.name);
					$('#upload_it_button').hide();
					$('#cancel_all_button').show();
					$('#clear_queue_button').hide();
				},
				'onComplete'   : function (event, queueID, fileObj, response, data) {
					//Post response back to controller
					//var response_= jQuery.parseJSON(response)
					//response_.cat_id="1";
					self.count.tot_uploaded_count++;
					//if(self.uploadify_configuration.upload_target_name.length>)

					$('#upload_status').html('');

					$('<span/>', {
						'class': 'status_container',
						html: ''
					}).appendTo('#upload_status');

					$('<span/>', {
						'class': 'cat_name',
						html: 'Uploading to '+ self.uploadify_configuration.upload_target_name
					}).appendTo('#upload_status span.status_container');

					$('<span/>', {
						'class': 'uploaded_count',
						html: ' ('+self.count.tot_uploaded_count+'/'+self.count.tot_upload_queue_items+')'
					}).appendTo('#upload_status span.status_container');



					//wm.debug('onComplete:'+event+','+queueID+','+fileObj+','+response+','+data)

					var cat_id_=self.uploadify_configuration.upload_target_id;
					$.post(self.uploadify_configuration.controller,{filearray: response,cat_id:cat_id_},function(info){
						wm.debug('info:'+info+',self.FLAG_onAllComplete:'+self.FLAG_onAllComplete)
						var img_item={};
						$.each((info.data), function(index, value) {
							//alert(index + ': ' + value);
							$("#uploaded_list").append(generateThumbnail(value));  //Add response returned by controller
							if(self.count.tot_uploaded_count==self.count.tot_upload_queue_items){
								//if(self.FLAG_onAllComplete){
								wm.debug('callback: '+(typeof self.callback) )

								if (typeof self.callback == 'function') { // make sure the callback is a function
									wm.debug('within1')
									self.callback(self.uploadify_configuration.upload_target_id); // brings the scope to the callback
									wm.debug('within2')
								}

								$('#cancel_all_button').hide();
								wm.debug('onAllComplete> uploadifyClearQueue')
								$('#upload').uploadifyClearQueue();

							}
						});
						self.FLAG_onAllComplete=false;
					});
					/*										for(item in info.data){
								wm.debug(item)//img_item=jQuery.parseJSON(item)
							}
							t=img_item;
					 */

				},
				'onAllComplete'   : function (event, queueID, fileObj, response, data) {
					//Notification
					//alert('all')
					wm.debug('onAllComplete> '+FLAG_onAllComplete);
					self.FLAG_onAllComplete=true;
				},
				'onSelectOnce' : function(event,data) {
					$('#upload_it_button').show();
					$('#cancel_all_button').hide();
					$('#clear_queue_button').show();

					//alert(data.filesSelected + ' files were selected for upload.');
				}
			});
		},
		callback: null
	};

	function generateThumbnail(img){
		//background: no-repeat scroll 50% 50% #EEEEEE;
		return '<div class="uploaded_image_container"><img src="'+img.uri_thumb+'" title="'+img.name+'"  style="" /></div>';//width:30px;height:30px;
	}


</script>
<style>
	#upload_box {
		display:none;
		width:100%;
		height: 290px;
		margin: 0 auto;
		padding: 20px 0 10px;
		position:relative;

		/*Only FF*/
		box-shadow: 0px 2px 6px #999999 inset;
		-moz-box-shadow: 0px 2px 6px #999999 inset;
	}

	#upload_box #upload_box_status {
		background-color: #FFFFFF;
		border: 1px solid #E3E3E3;
		display: block;
		font-size: 13px;
		left: 40%;
		margin: 0 auto;
		padding: 10px;
		position: absolute;
		top: 0;
		width: auto;
		z-index: 50;
	}

	#upload_box #upload_box_close_btn {

		display: block;
		float: right;
		font-size: 20px;
		height: 26px;
		margin: 0 17px 0 0;
		width: 30px;
	}

	#upload_form, #upload_queue, #uploaded_list, #upload_status {
		display:block;
	}

	#upload_form, #upload_status{
		height:45px;
	}

	#upload_status {
		height: 45px;
		margin: 10px auto;
		width: 90%;
	}

	#upload_status span.status_container {
		display: block;
		font-weight: bold;
		margin: 0 auto;
		padding: 18px 0 0;
		text-align: center;
	}

	#upload_status span.status_container span.cat_name{
		float:left;
	}
	#upload_status span.status_container span.uploaded_count{
		float:right;
	}

	#upload_form {
		margin: 10px auto;
		width: 90%;
	}

	#upload_form form {
		float: left;
		margin: 5px 0;
		width: 45px;
	}


	#clear_queue_button,#cancel_all_button,#upload_it_button {
		float: right;
	}


	#uploaded_list, #upload_queue{
		border: 2px solid #D0D0D0;
		height: 200px;
		clear: both;
		margin: 0 auto 10px;
		overflow: auto;
		width: 90%;
	}

	#upload_queue {
		padding: 0 0 5px;
	}




	#upload_queue .uploadifyQueueItem {
		margin: 6px auto 0;
		padding: 8px;
		width: 245px;
		color: #34287B;
		background-color: #C2BEE7;
		border: 1px solid #A091F9;
	}

	#upload_queue .uploadifyQueueItem .fileName{
		width:215px;
		overflow:hidden;


	}


	#uploaded_list{
	}

	#uploaded_list .uploaded_image_container {
		border: 1px solid #3079ED;
		float: left;
		height: 30px;
		margin: 2px;
		overflow: hidden;
		text-align: center;
		width: 30px;
	}

	#uploaded_list .uploaded_image_container img{ width:30px; height:auto; width:auto; height:30px;}


	/*
	/////////////////////////END uploadify

	*/


	#panel-sidebar 	.ui-resizable-e {
		right: 0;
	}


	/*
	/////////////////Uploadify

	*/

	#upload_container {
		display: block;
		height: 288px;
		margin: 0 0 0 30px;
		overflow: auto;
		position: relative;
		width: 750px; /* 325,750 */
	}

	#upload_container_uploader,
	#upload_container_uploaded {
		background: none repeat scroll 0 0 #F1F1F1;
		border: 1px solid #DDDCDC;
		float: left;
		width: 320px;
		height:282px;

	}

	#upload_container_uploader{
		display: block;
	}

	#upload_container_uploaded {
		display:none;
		margin: 0 0 0 100px;
	}

	.ui-resizable-s {
		bottom: 0;
	}

</style>




<!-- END From Uploadify View -->

<script language="javascript">
	//POST URIs
	var post__change_category='{change_category}';


	var img_path='{module:resource}/images/';//'(module:resource)/upload/images/imagine.nog'
	//tree = json__init;//jQuery.parseJSON(cat_imgs);
	//All images of a particular cat/parent_id
	//URI AJAX Controllers
	var
	uri_cat_data='/get_category_data';
	var
	panel_sidebar_menubar_ht=40,
	gallery_container_resizablehandle_adjustment_ht=3,
	panel_sidebar_resizablehandle_adjustment_ht=8;
	/*
$(window).resize(function() {
	wm.debug('window.resize>')
	$("#gallery_container").trigger("resize");
	$("#panel-sidebar").trigger("resize");
});
	 */
	function initInterface(){
		wm.debug('inside initInterface')
		//    $("#top-panel").resizable( "option", "ghost", true, "handles", "e", "minWidth", 300/*def below too*/ );
		$("#gallery_container").resizable( {handles:'s', minHeight:400 });

		$("#gallery_container").bind( "resize", function(event, ui) {
			//console.debug($("#top-panel").height());
			//wm.debug('#gallery_container.resize>')

			$("#panel-sidebar").height($("div#gallery_container").height());
			$("#panel-content").height($("div#gallery_container").height());

			$("#sidebar-category_container").height($("#panel-sidebar").height()-panel_sidebar_menubar_ht-3);

			$("#panel-content-container").height($("div#panel-content").height()-$("#topbar-menu").height()-4);

			//Center position of grippie of sidebar
			$('#panel-sidebar .ui-resizable-handle span').css({'marginTop':(parseInt( $('div#panel-sidebar').height()-$('div#panel-sidebar div.ui-resizable-e span').height())/2)});

		});

		$("#panel-sidebar").bind( "resize", function(event, ui) {
			//wm.debug('#panel-sidebar.resize>')
			Resize_panel_sidebar();
		});

		/*   $("#bottom-panel").resizable({handles:'s', minHeight:200 });
		 */
		$("#panel-sidebar").resizable({handles:'e', minWidth:265/*def below too-215*/, maxWidth:532});

		$("div#gallery_container #panel-sidebar .ui-resizable-e").html('<span></span>');
		$("div#gallery_container div.ui-resizable-s").html('<span></span>');
		//$( ".selector" ).resizable();

		///Below is part of original init

		//console.debug("panel-content:" + $("#panel-content").height() + ", topbar-menu:" + $("div#topbar-menu").height() +", list:" + $("#content-listbar").height());
		$("#panel-content-container").height($("div#panel-content").height()-$("#topbar-menu").height()-4);
		$("#panel-content").width($("div#gallery_container").width()-$("#panel-sidebar").width()-3);

		//console.debug("panel-content:" + $("#panel-content").height() + ", topbar-menu:" + $("div#topbar-menu").height() +", list:" + $("#content-listbar").height());
		$("#sidebar-category_container").height($("div#panel-sidebar").height()-$("#sidebar-menubar").height()-4);
		$("#sidebar-category_container").width(366);

		//Center position of grippie of sidebar
		$('#panel-sidebar .ui-resizable-handle span').css({'marginTop':(parseInt( $('div#panel-sidebar').height()-$('div#panel-sidebar div.ui-resizable-e span').height())/2)});

	}

	function Resize_panel_sidebar(){
		//wm.debug("panel-content:" + $("#panel-content").width() + ", gallery_container:" + $("div#gallery_container").width() +", side:" + $("#panel-sidebar").width());
		$("#panel-content").width($("div#gallery_container").width()-$("#panel-sidebar").width()-3);
		$("#panel-content").height($("#panel-sidebar").height());

		$("#sidebar-category_container").height($("#panel-sidebar").height()-panel_sidebar_menubar_ht-gallery_container_resizablehandle_adjustment_ht);
		$("#sidebar-category_container").width($("#panel-sidebar").width()-panel_sidebar_resizablehandle_adjustment_ht);
	}


	/////////////////////////Inline uploadify

	var loading_uploadify_using_iframes=false;
	var uploadifyI=undefined;//AdminConsoleUploadifyController
	function setUploadifyInstance(AdminConsoleUploadifyController){
		uploadifyI=AdminConsoleUploadifyController;
		wm.debug('IFrame ready! :D');
		//Set configuration values
		uploadifyI.uploadify_configuration=uploadify_configuration;
		//Initialize uploadify
		uploadifyI.init_uploadify();
	}

	function uploadifyRendered(){
		hideThrobber($('#upload_box'));
		$('#iframe_upload_module').show(1000);
	}

	function showThrobber(parent){
		parent.append('<div class="throbber" style="background:url(\'{module:resource}/images/preloader.gif\') 0 0 no-repeat;"></div>');
	}

	function hideThrobber(parent){
		parent.children('div.throbber').remove();
	}

	var
	uploadify_configuration =
		{
		'controller'  :'{save_image_do}',
		'dump_path'	  :'{dump_path}',
		'fileExt'	  : '{allowed_file_extensions}',
		//	'buttonImg'   : '/uploadify/button.jpg',
		//	'rollover'    : true,
		'sizeLimit'   : '{max_upload_size}',//Has to be lower than upload_max_filesize
		//	'width'       : 40,
		//	'height'	  : 40,
		'queueID'	  : 'upload_queue',
		'scriptAccess': 'always',
		'multi': true
	},
	uploadify_imgs_cat_id;
	//$('#iframe_upload_module).


	function callIframeFunc(){
		if(typeof gIframeFunc != 'undefined'){
			gIframeFunc();
		}else{
			alert('Iframe function not available')
		}
	}

	/////////////////////////END Inline uploadify




	//////Keep at the bottom

	init=function(){

		wm.debug('gallery init> [Called]');


		//On page load, pass the AdminConsoleUploadifyController  instance to the parent
		// Instead of searching the function from the parent side, it is much safer and more predictable to hand over a
		// function object from the IFrame side. This also makes it very easy to check if the IFrame function has already loaded.
		// It works like a charm in Chrome, FF, IE 9, 8 and even 7.
		//Reference: http://forum.jquery.com/topic/calling-function-inside-an-iframe
		//OR
		//if follwoing inline approach.. Then calls function in this page itself

		//First register our uploaded-img-render-into-list event handler
		AdminConsoleUploadifyController.callback = refreshImgList;//refreshImgList addNewImage;//uploadifyTOrender;//
		parent.setUploadifyInstance(AdminConsoleUploadifyController);


		$('#upload_box_close_btn').bind('click',function(){

			//hide cat mask
			showCategoryListMask(false);

			//Clear all list
			$('#upload_it_button').hide();
			$('#cancel_all_button').hide();
			$('#clear_queue_button').hide();

			$('#upload_queue').html('');
			$('#upload_status').html('');
			$('#uploaded_list').html('');

			$('#upload_box').slideUp('1000').fadeOut('500');

			$('div#topbar-menu .topbar-menu-item[button=btn_upl_img]').show();
		});




		$('#cancel_all_button').bind('click',function(){
			$('#upload').uploadifyClearQueue();
		});

		$('#clear_queue_button').bind('click',function(){
			$('#upload').uploadifyClearQueue();
		});

		$('#upload_it_button').bind('click',function(){

			//$('#upload_container').css({width:'750px'});
			$('#upload_container_uploaded').fadeIn(500);

			uploadifyI.startUpload(arg_upload_target_name,arg_upload_target_id);
			$('#upload').uploadifyUpload();

			/*

				$('#upload_container').animate({
					width: 750
				}, 1000, function() {
				// Animation complete.
					$('#upload_container_uploaded').fadeIn(500,function(){
					});
				});
			 */
		});

		initInterface();


	}





</script>
<!--<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
jquery-ui.css

<link href="{module:resource}/css/ui-lightness/jquery-ui-1.8.16.custom.css" rel="stylesheet" type="text/css"/>
--><link href="{module:resource}/css/gallery.adminconsole.css" rel="stylesheet" type="text/css"/>


<div id="upload_box">
	<div id="upload_box_close_btn" class="menu-button-item-blue">X</div>
	<div id="upload_box_status" class="">X</div>
	<!-- Will be empty if using iframes -->
	<div id="upload_container">
		<div id="upload_container_uploader">
			<div id="upload_form">
				<?php echo form_open_multipart('admin/testmodule/upload'); ?>
				<?php echo form_upload(array('name' => 'Filedata', 'id' => 'upload')); ?>
				<?php echo form_close(); ?>
				<div id="cancel_all_button" class="menu-button-item-red">STOP</div>
				<div id="upload_it_button" class="menu-button-item-blue">Upload</div>
				<div id="clear_queue_button" class="menu-button-item-red">Clear</div>
			</div>
			<div id="upload_queue">
			</div>
		</div><!-- #upload_container_uploader -->
		<div id="upload_container_uploaded">
			<div id="upload_status">
			</div>
			<div id="uploaded_list">
			</div>
		</div>
		<br class="clear" />
	</div><!-- END #upload_container-->
</div><!-- END #upload_box-->

<div id="gallery_container">
	<div id="floating-formbox" collapsed="true">
		<div id="topbar-content">
			<div id="topbar-content-header">header</div>
			<div id="topbar-content-title">title</div>

			<div id="topbar-content-area">

				<div class="topbar-content-containers" id="content-area__category_properties">
					<div class="scroll_wrapper">
						<form id="form__category_properties" action="action_url">
							<input name="cat_id" id="cat_id" type="hidden" />
							<div class="field_container">
								<table>
									<tr><td><label for="cat_name">Name</label></td></tr>
									<tr><td><input name="cat_name" id="cat_name" type="text" /></td></tr>
									<tr><td>&nbsp;</td></tr>
									<tr><td><label for="cat_description">Description</label></td></tr>
									<tr><td><textarea name="cat_description" id="cat_description"></textarea></td></tr>
									<tr><td>&nbsp;</td></tr>
									<tr><td><label for="cat_alt">Alternate text</label></td></tr>
									<tr><td><input name="cat_alt" id="cat_alt" type="text" /></td></tr>
									<tr><td>&nbsp;</td></tr>
									<tr><td><label for="cat_name_url">Link to URL</label></td></tr>
									<tr><td><input name="cat_name_url" id="cat_name_url" type="text" /></td></tr>
									<tr><td>&nbsp;</td></tr>
									<tr><td><label for="cat_visible">Visible</label>&nbsp;&nbsp;<input name="cat_visible" id="cat_visible" type="checkbox" checked="checked" /></td></tr>
									<tr><td>&nbsp;</td></tr>
								</table>

								<input type="hidden" name="cat_cover_id" id="cat_cover_id" value="" />
								<table>
									<tr><td>Set Cover image</td></tr>
									<tr><td>&nbsp;</td></tr>
									<tr><td>
											<ul id="category_cover_img">
												<li>
													<span id="category_cover_img_dropbox_text">No cover image selected
														<span style="font-size:9px; display:block">[Drop an image here]</span>
													</span>
													<span id="category_cover_img_dropbox_text_dropping">Drop here</span>
												</li>
											</ul>
										</td></tr>
									<tr><td>&nbsp;</td></tr>
									<tr><td><a id="remove_cover_image">Remove cover image</a></td></tr>
								</table>

								<div class="more_info_container">
									<div class="more_info_container_toggle" collapsed="true">View more</div>
									<div class="more_info_container_content">
										<table>
											<tr><td>Date created&nbsp;&nbsp;<b><span id="cat_created"></span></b></td></tr>
											<tr><td>&nbsp;</td></tr>
											<tr><td>Date Modified&nbsp;&nbsp;<b><span id="cat_modified"></span></b></td></tr>
											<tr><td>&nbsp;</td></tr>
										</table>
									</div>
								</div>
							</div>

						</form>
					</div>
				</div>

				<div class="topbar-content-containers" id="content-area__image_properties">
					<div class="scroll_wrapper">
						<form id="form__image_properties" action="action_url">
							<input name="img_id" id="img_id" type="hidden" />
							<div class="field_container">
								<table>
									<tr><td>&nbsp;</td></tr>
									<tr><td><label for="img_links">Image links</label>:&nbsp;&nbsp;<span id="img_links"></span></td></tr>
									<tr><td>&nbsp;</td></tr>
									<tr><td><label for="img_name">Name</label></td></tr>
									<tr><td><input name="img_name" id="img_name" type="text" /></td></tr>
									<tr><td>&nbsp;</td></tr>
									<tr><td><label for="img_description">Description</label></td></tr>
									<tr><td><textarea name="img_description" id="img_description"></textarea></td></tr>
									<tr><td>&nbsp;</td></tr>
									<tr><td><label for="img_alt">Alternate text</label></td></tr>
									<tr><td><input name="img_alt" id="img_alt" type="text" /></td></tr>
									<tr><td>&nbsp;</td></tr>
									<tr><td><label for="img_name_url">Link to URL</label></td></tr>
									<tr><td><input name="img_name_url" id="img_name_url" type="text" /></td></tr>
									<tr><td>&nbsp;</td></tr>
									<tr><td><label for="img_visible">Visible</label>&nbsp;&nbsp;<input name="img_visible" id="img_visible" type="checkbox" checked="checked" /></td></tr>
									<tr><td>&nbsp;</td></tr>
								</table>

								<div class="more_info_container">
									<div class="more_info_container_toggle" collapsed="true">View more</div>
									<div class="more_info_container_content">
										<table>
											<tr><td>Date created&nbsp;&nbsp;<b><span id="img_created"></span></b></td></tr>
											<tr><td>&nbsp;</td></tr>
											<tr><td>Date Modified&nbsp;&nbsp;<b><span id="img_modified"></span></b></td></tr>
											<tr><td>&nbsp;</td></tr>
										</table>
									</div>
								</div>
							</div>

						</form>
					</div>
				</div>



				<div class="topbar-content-containers" id="content-area__newcategory_properties">
					<div class="scroll_wrapper">
						<form id="form__newcategory_properties" action="action_url">
							<div class="field_container">
								<table>
									<tr><td><label for="cat_name">Name</label></td></tr>
									<tr><td><input name="cat_name" id="cat_name" type="text" /></td></tr>
									<tr><td>&nbsp;</td></tr>
									<tr><td><label for="cat_description">Description</label></td></tr>
									<tr><td><textarea name="cat_description" id="cat_description"></textarea></td></tr>
									<tr><td>&nbsp;</td></tr>
									<tr><td><label for="cat_alt">Alternate text</label></td></tr>
									<tr><td><input name="cat_alt" id="cat_alt" type="text" /></td></tr>
									<tr><td>&nbsp;</td></tr>
									<tr><td><label for="cat_name_url">Link to URL</label></td></tr>
									<tr><td><input name="cat_name_url" id="cat_name_url" type="text" /></td></tr>
									<tr><td>&nbsp;</td></tr>
									<tr><td><label for="cat_visible">Visible</label>&nbsp;&nbsp;<input name="cat_visible" id="cat_visible" type="checkbox" checked="checked" /></td></tr>
									<tr><td>&nbsp;</td></tr>
								</table>
							</div>

						</form>
					</div>
				</div>


			</div><!-- #topbar-content-area END-->
			<input id="property-page-menu-save-button-item" type="submit" value="Save" class="menu-button-item" button="btn_save_propertypanel_form_details">
			<div class="close_button" title="Close">X</div>
			<br class="clear" />
		</div><!-- #topbar-content -->
	</div><!-- #floating-formbox -->


	<div id="panel-sidebar">
		<div id="sidebar-menubar">
			<div class="sidebar-menu-item" button="btn_new_cat" style="margin-right: 10px;">New</div>
			<div class="sidebar-menu-item" button="btn_del_cat">Delete</div>
			<div class="sidebar-menu-item" button="btn_vis_cat">Visible</div>
			<div class="sidebar-menu-item" button="btn_edit_cat">Edit</div>
		</div>
		<div id="sidebar-category_container">
			<!-- VIEW
				  <li class="category_item" style=""> <a> <span> <i></i> </span> </a>
				    <div class="category_item-details_container">
				      <div class="details_cat_name">cat name</div>
				      <div class="details_cat_imgcount">12pics</div>
				    </div>
				  </li>
			-->
		</div><!-- #sidebar-category_container -->
	</div><!-- #panel-sidebar -->
	<div id="panel-content">
		<div id="topbar-menu">

			<p id="feedback" style="display: block; width: 150px; float: left;">
				<span id="select-result"></span>
			</p>
			<div class="topbar-menu-item" button="btn_upl_img">Upload</div>
			<div class="topbar-menu-item" button="btn_del_img">Delete</div>
			<div class="topbar-menu-item" button="btn_vis_img">Visible</div>
			<div class="topbar-menu-item" button="btn_edit_img">Edit</div>
		</div>
		<div id="panel-content-container">

			<div id="content-listbar">
				<!-- VIEW
					      <li class="image_item" img_id="" title="" class=""> <a> <span> <i style="background:url('{module:resource}/images/sample_128_128.jpg') 0 0 no-repeat;"></i> </span> </a>
						<div class="image_item-details_container">
						  <div class="details_img_name">cat name</div>
						  <div class="details_img_imgcount">buttons</div>
						</div>
					      </li>

				-->

			</div><!-- #content-listbar -->
		</div><!-- #panel-content-container -->
	</div><!-- #panel-content -->

	<br class="clear" />
</div><!-- #gallery_container -->


