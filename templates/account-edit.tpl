{assign var=$page_title value="Edit Your Account"}
{include filename="global-header.tpl"}

<div class="bar">
<div class="bar-left">
<div class="bar-text-left">
Edit Your Account
</div>
</div>
<div class="bar-right">
</div>
</div>


<div class="content-section">
<form method="POST" action="{$config.base_url}/account.php">

<table align="center" width="100%" cellpadding="5" cellspacing="0">
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
{$account.username|htmlspecialchars}
</td>
</tr>
<tr>
<td width="150" align="right" valign="top">
<b>Password</b>
</td>
<td>
<input type="password" size="20" name="password" value="{$account.password|htmlspecialchars}" /><br />
<span class="small">Only fill this in if you want to change your password</span>
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
<input type="text" size="30" name="name" value="{$account.name|htmlspecialchars}" />
</td>
</tr>
<tr>
<td width="150" align="right">
<b>E-mail Address</b>
</td>
<td>
<input type="text" size="30" name="email" value="{$account.email|htmlspecialchars}" />
</td>
</tr>
{* Show the user defined fields *}
{foreach var=field from=$user_fields}
  {if $field.on_edit}
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

<tr>
<td align="center" colspan="2">
<button type="submit">Update Account</button>
</td>
</tr>
</table>

<input type="hidden" name="r" value="edit">
</form>
</div>

{include filename="global-footer.tpl"}
