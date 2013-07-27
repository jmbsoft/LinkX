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
<tr>
<td>
Your account has been located and a confirmation e-mail message has been sent to {$account.email|htmlspecialchars} with
instructions on how to reset your account password.  This confirmation e-mail should arrive within a few minutes and will
be valid for 24 hours.
</td>
</tr>
</table>

{include filename="global-footer.tpl"}