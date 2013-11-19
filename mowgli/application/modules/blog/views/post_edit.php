<script type="text/javascript" src="{module:resource}/scripts/blog.functions.js"></script>

<script type="text/javascript">

        var server_message='{default-message}';

        var default_category ='{default-category}';

	init=function(){

		wm.debug('init post form');
		wm.admin.forms.init();
		blog_post_edit_init();
		slugify( '#post_title', '#post_slug' );

		//wm.editor.init();
		tinymce_init();
        }

</script>

<div id="form-wrapper">
	<form class="form-horizontal" name="edit-posts" id="edit-posts" action="{admin:root}/blog/save_post" method="post" form_type="wm_standard_form">
							<input type="hidden" name="action_type" value="{action_type}" />
							<input type="hidden" name="post_id" value="{post_id}" />
							<input type="hidden" name="username" value="{username}" />
							<div class="control-group">
								<label class="control-label" for="post_title">Title</label>
								<div class="controls wm-form-fields">
								  <input class="form_text_fields" type="text" value="{post_title}" id="post_title" name="post_title" placeholder="Title of post">
									  <div class="form_inline_msgbox"></div>
									  <div class="form_inline_tip"><i class="icon-info-sign"></i> Title of post.</div>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="post_slug">Slug</label>
								<div class="controls wm-form-fields">
								  <input class="form_text_fields" type="text" value="{post_slug}" id="post_slug" name="post_slug" placeholder="url friendly title of post">
									  <div class="form_inline_msgbox"></div>
									  <div class="form_inline_tip"><i class="icon-info-sign"></i> URL Friendly title of post ( Eg. <b>title-of-this-post</b> )</div>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="post_body">Body</label>
								<div class="controls wm-form-ta-fields">
								  <textarea class="form_textarea_field tinymce_simple" name="post_body" id="post_body" cols="80" rows="15" placeholder="main body of post">{post_body}</textarea>
									  <div class="form_inline_msgbox"></div>
									  <div class="form_inline_tip"><i class="icon-info-sign"></i> Main body of post</div>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="post_categories[]">Categories</label>
								<div class="controls wm-form-fields">
										<!-- encapsulate with <label class="checkbox inline"><label>  -->
										{blog:multi-checkbox:post_categories}
										<!-- encapsulate with <label class="checkbox inline"><label>  -->
									  <div class="form_inline_msgbox"></div>
									  <div class="form_inline_tip"><i class="icon-info-sign"></i> Select categories</div>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="post_tags">Tags</label>
								<div class="controls wm-form-fields">
								  <input class="form_text_fields" type="text" value="{post_tags}" id="post_tags" name="post_tags" placeholder="post_tags">
									  <div class="form_inline_msgbox"></div>
									  <div class="form_inline_tip"><i class="icon-info-sign"></i> Separate tags with a comma ( eg. Tag 1, Tag 2, Tag 3 )</div>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="post_status">Status</label>
								<div class="controls wm-form-fields">
									{blog:select:post_status}
									  <div class="form_inline_msgbox"></div>
									  <div class="form_inline_tip"><i class="icon-info-sign"></i> Select the status of the post</div>
								</div>
							</div>
							<div class="control-group">
								<label class="control-label" for="post_is_comments">Comments</label>
								<div class="controls wm-form-fields">
								  {blog:checkbox:post_is_comments} allow comments for this post ?
									  <div class="form_inline_msgbox"></div>
									  <div class="form_inline_tip"><i class="icon-info-sign"></i> Enable or Disable comments</div>
								</div>
							</div>
							<div class="form-actions">
							  <button type="submit" id="submit-btn" class="btn btn-primary btn-form-submit" title="Save changes">Save changes</button>
							  <!-- <button type="button" class="btn">Cancel</button> -->
							</div>
	</form>
</div>
<!--

PENDING ( Front end logic )

- horizontal scroll for categ list
-->