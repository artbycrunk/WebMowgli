<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  </head>
  <body>

      <div class="errors" style="color: red;">
        {admin:error_message}
    </div>
      
      <form action="{site:root}/admin/import/add_template_do" method="POST" accept-charset="UTF-8">

          Name:* <br /> <input type="text" name="name" value="{add_template:name}"/> <br /><br />
      <!--    <input type="checkbox" name="create-page" {add_template:create-page} /> Automatically create page<br/>
          Page Name: <br /> <input type="text" name="page-name" value="{add_template:page-name}" /> <br /><br /> -->
          Template Code:* <br /> <textarea name="html" style="height: 400px; width: 700px;" cols="" rows="" >{add_template:html}</textarea> <br />
          description: <br /> <textarea name="description" style="height: 100px; width: 700px;" cols="" rows="">{add_template:description}</textarea> <br /><br />

          <input type="submit" name="submit" value="Create Template" />
      </form>
  </body>
</html>
