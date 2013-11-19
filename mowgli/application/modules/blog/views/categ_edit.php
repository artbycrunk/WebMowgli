<script type="text/javascript" src="{module:resource}/scripts/blog.functions.js"></script>

<script type="text/javascript">

        var server_message='{default-message}';

        var default_category ='{default-category}';
        init=function(){

		wm.debug('init categ form')
		wm.admin.forms.init();
		slugify( '#categ_name', '#categ_slug' );
        }

</script>


<div id="form-wrapper">
        <div id="form-notification-container">
                <div id="server_error_msgs"></div>
        </div>
        <div id="form">
                <form name="edit-categ" id="edit-categ" action="{admin:root}/blog/save_categ" method="post" form_type="wm_standard_form">
                        <input type="hidden" name="action_type" value="{action_type}" />
                        <input type="hidden" name="categ_id" value="{categ_id}" />

                        <table class="form-table">

                                <tr><th class="form_field_label"><label for="categ_name">Name</label></th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="{categ_name}" id="categ_name" name="categ_name">
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">name of category</div>
                                        </td></tr>

                                <tr><th class="form_field_label"><label for="categ_slug">Slug</label></th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="{categ_slug}" id="categ_slug" name="categ_slug">
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">url friendly title of category ( Eg. <b>name-of-this-categ</b> )</div>
                                        </td></tr>

                                <!-- description -->
                                <tr><th class="form_field_label"><label for="categ_description">Description</label></th></tr>
                                <tr><td class="field">
                                                <textarea class="form_textarea_field tinymce" name="categ_description" id="categ_description" cols="80" rows="15">{categ_description}</textarea>
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">description for this category</div>
                                        </td></tr>

                                <!-- is_comments -->
                                <tr><th class="form_field_label"><label for="categ_is_comments">Comments</label></th></tr>
                                <tr><td class="field">
                                                {blog:checkbox:categ_is_comments} allow comments for this category ?
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">enable or disable comments</div>
                                        </td></tr>

                                <!-- is_visible -->
                                <tr><th class="form_field_label"><label for="categ_is_visible">Visible</label></th></tr>
                                <tr><td class="field">
                                                {blog:checkbox:categ_is_visible} is this category is visible ?
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">show or hide this category to users</div>
                                        </td></tr>

                                <tr><td class="seperator"></td></tr>
                                <tr><td class="field">
                                                <input type="submit" id="submit-btn" class="btn btn-large" title="Save changes" value="Save" />
                                        </td></tr>
                        </table>

                </form>
        </div><!-- #form -->
</div>

<!--

PENDING ( Front end logic )

- horizontal scroll for categ list
-->