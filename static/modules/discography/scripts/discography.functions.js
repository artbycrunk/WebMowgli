function mainDiscoInit(){

	wm.debug('[cat_edit.php] inside init');
	wm.admin.forms.init();

}

function submitHandler(){
	$('#description').val((tinyMCE.get('description').getContent()));
}
