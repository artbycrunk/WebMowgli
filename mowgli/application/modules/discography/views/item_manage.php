<script>
init=function(){
}
</script>

<form id="disc_form" name="disc_form" action="{discography:post_url}" method="POST">
        Album: {discography:select:categ:id} <!-- name=categ_id -->
</form>

<br/><br/>


{discography:item:list}