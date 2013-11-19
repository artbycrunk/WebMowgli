<!DOCTYPE html>
<html lang="en">

	<head>

		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

		<title>Simpla Admin | Sign In</title>

		<!--                       CSS                       -->

		<!-- Reset Stylesheet -->
                <link rel="stylesheet" href="{admin:resource}/css/reset.css" type="text/css" media="screen" />

		<!-- Main Stylesheet -->

		<link rel="stylesheet" href="{admin:resource}/css/style.css" type="text/css" media="screen" />

		<!-- Invalid Stylesheet. This makes stuff look pretty. Remove it if you want the CSS completely valid -->
		<link rel="stylesheet" href="{admin:resource}/css/invalid.css" type="text/css" media="screen" />

		<!-- Colour Schemes

		Default colour scheme is green. Uncomment prefered stylesheet to use it.

		<link rel="stylesheet" href="{admin:resource}/css/blue.css" type="text/css" media="screen" />

		<link rel="stylesheet" href="{admin:resource}/css/red.css" type="text/css" media="screen" />

		-->

		<!-- Internet Explorer Fixes Stylesheet -->

		<!--[if lte IE 7]>
			<link rel="stylesheet" href="{admin:resource}/css/ie.css" type="text/css" media="screen" />
		<![endif]-->

		<!--                       Javascripts                       -->

		<!-- jQuery -->
		<script type="text/javascript" src="{admin:resource}/scripts/jquery-1.3.2.min.js"></script>


		<!-- jQuery Configuration -->
		<script type="text/javascript" src="{admin:resource}/scripts/simpla.jquery.configuration.js"></script>

		<!-- Facebox jQuery Plugin -->
		<script type="text/javascript" src="{admin:resource}/scripts/facebox.js"></script>

		<!-- jQuery WYSIWYG Plugin -->
		<script type="text/javascript" src="{admin:resource}/scripts/jquery.wysiwyg.js"></script>

		<!-- Internet Explorer .png-fix -->


		<!--[if IE 6]>
			<script type="text/javascript" src="{admin:resource}/scripts/DD_belatedPNG_0.0.7a.js"></script>
			<script type="text/javascript">
				DD_belatedPNG.fix('.png_bg, img, li');
			</script>
		<![endif]-->

	</head>

	<body id="login">

		<div id="login-wrapper" class="png_bg">
			<div id="login-top">

				<h1>Simpla Admin</h1>
				<!-- Logo (221px width) -->
				<img id="logo" src="{admin:resource}/images/logo.png" alt="Simpla Admin logo" />

			</div> <!-- End #logn-top -->

			<div id="login-content">

                            <form action="{site:root}/user/login_do" method="POST" >


					<div class="notification information png_bg">
						<div>
							You have successfully logged off
						</div>
					</div>


					<p>

						<label>Username</label>
                                                <input class="text-input" type="text" name="username" />
					</p>
					<div class="clear"></div>
					<p>
						<label>Password</label>
                                                <input class="text-input" type="password" name="password"/>
					</p>

					<div class="clear"></div>
					<p id="remember-password">
                                            <input type="checkbox" name="remember-me" />Remember me
					</p>
					<div class="clear"></div>
					<p>
						<input class="button" type="submit" value="Sign In" />
					</p>

				</form>

			</div> <!-- End #login-content -->

		</div> <!-- End #login-wrapper -->

  </body>

</html>
