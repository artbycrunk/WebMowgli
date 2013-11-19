<script type="text/javascript">
        /*
         * 'Email settings' page specific JS
         */
        var server_message='{default-message}';
        $(document).ready(function(){
                wm.debug('ready')
                init();
        });
        init=function(){
                wm.admin.forms.init();

                // to hide/unhide smtp fields
                if($('input#smtp').is(':checked')){
                        $('#smtp_container').show(500).slideDown(500);
                }
                $('#{set:category}-settings :checkbox').click(function() {
                        var $this = $(this);
                        // $this will contain a reference to the checkbox
                        if ($this.is(':checked')) {
                                // the checkbox was checked
                                $('#smtp_container').show(500).slideDown(500);
                        } else {
                                // the checkbox was unchecked
                                $('#smtp_container').hide(500).slideUp(500);
                        }
                });


        }

</script>
<style>
        #smtp_container{
                display:none;

        }
</style>
<div id="form-wrapper">
        <div id="form-notification-container">
                <div id="server_error_msgs"></div>
        </div>
        <div id="form">
                <form name="{set:category}-settings" id="{set:category}-settings" action="{admin:root}/settings/email_save" method="post" form_type="wm_standard_form">
                        <input type="hidden" name="setting_categ" value="{set:category}" />
                        <table class="form-table">

                                <tr><th class="form_field_label"><label for="contact_email">Contact Email</label></th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="{set:value:contact_email}" id="contact_email" name="contact_email" size="15">
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">all emails from users, forms, notification, etc will be sent to this address</div>
                                        </td></tr>



                                <tr><th class="form_field_label"><label for="server_email">Server Email</label></th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="{set:value:server_email}" id="server_email" name="server_email" size="15">
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">email address to use while sending mails to users</div>
                                        </td></tr>



                                <tr><th class="form_field_label"><label for="from_name">From Name</label></th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="{set:value:from_name}" id="from_name" name="from_name" size="15">
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">the name to use while sending emails to users</div>
                                        </td></tr>

                                <tr><th class="form_field_label"><label for="bcc">Bcc</label></th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="{set:value:bcc}" id="bcc" name="bcc" size="15">
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">seperate email addresses using a comma ( , )</div>
                                        </td></tr>

                                <tr><th class="form_field_label"><label for="smtp">Use SMTP ?</label></th></tr>
                                <tr><td class="field">
                                                {set:checkbox:smtp} Use SMTP for email
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">check if you wish to use an smtp server for sending emails</div>
                                        </td></tr>
                        </table>
                        <div id="smtp_container">
                                <table class="form-table">
                                        <tr><th class="form_field_label"><label for="smtp_username">SMTP Username</label></th></tr>
                                        <tr><td class="field">
                                                        <input class="form_text_fields" type="text" value="{set:value:smtp_username}" id="smtp_username" name="smtp_username" size="15">
                                                        <div class="form_inline_msgbox"></div>
                                                        <div class="form_inline_tip">username or email required to login</div>
                                                </td></tr>

                                        <tr><th class="form_field_label"><label for="smtp_password">SMTP Password</label></th></tr>
                                        <tr><td class="field">
                                                        <input class="form_text_fields" type="password" value="{set:value:smtp_password}" id="smtp_password" name="smtp_password" size="15">
                                                        <div class="form_inline_msgbox"></div>
                                                        <div class="form_inline_tip">smtp email password</div>
                                                </td></tr>

                                        <tr><th class="form_field_label"><label for="smtp_host">SMTP Host</label></th></tr>
                                        <tr><td class="field">
                                                        <input class="form_text_fields" type="text" value="{set:value:smtp_host}" id="smtp_host" name="smtp_host" size="15">
                                                        <div class="form_inline_msgbox"></div>
                                                        <div class="form_inline_tip">smtp server or host address</div>
                                                </td></tr>

                                        <tr><th class="form_field_label"><label for="smtp_port">SMTP Port</label></th></tr>
                                        <tr><td class="field">
                                                        <input class="form_text_fields" type="text" value="{set:value:smtp_port}" id="smtp_port" name="smtp_port" size="15">
                                                        <div class="form_inline_msgbox"></div>
                                                        <div class="form_inline_tip">smtp port number</div>
                                                </td></tr>


                                        <tr><th class="form_field_label"><label for="smtp_charset">SMTP Charset</label></th></tr>
                                        <tr><td class="field">
                                                        <input class="form_text_fields" type="text" value="{set:value:smtp_charset}" id="smtp_charset" name="smtp_charset" size="15">
                                                        <div class="form_inline_msgbox"></div>
                                                        <div class="form_inline_tip">smtp character set ( Eg. utf-8 )</div>
                                                </td></tr>
                                </table>
                        </div>
                        <table class="form-table">
                                <tr><td class="seperator"></td></tr>
                                <tr><td class="field">
                                                <input type="submit" id="submit-btn" class="btn btn-large" title="Save changes" value="Save" />
                                        </td></tr>

                        </table>
                </form>
        </div>
</div>
