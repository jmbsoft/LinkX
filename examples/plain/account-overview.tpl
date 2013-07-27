{assign var=$page_title value="Account Overview"}
{include filename="global-header.tpl"}

<table align="center" width="800" cellpadding="0" cellspacing="0">
<tr>
<td>
<div class="infobar" style="margin-top: 8px; margin-bottom: 8px;">
Account Overview
</div>
</td>
</tr>
</table>

<table align="center" width="800" cellpadding="5" cellspacing="0" border="0">
{if $message == 'accountupdate'}
<tr>
<td colspan="3" class="notice">
Your account has been successfully updated
</td>
</tr>
{/if}
<tr>
<td width="150" align="right">
<b>Username</b>
</td>
<td width="600">
{$account.username|htmlspecialchars}
</td>
<td width="50" align="right">
<a href="{$config.base_url}/account.php?r=showedit&id={$account.username|urlencode}" class="link">[Edit]</a>
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Name</b>
</td>
<td colspan="2">
{$account.name|htmlspecialchars}
</td>
</tr>
<tr>
<td width="150" align="right">
<b>E-mail Address</b>
</td>
<td colspan="2">
{$account.email|htmlspecialchars}
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Number of Links</b>
</td>
<td colspan="2">
{$account.num_links|number_format::0::$config.dec_point::$config.thousands_sep}
</td>
</tr>
</table>


{if $account.num_links > 0}
<table align="center" width="800" cellpadding="0" cellspacing="0">
<tr>
<td>
<div class="infobar" style="margin-top: 8px; margin-bottom: 8px;">
Your Links
</div>
</td>
</tr>
</table>

<table align="center" width="800" cellpadding="5" cellspacing="0">
{foreach var=link from=$links}
<tr>
<td>
<a href="{$link.site_url|htmlspecialchars}" class="link">{$link.title|htmlspecialchars}</a><br />
{$link.description|htmlspecialchars}<br />
<span class="url">{$link.site_url|htmlspecialchars}</span>
</td>
<td valign="top" align="right" width="100">
<a href="{$config.base_url}/submit.php?r=showedit&id={$link.link_id|htmlspecialchars}" class="link">[Edit]</a>
</td>
</tr>
{/foreach}
</table>
{/if}

{include filename="global-footer.tpl"}
