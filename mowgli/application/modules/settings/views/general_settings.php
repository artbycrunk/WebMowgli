<script type="text/javascript">
        /*
         * 'General settings' page specific JS
         */
        var server_message='{default-message}';
        $(document).ready(function(){
                wm.debug('ready')
                init();
        });
        init=function(){
                wm.debug('init')
                wm.admin.forms.init();

		enable_comments = $('#enable_comments');

		enable_comments.is(':checked') ? $("#section_comments_username").show() : $("#section_comments_username").hide();

		enable_comments.click(function(){

//			alert( 'checked = ' + this.checked );

			$('#section_comments_username').toggle( this.checked );

		});
        }
</script>

<div id="form-wrapper">
	<form class="form-horizontal" name="{set:category}-settings" id="{set:category}-settings" action="{admin:root}/settings/general_save" method="post" form_type="wm_standard_form">
							<input type="hidden" name="setting_categ" value="{set:category}" />
							<div class="control-group">
								<label class="control-label" for="site_name">Site Name</label>
								<div class="controls wm-form-fields">
								  <input class="form_text_fields" type="text" value="{set:value:site_name}" id="site_name" name="site_name" placeholder="name of site">
									  <div class="form_inline_msgbox"></div>
									  <div class="form_inline_tip"><i class="icon-info-sign"></i> Name of site.</div>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="home_page">Home Page</label>
								<div class="controls wm-form-fields">
								  <input class="form_text_fields" type="text" value="{set:value:home_page}" id="home_page" name="home_page" placeholder="Home Page">
									  <div class="form_inline_msgbox"></div>
									  <div class="form_inline_tip"><i class="icon-info-sign"></i> Default home page ( eg. index, home, gallery ) for the main domain ( {site:root} ).</div>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="page_404">404 Error Page</label>
								<div class="controls wm-form-fields">
								  <input class="form_text_fields" type="text" value="{set:value:page_404}" id="page_404" name="page_404" placeholder="404 Error Page">
									  <div class="form_inline_msgbox"></div>
									  <div class="form_inline_tip"><i class="icon-info-sign"></i> Custom page to display when a url is not found.</div>
								</div>
							</div>
							<!-- Google Analytics -->
							<div class="control-group">
								<label class="control-label" for="analytics">Analytics Code</label>
								<div class="controls wm-form-ta-fields">
								  <textarea class="form_textarea_field" cols="50" rows="7" id="analytics" name="analytics" placeholder="Analytics Code">{set:value:analytics}</textarea>
									  <div class="form_inline_msgbox"></div>
									  <div class="form_inline_tip"><i class="icon-info-sign"></i> Enter code for site analytics</div>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="enable_comments">Enable Comments</label>
								<div class="controls wm-form-fields ">
								  {set:checkbox:enable_comments} Allow comments on this website ?
									  <div class="form_inline_msgbox"></div>
									  <div class="form_inline_tip"><i class="icon-info-sign"></i> Check if you wish to use an smtp server for sending emails</div>
								</div>
							</div>
							<div id="section_comments_username" class="control-group">
								<label class="control-label" for="comments_username">Comments Username</label>
								<div class="controls wm-form-fields ">
								  <input class="form_text_fields" type="text" value="{set:value:comments_username}" id="comments_username" name="comments_username" placeholder="Comments Username">
									  <div class="form_inline_msgbox"></div>
									  <div class="form_inline_tip"><i class="icon-info-sign"></i> Username for DISQUS comments, to register visit <a href="http://disqus.com" target="_blank">www.disqus.com</a></div>
								</div>
							</div>
							<div class="form-actions">
							  <button type="submit" id="submit-btn" class="btn btn-primary btn-form-submit" title="Save changes">Save changes</button>
							  <!-- <button type="button" class="btn">Cancel</button> -->
							</div>
	</form>
</div>