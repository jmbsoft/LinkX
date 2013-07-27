{assign var=$page_title value="Search Results"}
{include filename="global-header.tpl"}

{if $search_too_short}
<div class="error">
Your search term must be at least 4 characters
</div>
{else}
{* Load the search results *}
{links var=links type=search perpage=20}

<div class="bar">
<div class="bar-left">
<div class="bar-text-left">
Search results {$pagination.start|htmlspecialchars} - {$pagination.end|htmlspecialchars} of {$pagination.total|htmlspecialchars}
</div>
</div>
<div class="bar-right">
<div class="bar-text-right">
{if $pagination.prev}
<a href="search.php?s={$search_term|urlencode}&p={$pagination.prev_page|urlencode}" class="link"><img src="{$config.base_url}/images/go-previous.gif" border="0" alt="" style="position: relative; top: 1px;"> Previous</a>
&nbsp;
{/if}
{if $pagination.next}
&nbsp;
<a href="search.php?s={$search_term|urlencode}&p={$pagination.next_page|urlencode}" class="link">Next <img src="{$config.base_url}/images/go-next.gif" border="0" alt="" style="position: relative; top: 1px;"></a>
{/if}
</div>
</div>
</div>

<div class="content-section">
{if $pagination.total > 0}
{foreach var=link from=$links}
<a href="{$link.site_url|htmlspecialchars}" id="{$link.link_id|htmlspecialchars}" class="link" target="_blank">{$link.title|htmlspecialchars|hilite}</a>
{if $link.ratings > 0}
<img src="{$config.base_url}/images/{$link.rating_avg|tnearest_half}.gif" border="0" alt="{$link.rating_avg|number_format::1::$config.dec_point::$config.thousands_sep}">
{/if}
<br />
{$link.description|htmlspecialchars|hilite}<br />
<div class="small grey">(Clicks: {$link.clicks|number_format::0::$config.dec_point::$config.thousands_sep}; Comments:  {$link.comments|number_format::0::$config.dec_point::$config.thousands_sep}; Added: {$link.date_added|tdate::$config.date_format})</div>
<span class="url">{$link.site_url|htmlspecialchars}</span> -
{if $config.mod_rewrite}
<a href="{$config.base_url}/{$link.title|trewrite}-{$config.page_details|sprintf::$link.link_id}" class="link small">Details</a>
{else}
<a href="{$config.base_url}/details.php?id={$link.link_id|urlencode}" class="link small">Details</a>
{/if}
<br /><br />
{/foreach}
{else}
No matches for '{$search_term|htmlspecialchars}'
{/if}
</div>
{/if}

{include filename="global-footer.tpl"}
