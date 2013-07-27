{assign var=$page_title value="Account Password Reset"}
{include filename="global-header.tpl"}

<div class="bar">
<div class="bar-left">
<div class="bar-text-left">
Account Password Reset
</div>
</div>
<div class="bar-right">
</div>
</div>

<div class="content-section">
Your account has been located and a confirmation e-mail message has been sent to {$account.email|htmlspecialchars} with
instructions on how to reset your account password.  This confirmation e-mail should arrive within a few minutes and will
be valid for 24 hours.
</div>

{include filename="global-footer.tpl"}
