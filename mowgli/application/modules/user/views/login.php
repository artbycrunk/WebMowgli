<!DOCTYPE html>
<html lang="en">
        <head>
                <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
                <title>Sign In</title>
                {head}
                <script type="text/javascript">
                        /*
                         * 'Login' page specific JS
                         */
                        var server_message='{default-message}';
                </script>

                <script type="text/javascript">
                        $(function() {
                                $("#username").focus();
                        });
                </script>

        </head>
        <body class="login">
                <div id="login-form-wrapper">

<!--                        <div id="form-header"><span>Login to Administration Panel</span></div>-->


                        <div id="form">

                                <form name="login-form" id="login-form" action="{site:root}/user/login_do" method="post" form_type="wm_user_forms">

                                        <input id="login-mode" type="hidden" name='mode' value='{mode}' />

                                        <div class="form_field_main_container">
						<div id="form-logo">
							<img src="{admin:resource}/images/logo.png" alt="wm_Logo" height="64" width="235">
						</div>

						<div id="notification-container">
							<div id="server_error_msgs" class=""></div>
						</div>

 <!-- <div id="form-header"><span>Login to Administration Panel</span></div> -->
                                                <div class="form_field_container">
                                                        <!--<div class="form_field_label">Username</div>-->
                                                        <div class="form_field_container2">
								<div class="input-prepend">
									<!--<span class="login-icons"><i class="icon-user"></i></span>-->
									<input class="form_text_fields" id="username"  type="text" placeholder="Username" name="username" required="required">
								</div>
                                                                <!-- <div class="form_field_container3">
                                                                        <input class="form_text_fields" type="text" placeholder="Username" value="" id="username" name="username" required="required">
                                                                </div>-->
                                                        </div>
                                                        <!-- <div class="form_inline_msgbox alert alert-error"></div> -->
                                                </div>
                                                <div class="form_field_container">
                                                        <!--<div class="form_field_label">Password</div>-->
                                                        <div class="form_field_container2">
								<div class="input-prepend">
									<!--<span class="login-icons"><i class="icon-lock"></i></span>-->
									<input id="password" class="form_text_fields" type="password" placeholder="Password" name="password" required="required">
								</div>
                                                                <!--<div class="form_field_container3">
                                                                        <input class="form_text_fields" type="password" placeholder="Password" value="" id="password" name="password" required="required">
                                                                </div>-->
                                                        </div>
                                                        <!-- <div class="form_inline_msgbox alert alert-error"></div> -->
                                                </div>
						<div class="form_field_container">
							<input type="submit" id="submit-btn" class="btn btn-large btn-primary btn-block btn-submit" title="Click here to Sign in" value="Log me in!" />
						</div>
						<div id="reset">
							<a href="{site:root}/user/register">Register</a><span> | Forgot password?</span> <a href="{site:root}/user/reset_password">Reset</a>
						</div>
						<!-- note: remember-me feature disabled till complete-->
                                                <div id="remember-me" style="display: none;"><label class="checkbox"><input type="checkbox" name="remember-me">Remember me on this this computer</label></div>
                                        </div>
                                        <div id="lower-form">
<!--						<div id="reset"><span> if you dont have an account?</span> </div>-->


                                                <div class="spinner" ></div>
                                        </div>
                                        <br class="clear" />
                                </form>
                        </div><!-- #form -->

                </div>

        </body>
</html>
