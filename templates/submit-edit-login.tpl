{assign var=$page_title value="Edit a Link"}
{include filename="global-header.tpl"}

<div class="bar">
<div class="bar-left">
<div class="bar-text-left">
Edit a Link
</div>
</div>
<div class="bar-right">
</div>
</div>


<div class="content-section">
<form method="POST" action="{$config.base_url}/submit.php">

<table align="center" width="100%" cellpadding="3" cellspacing="2">
<tr>
<td colspan="2">
Use this interface to edit a link that you submitted.  You will need to enter the exact URL, e-mail address, and password that you used when you added or
last edited the link.   If you have a user account and submitted the link using that account, please
use the <a href="{$config.base_url}/account.php?r=login" class="link">account login interface</a> to edit that link instead of this interface.
<br />
<br />
</td>
</tr>
{if $errors}
<tr>
<td colspan="2" class="error">
{$errors}
</td>
</tr>
{/if}
<tr>
<td width="150" align="right">
<b>Site URL</b>
</td>
<td>
<input type="text" size="80" name="login_site_url" value="{$request.login_site_url|htmlspecialchars}" />
</td>
</tr>
<tr>
<td width="150" align="right">
<b>E-mail Address</b>
</td>
<td>
<input type="text" size="50" name="login_email" value="{$request.login_email|htmlspecialchars}" />
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Password</b>
</td>
<td>
<input type="password" size="20" name="login_password" value="{$request.login_password|htmlspecialchars}" />
</td>
</tr>
<tr>
<td align="center" colspan="2">
<button type="submit">Edit Link</button>
</td>
</tr>
</table>

<input type="hidden" name="r" value="showedit">
<input type="hidden" name="noaccount" value="1">
</form>
</div>

{include filename="global-footer.tpl"}
