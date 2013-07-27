=>[subject]
Confirm Your Account
=>[plain]
Greetings,

To confirm the account that you just created at our link directory, please visit the following URL:
{$config.base_url}/account.php?r=confirm&id={$confirm_id|urlencode}

Cheers,
Link Directory Administrator
{$config.base_url}/
=>[html]
Greetings,<br />
<br />
To confirm the account that you just created at our link directory, please visit the following URL:<br />
<a href="{$config.base_url}/account.php?r=confirm&id={$confirm_id|urlencode}">{$config.base_url}/account.php?r=confirm&id={$confirm_id|urlencode}</a><br />
<br />
Cheers,<br />
Link Directory Administrator<br />
<a href="{$config.base_url}">{$config.base_url}/</a>
