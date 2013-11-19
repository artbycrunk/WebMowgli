<script type="text/javascript">

        var server_message='{default-message}';

        var default_category ='{default-category}';
        init=function(){
                wm.debug('init_ blog_settings form')
                wm.admin.forms.init();
		tinymce_init();
        }
</script>


<div id="form-wrapper">
        <div id="form-notification-container">
                <div id="server_error_msgs"></div>
        </div>
        <div id="form">
                <form name="blog-settings" id="blog-settings" action="{admin:root}/blog/settings_save" method="post" form_type="wm_standard_form">

                        <table class="form-table">

                                <!-- default_view -->
                                <tr><th class="form_field_label"><label for="default_view">Default View</label></th></tr>
                                <tr><td class="field">
                                                <!-- name='default_view' -->
                                                {set:select:default_view}
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">default view to display for your blog page</div>
                                        </td></tr>

                                <!-- Permalink -->
                                <tr><th class="form_field_label"><label for="permalink">Permalink structure</label></th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="{permalink}" id="permalink" name="permalink">
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">
                                                        the structure that you would like your post urls to look like. <br/>
                                                        <p>possible values <b> any_text, %author%, %category%, %tag%, %year%, %month%, %day%, %post%, %post_id%, %part%</b></p>
                                                        Eg 1. journal-notes/%year%/%month%/%day%/%post%/%part% <br/>
                                                        Eg 2. some-text-here/%category%/%post_id%/%part% <br/>
                                                </div>
                                        </td></tr>

                                <!-- Excerpt Word Count -->
                                <tr><th class="form_field_label"><label for="excerpt_word_count">Excerpts word count</label></th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="{excerpt_word_count}" id="excerpt_word_count" name="excerpt_word_count">
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">enter the number of words <i>( default = 100 words )</i> to display for excerpts or summary or category, post, tag, author etc</div>
                                        </td></tr>

                                <!-- Limit -->
                                <tr><th class="form_field_label"><label for="limit">Number of posts</label></th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="{limit}" id="limit" name="limit">
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">enter the number of posts or items <i>( default = 10 items )</i> to display at a time.</div>
                                        </td></tr>

                                <!-- default header -->
                                <tr><th class="form_field_label"><label for="default_header">Default header</label></th></tr>
                                <tr><td class="field">
                                                <textarea class="form_textarea_field" name="default_header" id="default_header" cols="50" rows="7">{default_header}</textarea>
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">enter any text or code snippet as the default header for posts, categories, etc, this will be overridden by individual headers</div>
                                        </td></tr>

                                <!-- default footer -->
                                <tr><th class="form_field_label"><label for="default_footer">Default footer</label></th></tr>
                                <tr><td class="field">
                                                <textarea class="form_textarea_field" name="default_footer" id="default_footer" cols="50" rows="7">{default_footer}</textarea>
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">enter any text or code snippet as the default footer for posts, categories, etc, this will be overridden by individual headers</div>
                                        </td></tr>

                                <!-- default ad script -->
                                <tr><th class="form_field_label"><label for="ad_script">Default Ad script</label></th></tr>
                                <tr><td class="field">
                                                <textarea class="form_textarea_field" name="ad_script" id="ad_script" cols="50" rows="7">{ad_script}</textarea>
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">enter any ad code snippet that will be used through out the blog system, to display the ad, simply use keyword {ad} anywhere in the post or category or widget</div>
                                        </td></tr>

                                <!-- Allow Comments -->
                                <tr><th class="form_field_label"><label for="allow_comments">Allow Comments</label></th></tr>
                                <tr><td class="field">
                                                {blog:checkbox:allow_comments} allow comments in blog system ?
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">universally enable or disable comment for blog</div>
                                        </td></tr>



                                <!-- Save -->
                                <tr><td class="seperator"></td></tr>
                                <tr><td class="field">
						<input type="submit" id="submit-btn" class="btn btn-large" title="Save changes" value="Save" />
                                        </td></tr>

                        </table>

                </form>
        </div><!-- #form -->
</div>

