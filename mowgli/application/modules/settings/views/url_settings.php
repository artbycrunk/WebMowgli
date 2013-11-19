<script type="text/javascript">
        /*
         * 'Url settings' page specific JS
         */
        var server_message='{default-message}';
        $(document).ready(function(){
                wm.debug('ready')
                init();
        });
        init=function(){
                wm.debug('init')
                wm.admin.forms.init();
        }
</script>

<div id="form-wrapper">
        <div id="form-notification-container">
                <div id="server_error_msgs" class="form_inline_msgbox alert alert-error"></div>
        </div>
        {if mapping }
        <div id="form">
                <form name="url-settings" id="url-settings" action="{admin:root}/settings/url_save"  method="post" form_type="wm_standard_form">
                        <input type="hidden" name="setting_categ" value="{set:category}" />

                        <table class="form-table">
                                <tr><th class="form_field_label"><label for="post_title">Url Prefixes</label></th></tr>
                                <tr><td class="field">


                                                <table id="url-prefix-settings" name="url-prefix-settings" class="data_table table table-striped">
                                                        <!--
                                                        {resource:type} => css/js/images/other
                                                        -->
                                                        <thead>
                                                                <tr>
                                                                        <th class="header">Module</th>
                                                                        <th class="header">Prefix</th>
                                                                </tr>
                                                        </thead>
                                                        <tfoot>
                                                                <tr>
                                                                        <td colspan="5"></td>
                                                                        <td></td>
                                                                </tr>

                                                        </tfoot> <!-- -->


                                                        <tbody>
                                                                {mapping}
                                                                <!-- module name array, to be able to retrieve all module values again later -->
                                                                <input type="hidden" name="modules[]" value="{module:name}" />
                                                                <tr class="field">
                                                                        <td>{module:name}</td>
                                                                        <td><input class="form_text_fields" type="text" name="module_url_prefixes__{module:name}" value="{module:prefix}" /></td>
                                                                </tr>
                                                                {/mapping}
                                                        </tbody>

                                                </table>
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">Prefix for individual modules that support unique URLs</div>


                                        </td>
                                </tr>
                        </table>


                        <!-- Post Status, Comments, Save -->
                        <table class="form-table">

                                <tr><td class="seperator"></td></tr>
                                <tr><td class="field">
                                                <input type="submit" id="submit-btn" class="btn btn-large" title="Save changes" value="Save" />
                                        </td></tr>
                        </table>

                </form>
        </div><!-- #form -->

        {else}

        <p>No Module supports unique url prefixes  </p>

        {/if}
</div>