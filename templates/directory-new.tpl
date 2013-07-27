{assign var=$page_title value="Newest Links"}
{assign var=$page_new value="1"}
{assign var=$page_rss value=new}
{include filename="global-header.tpl"}

{* Load the new links *}
{links type=new var=links amount=20}

<div class="bar">
<div class="bar-left">
<div class="bar-text-left">
Newest Links
</div>
</div>
<div class="bar-right">
</div>
</div>

<div class="content-section">
{* Loop through the newest links and display them *}
{foreach var=link from=$links}
<a href="{$link.site_url|htmlspecialchars}" id="{$link.link_id|htmlspecialchars}" class="link" target="_blank">{$link.title|htmlspecialchars}</a>
{if $link.ratings > 0}
<img src="{$config.base_url}/images/{$link.rating_avg|tnearest_half}.gif" border="0" alt="{$link.rating_avg|number_format::1::$config.dec_point::$config.thousands_sep}">
{/if}
<br />
{$link.description|htmlspecialchars}<br />
<div class="small grey">(Clicks: {$link.clicks|number_format::0::$config.dec_point::$config.thousands_sep}; Comments:  {$link.comments|number_format::0::$config.dec_point::$config.thousands_sep}; Added: {$link.date_added|tdate::$config.date_format})</div>
<span class="url">{$link.site_url|htmlspecialchars}</span> -
{if $config.mod_rewrite}
<a href="{$config.base_url}/{$link.title|trewrite}-{$config.page_details|sprintf::$link.link_id}" class="link small">Details</a>
{else}
<a href="{$config.base_url}/details.php?id={$link.link_id|urlencode}" class="link small">Details</a>
{/if}
<br /><br />
{/foreach}
</div>

{include filename="global-footer.tpl"}