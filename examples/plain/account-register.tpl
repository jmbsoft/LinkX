{assign var=$page_title value="Create an Account"}
{include filename="global-header.tpl"}

<table align="center" width="800" cellpadding="0" cellspacing="0">
<tr>
<td>
<div class="infobar" style="margin-top: 8px; margin-bottom: 8px;">
Create An Account
</div>
</td>
</tr>
</table>

<form method="POST" action="{$config.base_url}/account.php">

<table align="center" width="800" cellpadding="5" cellspacing="0">
{if $errors}
<tr>
<td colspan="2" class="error">
{$errors}
</td>
</tr>
{/if}
<tr>
<td width="150" align="right">
<b>Username</b>
</td>
<td>
<input type="text" size="20" name="username" value="{$account.username|htmlspecialchars}" />
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Password</b>
</td>
<td>
<input type="password" size="20" name="password" value="{$account.password|htmlspecialchars}" />
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Confirm Password</b>
</td>
<td>
<input type="password" size="20" name="confirm_password" value="{$account.confirm_password|htmlspecialchars}" />
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Name</b>
</td>
<td>
<input type="text" size="40" name="name" value="{$account.name|htmlspecialchars}" />
</td>
</tr>
<tr>
<td width="150" align="right">
<b>E-mail Address</b>
</td>
<td>
<input type="text" size="50" name="email" value="{$account.email|htmlspecialchars}" />
</td>
</tr>
{* Show the user defined fields *}
{foreach var=field from=$user_fields}
  {if $field.on_create}
    {if $field.type == FT_CHECKBOX}
<tr>
<td width="150" align="right">
&nbsp;
</td>
<td>
{field from=$field value=$field.value}
<b><label for="{$field.name|htmlspecialchars}">{$field.label|htmlspecialchars}</label></b>
</td>
</tr>
    {else}
<tr>
<td width="150" align="right">
<b>{$field.label|htmlspecialchars}</b>
</td>
<td>
{field from=$field value=$field.value}
</td>
</tr>
    {/if}
  {/if}
{/foreach}

{if $config.account_captcha}
<tr>
<td width="150" align="right">
<b>Verification</b>
</td>
<td>
<img src="{$config.base_url}/code.php" border="0">
<input type="text" name="captcha" size="20" /><br />
<span class="small">Copy the characters from the image into the text box for verification</span>
</td>
</tr>
{/if}
<tr>
<td align="center" colspan="2">
<button type="submit">Create Account</button>
</td>
</tr>
</table>

<input type="hidden" name="r" value="create">
</form>

{include filename="global-footer.tpl"}