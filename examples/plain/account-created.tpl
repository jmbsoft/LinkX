{assign var=$page_title value="Account Created"}
{include filename="global-header.tpl"}

<table align="center" width="800" cellpadding="0" cellspacing="0">
<tr>
<td>
<div class="infobar" style="margin-top: 8px; margin-bottom: 8px;">
Account Created
</div>
</td>
</tr>
</table>

<table align="center" width="800" cellpadding="5" cellspacing="0">
<tr>
<td colspan="2">
{* Display information when confirming accounts through e-mail *}
{if $status == 'unconfirmed'}
Thank you for creating your account.  An e-mail message has been sent to {$account.email|htmlspecialchars} with instructions
on how you can now confirm and activate your account.  Your account will not become active until you follow the instructions
provided in that e-mail message.
{* Display information when requiring account approval *}
{elseif $status == 'pending'}
Thank you for creating your account.  To verify the validity of new accounts, they must be reviewed by an administrator.  Once
your account has been reviewed you will receive a confirmation e-mail message with further instructions.
{* Display standard confirmation *}
{else}
Thank you for creating your account.  Your account is now active and you can login at any time to use the member-restricted functions.
{/if}
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Username</b>
</td>
<td>
{$account.username|htmlspecialchars}
</td>
</tr>
{if $account.password}
<tr>
<td width="150" align="right">
<b>Password</b>
</td>
<td>
{$account.password|htmlspecialchars}
</td>
</tr>
{/if}
<tr>
<td width="150" align="right">
<b>Name</b>
</td>
<td>
{$account.name|htmlspecialchars}
</td>
</tr>
<tr>
<td width="150" align="right">
<b>E-mail Address</b>
</td>
<td>
{$account.email|htmlspecialchars}
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
{if $field.value}
<img src="{$config.base_url}/images/check.gif" border="0">
{else}
<img src="{$config.base_url}/images/uncheck.gif" border="0">
{/if}
<b>{$field.label|htmlspecialchars}</b>
</td>
</tr>
    {else}
<tr>
<td width="150" align="right">
<b>{$field.label|htmlspecialchars}</b>
</td>
<td>
{$field.value|htmlspecialchars}
</td>
</tr>
    {/if}
  {/if}
{/foreach}
</table>


{include filename="global-footer.tpl"}