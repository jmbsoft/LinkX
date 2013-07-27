{assign var=$page_title value="Account Logout"}
{include filename="global-header.tpl"}

<div class="bar">
<div class="bar-left">
<div class="bar-text-left">
Account Logout
</div>
</div>
<div class="bar-right">
</div>
</div>


<div class="content-section">
You are now logged out of your account.  You can <a href="{$http_referrer}">return to your last location</a> or
<a href="{$config.base_url}/">go to the main page</a>.
</div>

{include filename="global-footer.tpl"}
