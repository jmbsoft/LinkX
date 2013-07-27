=>[subject]
Link Submission Accepted
=>[plain]
Greetings,

{if $link.status == 'pending'}
Your link information has been recorded and will be reviewed by an administrator shortly.  If your link is
approved it will appear in our link directory within a few days.  Thank you for your submission!
{else}
Your link information has been recorded and your link will appear in our link directory within a few days.
Thank you for your submission!
{/if}

Site URL: {$link.site_url}
Title: {$link.title}
Description: {$link.description}

Cheers,
Link Directory Administrator
{$config.base_url}/
=>[html]
Greetings,<br />
<br />
{if $link.status == 'pending'}<br />
Your link information has been recorded and will be reviewed by an administrator shortly.  If your link is <br />
approved it will appear in our link directory within a few days.  Thank you for your submission!<br />
{else}<br />
Your link information has been recorded and your link will appear in our link directory within a few days.<br />
Thank you for your submission!<br />
{/if}<br />
<br />
Site URL: <a href="{$link.site_url|htmlspecialchars}">{$link.site_url|htmlspecialchars}</a><br />
Title: {$link.title|htmlspecialchars}<br />
Description: {$link.description|htmlspecialchars}<br />
<br />
Cheers,<br />
Link Directory Administrator<br />
<a href="{$config.base_url}/">{$config.base_url}/</a>
