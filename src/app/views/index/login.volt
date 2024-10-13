<form action="<?php echo $action; ?>" method="<?php echo $method; ?>" enctype="multipart/form-data"><br>
Email: <input type="text" id="<?php echo $email_name; ?>" name="<?php echo $email_name; ?>" type="email" autoComplete="email" /><br>
First Name: <input type="text" id="<?php echo $first_name; ?>" name="<?php echo $first_name; ?>" /><br>
Last Name: <input type="text" id="<?php echo $last_name; ?>" name="<?php echo $last_name; ?>" /><br>
<input type="hidden" id="csrf_token" name="csrf_token" value="<?php echo $csrf_token; ?>" /><br>
<button type="submit" id="method" name="method" value="profile" />Sign Up</button>
</form>
