<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>Change Password</title>
		{head}
		<script type="text/javascript">
			/*
			 * 'changepassword' page specific JS
			 */
			var server_message='{default-message}';
		</script>
	</head>
	<body class="change-password">
		<div id="change-password-form-wrapper">
			<div id="form-header"><span>Change Password</span></div>
			<div id="notification-container">
				<div id="server_error_msgs"></div>
			</div>
			<div id="form">
				{if is_reset_mode = true }
				<form name="changepassword-login-form" id="changepassword-login-form" action="{site:root}/user/reset_password_verify_do" method="post" form_type="wm_user_forms" autocomplete="off">
					{else}
					<form name="changepassword-login-form" id="changepassword-login-form" action="{site:root}/user/change_password_do" method="post" form_type="wm_user_forms" autocomplete="off">
						{/if}
						<div class="form_field_main_container">
							{if is_reset_mode = false }
							<!-- Current Password field is hidden when in password reset mode -->
							<div class="form_field_container">
								<div class="form_field_label">Current password</div>
								<div class="form_field_container2">
									<div class="form_field_container3">
										<input class="form_text_fields" type="password" value="" id="password-old" name="password-old" required="required">
									</div>
								</div>
								<div class="form_inline_msgbox alert alert-error"></div>
								<!--							<div class="form_inline_msgbox"></div>-->
							</div>
							{/if}
							<div class="form_field_container">
								<div class="form_field_label">New password</div>
								<div class="form_field_container2">
									<div class="form_field_container3">
										<input class="form_text_fields" type="password" value="" id="password-new" name="password-new" required="required">
									</div>
								</div>
								<div class="form_inline_msgbox alert alert-error"></div>

							</div>
							<div class="form_field_container">
								<div class="form_field_label">Re-enter the new password</div>
								<div class="form_field_container2">
									<div class="form_field_container3">
										<input class="form_text_fields" type="password" value="" id="password-new-re" name="password-new-re" required="required">
									</div>
								</div>
								<div class="form_inline_msgbox alert alert-error"></div>

							</div>
						</div>
						<div id="lower-form">
							<div id="reset"><span>Changed your mind? </span><a href="{admin:root}/dashboard">Dashboard</a>.</div>
							<input type="submit" id="submit-btn" class="btn btn-large btn-primary btn-submit" title="Click here to change your password" value="Change password" />
							<div class="spinner" ></div>
						</div>
<!--						<div id="lower-form">
							<input type="submit" id="submit-btn" class="btn btn-large btn-primary btn-reset" title="Click here to change your password" value="Change password" />
							<div class="spinner" ></div>
						</div>-->
						<br class="clear" />
					</form>
			</div><!-- #form -->
		</div>

	</body>
</html>