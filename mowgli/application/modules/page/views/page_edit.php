<script type="text/javascript">

        var server_message='{default-message}';

        init=function(){
                wm.debug('init_ postlate_edit form')
                wm.admin.forms.init();
		// to create page_slug of page_name ( convert page_name to page_slug )
		slugify( "#page_name", "#page_slug" );
        }

</script>


<div id="form-wrapper">
        <div id="form-notification-container">
                <div id="server_error_msgs"></div>
        </div>
        <div id="form">

                <form name="edit-posts" id="edit-posts" action="{admin:root}/page/save_page" method="post" form_type="wm_standard_form">

                        <input type="hidden" name="action_type" value="{action_type}" />
                        <input type="hidden" name="page_id" value="{page_id}" />

                        <table class="form-table">

                                <tr><th class="form_field_label"><label for="page_name">Name</label></th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="{page_name}" id="page_name" name="page_name">
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">name of page</div>
                                        </td></tr>

                                <tr><th class="form_field_label"><label for="page_slug">Uri</label></th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="{page_slug}" id="page_slug" name="page_slug">
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">enter the URL friendly name of page ( use only alpha-numeric, dashes and underscore characters  )</div>
                                        </td></tr>

                                <!--

                                <tr><th class="form_field_label"><label for="page_redirect">Redirect Url</label></th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="{page_redirect}" id="page_redirect" name="page_redirect">
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">redirect url</div>
                                        </td></tr>

                                -->

                                <tr class="module_fields" ><th class="form_field_label"><label for="page_temp_id">Template</label></th></tr>
                                <tr  class="module_fields"><td class="field">
                                                {page:select:page_temp_id}
                                                <!--
                                                <select class="form_select_fields" name="page_temp_id" id="page_temp_id">
                                                        <option selected value="1">template 1</option>
                                                        <option value="2">template 2</option>
                                                </select>    -->
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">select the template that this page should use</div>
                                        </td></tr>


                                <tr><th class="form_field_label"><label for="page_title">Title</label></th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="{page_title}" id="page_title" name="page_title">
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">title of page</div>
                                        </td></tr>

                                <tr><th class="form_field_label"><label for="page_description">Description</label></th></tr>
                                <tr><td class="field">
                                                <textarea class="form_textarea_field tinymce" name="page_description" id="page_description" cols="80" rows="5">{page_description}</textarea>
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">enter the description of the page</div>
                                        </td></tr>

                                <tr><th class="form_field_label"><label for="page_keywords">Keywords</label></th></tr>
                                <tr><td class="field">
                                                <textarea class="form_textarea_field tinymce" name="page_keywords" id="page_keywords" cols="80" rows="5">{page_keywords}</textarea>
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">separate keywords with a comma</div>
                                        </td></tr>

                                <tr><th class="form_field_label"><label for="page_is_visible">Publish</label></th></tr>
                                <tr><td class="field">
                                                {page:checkbox:page_is_visible} publish this page ?
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">publish or un-publish this page</div>
                                        </td></tr>


                                <tr><td class="seperator"></td></tr>
                                <tr><td class="field">
                                                <input type="submit" id="submit-btn" class="btn btn-large" title="Save changes" value="Save" />
                                        </td></tr>

                        </table>

                </form>


        </div><!-- #form -->
</div>
