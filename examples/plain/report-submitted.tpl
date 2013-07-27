{assign var=$page_title value="Link Report Submitted"}
{include filename="global-header.tpl"}

<table align="center" width="800" cellpadding="0" cellspacing="0">
<tr>
<td>
<div class="infobar" style="margin-top: 8px; margin-bottom: 8px;">
Report a Link
</div>
</td>
</tr>
</table>

<table align="center" width="800" cellpadding="3" cellspacing="2">
<tr>
<td colspan="2">
Thank you for submitting this report.  We will check this link shortly and take the appriopriate actions.

<br />
<br />

<a href="{$config.base_url}" class="link">Back to the main page</a><br />
<a href="{$config.base_url}/details.php?id={$link.link_id|urlencode}" class="link">Back to the link details page</a>
</td>
</tr>
</table>

{include filename="global-footer.tpl"}
