{assign var=$page_title value="Account Login"}
{assign var=$page_login value="1"}
{include filename="global-header.tpl"}

<div class="bar">
<div class="bar-left">
<div class="bar-text-left">
Account Login
</div>
</div>
<div class="bar-right">
</div>
</div>


<div class="content-section">
<form method="POST" action="{$config.base_url}/account.php">

<table align="center" width="100%" cellpadding="5" cellspacing="0">
<tr>
<td colspan="2" align="center" class="bold">
If you don't yet have an account, please <a href="{$config.base_url}/account.php?r=register" class="link">create one</a> now.
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
<td width="400" align="right">
<b>Username</b>
</td>
<td>
<input type="text" size="20" name="login_username" value="{$request.login_username|htmlspecialchars}" />
</td>
</tr>
<tr>
<td width="400" align="right">
<b>Password</b>
</td>
<td>
<input type="password" size="20" name="login_password" value="{$request.login_password|htmlspecialchars}" />
</td>
</tr>
<tr>
<td align="center" colspan="2">
<a href="account.php?r=forgot" class="small link">Forgot your password?</a>
</td>
</tr>
<tr>
<td align="center" colspan="2">
<button type="submit">Log In</button>
</td>
</tr>
</table>

<input type="hidden" name="u" value="{$request.u|htmlspecialchars}">
<input type="hidden" name="r" value="dologin">
</form>
</div>

{include filename="global-footer.tpl"}
