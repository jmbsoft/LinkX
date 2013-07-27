=>[subject]
Password Reset Confirmation
=>[plain]
Greetings,

Someone has recently requested that your account password be reset at our site.  If you did not make this request, you can ignore this e-mail message.

To reset your account password, please visit this confirmation URL:
{$config.base_url}/account.php?r=reset&id={$confirm_id}

Cheers,
Link Directory Administrator
{$config.base_url}/
=>[html]
Greetings,<br />
<br />
Someone has recently requested that your account password be reset at our site.  If you did not make this request, you can ignore this e-mail message.<br />
<br />
To reset your account password, please visit this confirmation URL:<br />
<a href="{$config.base_url}/account.php?r=reset&id={$confirm_id|urlencode}">{$config.base_url}/account.php?r=reset&id={$confirm_id|urlencode}</a><br />
<br />
Cheers,<br />
Link Directory Administrator<br />
<a href="{$config.base_url}/">{$config.base_url}/</a>
