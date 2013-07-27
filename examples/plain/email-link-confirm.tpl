=>[subject]
Confirm Your Link Submission
=>[plain]
Greetings,

To confirm the link that you just submitted to our link directory, please visit the following URL:
{$confirm_url}

Cheers,
Link Directory Administrator
{$config.base_url}/
=>[html]
Greetings,<br />
<br />
To confirm the link that you just submitted to our link directory, please visit the following URL:<br />
<a href="{$config.base_url}/submit.php?r=confirm&id={$confirm_id|urlencode}">{$config.base_url}/submit.php?r=confirm&id={$confirm_id|urlencode}</a><br />
<br />
Cheers,<br />
Link Directory Administrator<br />
<a href="{$config.base_url}/">{$config.base_url}/</a>
