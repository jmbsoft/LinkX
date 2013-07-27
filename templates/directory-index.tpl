{assign var=$page_title value="Home"}
{include filename="global-header.tpl"}

{* Load root level categories *}
{categories var=categories order="name"}

<div class="bar">
<div class="bar-left">
<div class="bar-text-left">
Browse the Directory
</div>
</div>
<div class="bar-right">
</div>
</div>

<table width="100%" cellpadding="5" style="padding: 4px 5px 10px 5px;">
<tr>
{foreach var=category from=$categories counter=cat_counter}
<td valign="top" width="33%">
{if $config.mod_rewrite}
<b><a href="{$config.base_url}/{$category.path|htmlspecialchars}/" class="category">{$category.name|htmlspecialchars}</a></b><br />
{else}
<b><a href="{$config.base_url}/index.php?c={$category.category_id|urlencode}" class="category">{$category.name|htmlspecialchars}</a></b><br />
{/if}
{if $category.subcategories}
  {* Load sub-categories *}
  {categories var=subcategories parent=category amount=3 order="links DESC"}
  <div style="font-size: 8pt; margin-left: 10px;">
  {foreach var=subcategory from=$subcategories counter=subcat_counter}
  {if $config.mod_rewrite}
  <a href="{$config.base_url}/{$subcategory.path|htmlspecialchars}/" class="category plain">{$subcategory.name|htmlspecialchars}</a><br />
  {else}
  <a href="{$config.base_url}/index.php?c={$subcategory.category_id|urlencode}" class="category plain">{$subcategory.name|htmlspecialchars}</a><br />
  {/if}
  {/foreach}
  </div>
{/if}
</td>
{if $cat_counter % 3 == 0}
</tr>
<tr>
{/if}
{/foreach}
</tr>
</table>


<div class="bar">
<div class="bar-left">
<div class="bar-text-left">
Newest Links
</div>
</div>
<div class="bar-right">
</div>
</div>


{links type=new var=links amount=5}

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