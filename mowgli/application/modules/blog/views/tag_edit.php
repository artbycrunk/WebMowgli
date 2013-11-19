<script type="text/javascript" src="{module:resource}/scripts/blog.functions.js"></script>

<script type="text/javascript">

        var server_message='{default-message}';

        var default_category ='{default-category}';
        init=function(){

		wm.debug('init blog tag form')
		wm.admin.forms.init();
		slugify( '#tag_name', '#tag_slug' );
        }

</script>


<div id="form-wrapper">
        <div id="form-notification-container">
                <div id="server_error_msgs"></div>
        </div>
        <div id="form">
                <form name="edit-tag" id="edit-tag" action="{admin:root}/blog/save_tag" method="post" form_type="wm_standard_form">
                        <input type="hidden" name="action_type" value="{action_type}" />
                        <input type="hidden" name="tag_id" value="{tag_id}" />

                        <table class="form-table">

                                <tr><th class="form_field_label"><label for="tag_name">Name</label></th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="{tag_name}" id="tag_name" name="tag_name">
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">name of tag</div>
                                        </td></tr>

                                <tr><th class="form_field_label"><label for="tag_slug">Slug</label></th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="{tag_slug}" id="tag_slug" name="tag_slug">
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">url friendly title of tag ( Eg. <b>name-of-this-tag</b> )</div>
                                        </td></tr>

                                <!-- description -->
                                <tr><th class="form_field_label"><label for="tag_description">Description</label></th></tr>
                                <tr><td class="field">
                                                <textarea class="form_textarea_field tinymce" name="tag_description" id="tag_description" cols="80" rows="15">{tag_description}</textarea>
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">description for this tag</div>
                                        </td></tr>

                                <tr><td class="seperator"></td></tr>
                                <tr><td class="field">
                                                <input type="submit" id="submit-btn" class="btn btn-large" title="Save changes" value="Save" />
                                        </td></tr>
                        </table>

                </form>
        </div><!-- #form -->
</div>