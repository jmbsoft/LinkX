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

<table align="center" width="800" cellpadding="5" cellspacing="0">
{if $error}
<tr>
<td class="error">
{$error|htmlspecialchars}
</td>
</tr>
{else}
<tr>
<td>
Confirmation has been completed and your account login information has been e-mailed to {$account.email|htmlspecialchars}
</td>
</tr>
{/if}
</table>

{include filename="global-footer.tpl"}