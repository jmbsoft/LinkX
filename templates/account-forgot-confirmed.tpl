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
{if $error}
<div class="error">
{$error|htmlspecialchars}
</div>
{else}
Confirmation has been completed and your account login information has been e-mailed to {$account.email|htmlspecialchars}
{/if}
</div>

{include filename="global-footer.tpl"}
