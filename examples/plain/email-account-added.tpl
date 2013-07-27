=>[subject]
Account Added
=>[plain]
Greetings,

Your account has been successfully added.  To use your account to submit links, please be sure to login first.

{$config.base_url}/account.php?r=login
Username: {$account.username}

Cheers,
Link Directory Administrator
{$config.base_url}/
=>[html]
Greetings,<br />
<br />
Your account has been successfully added.  To use your account to submit links, please be sure to login first.<br />
<br />
<a href="{$config.base_url}/account.php?r=login">{$config.base_url}/account.php?r=login</a><br />
Username: {$account.username|htmlspecialchars}<br />
<br />
Cheers,<br />
Link Directory Administrator<br />
<a href="{$config.base_url}/">{$config.base_url}/</a>
