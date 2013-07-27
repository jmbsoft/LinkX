{assign var=$page_title value="Submission Information"}
{assign var=$page_add value="1"}
{include filename="global-header.tpl"}

<div class="bar">
<div class="bar-left">
<div class="bar-text-left">
Link Submission Information
</div>
</div>
<div class="bar-right">
</div>
</div>

<div class="content-section">
{* Explain must be registered user to submit links *}
{if $config.user_for_links}
To submit a link you will first need to create an account and login.  Once you are logged in, <a href="{$config.base_url}" class="link">browse our link directory</a>
to find the category that best suits your link.  Once you have found that category, click on the 'Add a Link' tab at the top of the page to add your link in that category.
This will take you to the link submission form where you can fill out the required information to get your link listed on our site.

<br />
<br />

If you need to edit a link that is already in the database, please <a href="{$config.base_url}/account.php?r=login" class="link">login to your account</a> which will
show you the links you currently have in the database and allow you to edit them.
{else}
To submit a link, <a href="{$config.base_url}" class="link">browse our link directory</a> to find the category that best suits your link.  Once you have found that
category, click on the 'Add a Link' tab at the top of the page to add your link in that category.  This will take you to the link submission form where you can fill out
the required information to get your link listed on our site.

<br />
<br />

If you need to edit a link that is already in the database, please use the <a href="{$config.base_url}/submit.php?r=editlogin" class="link">link editing interface</a> or
<a href="{$config.base_url}/account.php?r=login" class="link">login to your account</a>.
{/if}
</div>

{include filename="global-footer.tpl"}
