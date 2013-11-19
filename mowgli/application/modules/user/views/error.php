<!DOCTYPE html>
<html lang="en">
        <head>
                <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
                <title>Error</title>
                {head}

        </head>
        <body class="login">
                <div id="login-form-wrapper">
                        <div id="form-header"><span>WebMowgli</span></div>
                        <div id="notification-container">
                                <div id="server_error_msgs" class=""></div>
                        </div>
                        <div id="form">
                                <form name="login-form" id="login-form" action="{site:root}/user/login_do" method="post" form_type="wm_user_forms">

                                        <input id="login-mode" type="hidden" name='mode' value='{mode}' />

                                        <div class="form_field_main_container">
                                                <div id="error_page_container" class="form_field_container">
                                                        <div class="form_field_label alert alert-error">{message}</div>
							<div id="error-page-reset" >
								<a href="{site:root}/user/login">Login</a><span> | </span>
								<a href="{site:root}/user/reset_password">Forgot password</a><span> | </span>
								<a href="{site:root}/user/register">Register</a>
							</div>
                                                </div>

                                        </div>
<!--					<div id="lower-form">

					</div>-->

                                        <br class="clear" />
                                </form>
                        </div><!-- #form -->

                </div>

        </body>
</html>
