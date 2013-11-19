<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>Reset Password</title>
		{head}
		<script type="text/javascript">
			/*
			 * 'Login' page specific JS
			 */
			var server_message='{default-message}';

			$(function() {
				$("#username").focus();
			});
                </script>
	</head>
	<body class="reset-password">
		<div id="reset-form-wrapper">
			<div id="form-header"><span>Reset Password</span></div>
			<div id="notification-container">
				<div id="server_error_msgs" class=""></div>
			</div>
			<div id="form">
				<form name="reset_password-form" id="reset_password-form" action="{site:root}/user/reset_password_do" method="post" form_type="wm_user_forms">
					<div class="form_field_main_container">
						<div id="container_username" class="form_field_container">
							<div class="form_field_label">Username</div>
							<div class="form_field_container2">
								<div class="form_field_container3">
									<input class="form_text_fields" type="text" value="" id="username" name="username" required="required">
								</div>
							</div>
							<div class="form_inline_msgbox alert alert-error"></div>
						</div>
						<div id="container_password" class="form_field_container">
							<div class="form_field_label">Email Id</div>
							<div class="form_field_container2">
								<div class="form_field_container3">
									<input class="form_text_fields" type="text" value="" id="email" name="email" required="required">
								</div>
							</div>
							<div class="form_inline_msgbox alert alert-error"></div>
						</div>
					</div>
					<div id="lower-form">
                                                <div id="reset"><span>Remember your password? </span><a href="{site:root}/user/login">Login</a>.</div>
						<div class="">
							<input type="submit" id="submit-btn" class="btn btn-large btn-primary btn-submit" title="Click here to reset password" value="Register" />
							<div class="spinner"></div>
						</div>

                                        </div>
					<br class="clear" />
				</form>
			</div><!-- #form -->
		</div>
	</body>
</html>