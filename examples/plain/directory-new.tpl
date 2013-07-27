{assign var=$page_title value="Newest Links"}
{include filename="global-header.tpl"}

{* Load the new links *}
{links type=new var=links amount=20}

<table align="center" width="800" cellpadding="0" cellspacing="0">
<tr>
<td valign="top" colspan="3">
<div class="infobar" style="margin-top: 5px; margin-bottom: 10px;">
Newest Links
</div>

{* Loop through the newest links and display them *}
{foreach var=link from=$links}
<a href="{$config.base_url}/click.php?id={$link.link_id|urlencode}&u={$link.site_url|urlencode}" class="link" target="_blank">{$link.title|htmlspecialchars}</a> <span class="small">({$link.date_added|tdate::"m-d-Y"})</span><br />
{$link.description|htmlspecialchars}<br />
<span class="url">{$link.site_url|htmlspecialchars}</span> -
{if $config.mod_rewrite}
<a href="{$config.base_url}/{$config.page_details|sprintf::$link.link_id}" class="link small">Details</a><br />
{else}
<a href="{$config.base_url}/details.php?id={$link.link_id|urlencode}" class="link small">Details</a><br />
{/if}
<span style="font-size: 8pt;">
{foreach var=category from=$link.categories}
{if $config.mod_rewrite}
<a href="{$config.base_url}/{$category.path|htmlspecialchars}/" class="category">{foreach var=part from=$category.path_parts}{$part.name|htmlspecialchars}{if $part.path != $category.path}/{/if}{/foreach}</a><br />
{else}
<a href="{$config.base_url}/index.php?c={$category.category_id|urlencode}" class="category">{foreach var=part from=$category.path_parts}{$part.name|htmlspecialchars}{if $part.path != $category.path}/{/if}{/foreach}</a><br />
{/if}
{/foreach}
</span>
<br />
{/foreach}
</td>
</tr>
</table>

{include filename="global-footer.tpl"}
