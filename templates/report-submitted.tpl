{assign var=$page_title value="Link Report Submitted"}
{include filename="global-header.tpl"}


<div class="bar">
<div class="bar-left">
<div class="bar-text-left">
Link Report Submitted
</div>
</div>
<div class="bar-right">
</div>
</div>


<div class="content-section">
Thank you for submitting this report.  We will check this link shortly and take the appriopriate actions.

<br />
<br />

<a href="{$config.base_url}" class="link">Back to the main page</a><br />
<a href="{$config.base_url}/details.php?id={$link.link_id|urlencode}" class="link">Back to the link details page</a>
</div>


{include filename="global-footer.tpl"}
