<style>

	.section {
		border: 2px #000 solid;
		padding: 10, 0, 0, 0;
		margin-top: 5px;
	}

	.sub-section{
		border: 1px #000 dashed;
		padding: 10, 0, 0, 0;
		background-color: scrollbar;
	}

	.hide{
		display: none;
	}

</style>

<div class="section">

	<div class="sub-section">

		<br/><i>***** posts *********</i><br/>
		{posts}
			First Posts data<br/>
		{/posts}
		<i>***** End of posts *********</i><br/>
	</div>

	<div class="sub-section hide">
		{posts}
			2nd post data here again
		{/posts}

	</div>


</div>
