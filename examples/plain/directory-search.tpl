{assign var=$page_title value="Search Results"}
{include filename="global-header.tpl"}

{if $search_too_short}
<table width="800" align="center">
<tr>
<td class="error">
Your search term must be at least 4 characters
</td>
</tr>
</table>
{else}
{* Load the search results *}
{links var=links type=search perpage=20}

<table align="center" width="800" cellpadding="0" cellspacing="0">
<tr>
<td>
<div class="infobar" style="margin-top: 8px; margin-bottom: 8px;">
<div style="float: right; padding-right: 5px;">
{if $pagination.prev}
<a href="search.php?s={$search_term|urlencode}&p={$pagination.prev_page|urlencode}" class="link plain">&lt; Prevous</a>
&nbsp;
{/if}

{if $pagination.next}
&nbsp;
<a href="search.php?s={$search_term|urlencode}&p={$pagination.next_page|urlencode}" class="link plain">Next &gt;</a>
{/if}
</div>
Search results {$pagination.start|htmlspecialchars} - {$pagination.end|htmlspecialchars} of {$pagination.total|htmlspecialchars}
</div>
</td>
</tr>
<tr>
<td valign="top">
{if $pagination.total > 0}
{foreach var=link from=$links}
<a href="{$config.base_url}/click.php?id={$link.link_id|urlencode}&u={$link.site_url|urlencode}" class="link" target="_blank">{$link.title|htmlspecialchars|hilite}</a><br />
{$link.description|htmlspecialchars|hilite}<br />
<div class="small grey">(Clicks: {$link.clicks|number_format::0::$config.dec_point::$config.thousands_sep}; Comments:  {$link.comments|number_format::0::$config.dec_point::$config.thousands_sep}; Added: {$link.date_added|tdate::$config.date_format})</div>
<span class="url">{$link.site_url|htmlspecialchars}</span> -
{if $config.mod_rewrite}
<a href="{$config.base_url}/{$config.page_details|sprintf::$link.link_id}" class="link small">Details</a>
{else}
<a href="{$config.base_url}/details.php?id={$link.link_id|urlencode}" class="link small">Details</a>
{/if}
<br /><br />
{/foreach}
{else}
No matches for '{$search_term|htmlspecialchars}'
{/if}
</td>
</tr>
</table>
{/if}

{include filename="global-footer.tpl"}
