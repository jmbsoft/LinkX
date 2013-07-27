{assign var=$page_title value="Home"}
{include filename="global-header.tpl"}

{* Load root level categories *}
{categories var=categories order="name"}

<table align="center" width="800" cellpadding="5">
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
  </span>
{/if}
</td>
{if $cat_counter % 3 == 0}
</tr>
<tr>
{/if}
{/foreach}
</tr>
<tr>
<td colspan="3">
<div class="infobar" style="margin-top: 5px">
Newest Links
</div>
</td>
</tr>

<tr>
<td colspan="3">
{links type=new var=links amount=5}

{* Loop through the newest links and display them *}
{foreach var=link from=$links}
<a href="{$config.base_url}/click.php?id={$link.link_id|urlencode}&u={$link.site_url|urlencode}" class="link" target="_blank">{$link.title|htmlspecialchars}</a><br />
{$link.description|htmlspecialchars}<br />
<span class="url">{$link.site_url|htmlspecialchars}</span> -
{if $config.mod_rewrite}
<a href="{$config.base_url}/{$config.page_details|sprintf::$link.link_id}" class="link small">View Details</a>
{else}
<a href="{$config.base_url}/details.php?id={$link.link_id|urlencode}" class="link small">View Details</a>
{/if}
<br /><br />
{/foreach}
</td>
</tr>
</table>

{include filename="global-footer.tpl"}