{assign var=$page_title value="Account Password Reset"}
{include filename="global-header.tpl"}

<table align="center" width="800" cellpadding="0" cellspacing="0">
<tr>
<td>
<div class="infobar" style="margin-top: 8px; margin-bottom: 8px;">
Account Password Reset
</div>
</td>
</tr>
</table>

<form method="POST" action="{$config.base_url}/account.php">

<table align="center" width="800" cellpadding="5" cellspacing="0">
<tr>
<td colspan="2" class="notice">
Enter your e-mail address below to confirm your account.  An e-mail message will be sent to this address with a link you will need to visit
in order to reset your account password.
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
<td width="325" align="right">
<b>E-mail Address</b>
</td>
<td>
<input type="text" size="50" name="email" value="{$account.email|htmlspecialchars}" />
</td>
</tr>
<tr>
<td align="center" colspan="2">
<button type="submit">Submit</button>
</td>
</tr>
</table>

<input type="hidden" name="r" value="resetconfirm">
</form>

{include filename="global-footer.tpl"}