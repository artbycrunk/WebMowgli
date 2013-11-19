<?
switch($_POST["command"]){	
 case "import_site_notifications": ?>
{
	"success":["test success 1","test success '2'"],
	"error":["test error 1","test error 2"],
    "warning":["test warning1","test warning 2"],
    "info":["This is a test info"]
}
<? break;?>
<? case "content_ids_and_associated_content": 
//template_id page_id
?>
{
    "contents": [
        {"id":"content_id1","htmlContent":"content_id1_contents"},
        {"id":"content_id2","htmlContent":"content_id2_contents"},
        {"id":"content_id3","htmlContent":"content_id3_contents"}    
     ]   
}
<?        
/*{
    "template_id_<?=$_POST["template_id"]?>": {
        "page_id_<?=$_POST["page_id"]?>": {
            "contents": [
                {"id":"content_id1","Htmlcontent":"content_id1_contents"},
                {"id":"content_id2","Htmlcontent":"content_id2_contents"},
                {"id":"content_id3","Htmlcontent":"content_id3_contents"}    
             ]   
        }
    }
}*/
 break; ?>
<? } ?>

