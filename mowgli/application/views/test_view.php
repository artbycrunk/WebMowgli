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

		<br/>
		{if status = published }
			string SUCCESS : {status}
		{else}
			string Fail !!
		{/if}
		<br/>

	</div>

	<div class="sub-section">

		<br/>
		{if count = 520 }
			Count SUCCESS : {count}
		{else}
			Count Fail !!
		{/if}
		<br/>

	</div>

	<div class="sub-section">

		<br/>
		{if success = true }
			Bool Success : {success}
		{else}
			Bool Fail !!
		{/if}
		<br/>

	</div>

	<div class="sub-section">

		{if posts }

			<br/>1st set of posts<br/>
			<i>***** posts *********</i><br/><br/>
			{posts}
				{if slug }
					Slug Success : {slug}<br/>
				{else}
					no slug<br/>
				{/if}
			{/posts}
			<br/><i>***** End of posts *********</i><br/>

		{else}

			<br/>NO POSTS :(

		{/if}

	</div>

	<div class="sub-section">
		2nd set of posts<br/>
		{posts}
			name = {name} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; slug = {slug}<br/>
		{/posts}

	</div>

	<div class="sub-section">

		{if posts }

			<br/>1st set of posts<br/>
			<i>***** posts *********</i><br/><br/>
			{posts}
				{if slug }
					Slug Success : {slug}<br/>
				{/if}
			{/posts}
			<br/><i>***** End of posts *********</i><br/>

		{else}

			<br/>NO POSTS here also :(

		{/if}

	</div>


</div>
