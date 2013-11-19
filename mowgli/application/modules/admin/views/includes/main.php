
<div id="dialog-confirm">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
		<span id="dialogue-confirm-message"><!--Message will show here--></span>
	</p>
</div>
<div id="container">
	<div class="container-header">
		<h2 class="container-heading">{main:page_name}</h2>

		{main:tab_list}

	</div>

        <div class="container-body">

		<!-- Not activated, only for testing -->
		<div class="alert alert-block alert-error fade in" style="display:none">
			<button data-dismiss="alert" class="close" type="button">×</button>
			<h4 class="alert-heading">Oh snap! You got an error!</h4>
			<p>Change this and that and try again. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Cras mattis consectetur purus sit amet fermentum.</p>

			<a href="#" class="btn btn-danger">Take this action</a>
			<button data-dismiss="alert" class="btn" type='button'>Cancel</button>
		</div>

		<div id="{main:tab_id}" class="tab-content default-tab">

			{main:tab_content}

		</div><!-- End tab -->

        </div><!-- END .container-body -->

</div><!-- #container-wrapper -->
