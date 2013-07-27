=>[subject]
Account Login Information
=>[plain]
Greetings,

Someone has recently requested that your account login information be reset.  Your current username and new password are listed below.

Username: {$account.username}
Password: {$account.password}

Cheers,
Link Directory Administrator
{$config.base_url}/
=>[html]
Greetings,<br />
<br />
Someone has recently requested that your account login information be reset.  Your current username and new password are listed below.<br />
<br />
Username: {$account.username|htmlspecialchars}<br />
Password: {$account.password|htmlspecialchars}<br />
<br />
Cheers,<br />
Link Directory Administrator<br />
<a href="{$config.base_url}/">{$config.base_url}/</a>
