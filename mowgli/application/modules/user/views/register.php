<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>Register</title>
		{head}
		<script type="text/javascript">
			/*
			 * 'Register' page specific JS
			 */
			var server_message='{default-message}';
		</script>
	</head>
	<body class="register">
		<div id="register-form-wrapper">
			<div id="form-header"><span>Register</span></div>
			<div id="notification-container">
				<div id="server_error_msgs"></div>
			</div>
			<div id="form">
				<form name="register-form" id="register-form" action="{site:root}/user/register_do" method="post" form_type="wm_user_forms">

					<div class="form_field_main_container">

						<div class="form_field_container">
							<div class="form_field_label">Username</div>
							<div class="form_field_container2">
								<div class="form_field_container3">
									<input class="form_text_fields" type="text" value="" id="username" name="username" required="required">
								</div>
							</div>
							<div class="form_inline_msgbox alert alert-error"></div>
						</div>
						<div class="form_field_container">
							<div class="form_field_label">Email Id</div>
							<div class="form_field_container2">
								<div class="form_field_container3">
									<input class="form_text_fields" type="text" value="" id="email" name="email" required="required">
								</div>
							</div>
							<div class="form_inline_msgbox alert alert-error"></div>
						</div>
						<div class="form_field_container">
							<div class="form_field_label">Password</div>
							<div class="form_field_container2">
								<div class="form_field_container3">
									<input class="form_text_fields" type="password" value="" id="password" name="password" required="required">
								</div>
							</div>
							<div class="form_inline_msgbox alert alert-error"></div><div class="form_inline_msgbox"></div>
						</div>
						<div class="form_field_container">
							<div class="form_field_label">Retype Password</div>
							<div class="form_field_container2">
								<div class="form_field_container3">
									<input class="form_text_fields" type="password" value="" id="password-re" name="password-re" required="required">
								</div>
							</div>
							<div class="form_inline_msgbox alert alert-error"></div>
						</div>
						<div class="form_field_container">
							<div class="form_field_label">Secret Question</div>
							<div class="form_field_container2">
								<div class="form_field_container3">
									<input class="form_text_fields" type="text" value="" id="question" name="question" required="required">
								</div>
							</div>
							<div class="form_inline_msgbox alert alert-error"></div>
						</div>
						<div class="form_field_container">
							<div class="form_field_label">Secret Answer</div>
							<div class="form_field_container2">
								<div class="form_field_container3">
									<input class="form_text_fields" type="text" value="" id="answer" name="answer" required="required">
								</div>
							</div>
							<div class="form_inline_msgbox alert alert-error"></div>
						</div>

					</div>
					<div id="lower-form">
                                                <div id="reset"><span>Already have an account? </span><a href="{site:root}/user/login">Login</a>.</div>
						<input type="submit" id="submit-btn" class="btn btn-large btn-primary btn-submit" title="Click here to register for an account" value="Register" />
						<div class="spinner" ></div>
                                        </div>
					<br class="clear" />
				</form>
			</div><!-- #form -->
		</div>
	</body>
</html>