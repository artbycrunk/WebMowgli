<!DOCTYPE html>
<html lang="en">
        <head>

                <title>{admin:title}</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
                <script type="text/javascript"> var site_url = '<?php echo site_url(); ?>'; </script>

                {head}

        </head>
        <body id="wm_admin">
		<!-- class='navbar navbar-inverse' -->
		<div class="navbar navbar-inverse navbar-fixed-top">
			<div class="navbar-inner">
				<div class="container-fluid">
					<a class="brand" href="{sidebar:logo_url}" target="_blank">WebMowgli</a>
					<ul class="nav">
						<li class="divider-vertical"></li>
						<li class="active">
							<a href="{site:root}/admin/dashboard">Dashboard</a>
						</li>
						<li><a href="{site:root}">Site</a></li>
					</ul>
					<ul class="nav pull-right">
						<li class="divider-vertical"></li>
						<li class="dropdown pull-right">
							<a href="#"class="dropdown-toggle" data-toggle="dropdown">
								{admin:user}
								<b class="caret"></b>
							</a>
							<ul class="dropdown-menu">
								<li><a href="{site:root}/user/change_password">Change password</a></li>
								<li><a href="{site:root}/user/logout">Logout</a></li>
							</ul>
						</li>
					</ul>
				</div>
			</div>

			<div class="container-fluid">
				<div class="row-fluid">
					<div class="span10 offset2">
						<div id="notifications" class="alert">
							<button id="notify_close_button" type="button" class="close">×</button>
							<span id="notification_status">Status</span> : <span id="notification_message">Notification message here</span>
						</div>
					</div>
				</div>

			</div>
		</div>
		<div id="wrapper" class="container-fluid">

			<!-- main content area -->
			<div class="row-fluid">

				<div id="sidebar-col" class="span2">
					{sidebar}
				</div>
				<div id="right-col" class="span10">

					{header}
					<div id="container-wrapper">

						<div id="body-wrapper">

							<!--  main start-->
							{main}
							<!--  main END -->

						</div><!--  body-wrapper END -->

						<!--  footer start -->
						{footer}
						<!--  footer end -->
					</div><!-- #container -->

				</div><!-- #right-col END-->
			</div> <!-- #row-fluid END-->
		</div><!-- #wrapper END-->

		<!-- Load Admin Javascript files -->
		{scripts}

        </body>
</html>
