{assign var=$page_title value=$this_category.name}
{assign var=$page_rss value=c}

{include filename="global-header.tpl"}

{* Display full category listing *}
<div style="padding: 0 0 8px 12px; font-weight: bold; font-size: 9pt;">
<a href="{$config.base_url}/" class="category">Home</a> 
{foreach var=part from=$this_category.path_parts}
{* Don't link to the category that is being viewed *}
{if $part.category_id == $this_category.category_id}
&raquo; {$part.name|htmlspecialchars}
{else}
{if $config.mod_rewrite}
&raquo; <a href="{$config.base_url}/{$part.path|htmlspecialchars}/" class="category">{$part.name|htmlspecialchars}</a>
{else}
&raquo; <a href="{$config.base_url}/index.php?c={$part.category_id|htmlspecialchars}" class="category">{$part.name|htmlspecialchars}</a>
{/if}
{/if}
{/foreach}
</div>


{* Only show if this category has 1 or more sub-categories *}
{if $this_category.subcategories > 0}

{* Load the sub-categories of this category *}
{categories var=categories order="name"}

<div class="bar">
<div class="bar-left">
<div class="bar-text-left">
Sub-Categories
</div>
</div>
<div class="bar-right">
</div>
</div>


<div class="content-section">
<table width="100%">
<tr>
{foreach var=category from=$categories counter=cat_counter}
<td valign="top" width="33%">
{if $config.mod_rewrite}
<a href="{$config.base_url}/{$category.path|htmlspecialchars}/" class="category">{$category.name|htmlspecialchars}{if $category.crosslink_id}@{/if}</a>
{else}
<a href="{$config.base_url}/index.php?c={$category.category_id|htmlspecialchars}" class="category">{$category.name|htmlspecialchars}{if $category.crosslink_id}@{/if}</a>
{/if}
<span class="small grey">({$category.links|number_format::0::$config.dec_point::$config.thousands_sep}/{$category.subcategories|number_format::0::$config.dec_point::$config.thousands_sep})</span>
</td>
{if $cat_counter % 3 == 0}
</tr>
<tr>
{/if}
{/foreach}
</tr>
</table>
</div>
{/if}


{* Load the featured links for this category *}
{links var=featured_links type=featured}

{* Only show if this category has 1 or more featured links *}
{if count($featured_links) > 0}
<div class="bar">
<div class="bar-left">
<div class="bar-text-left">
Featured Links
</div>
</div>
<div class="bar-right">
</div>
</div>


<div class="content-section">
{* Loop through the featured links and display them *}
{foreach var=link from=$featured_links}
<a href="{$link.site_url|htmlspecialchars}" id="{$link.link_id|htmlspecialchars}" class="link" target="_blank">{$link.title|htmlspecialchars}</a>
{if $link.ratings > 0}
<img src="{$config.base_url}/images/{$link.rating_avg|tnearest_half}.gif" border="0" alt="{$link.rating_avg|number_format::1::$config.dec_point::$config.thousands_sep}">
{/if}
<br />
{$link.description|htmlspecialchars}<br />
<span class="url">{$link.site_url|htmlspecialchars}</span> - 
{if $config.mod_rewrite}
<a href="{$config.base_url}/{$link.title|trewrite}-{$config.page_details|sprintf::$link.link_id}" class="link small">Details</a>
{else}
<a href="{$config.base_url}/details.php?id={$link.link_id|urlencode}" class="link small">Details</a>
{/if}
<br /><br />
{/foreach}
</div>
{/if}

{* Load the regular links for this category *}
{links var=regular_links type=regular perpage=20 order="RAND()"}

{* Only show if this category has 1 or more links *}
{if count($regular_links) > 0}
<div class="bar">
<div class="bar-left">
<div class="bar-text-left">
Links {$pagination.start|htmlspecialchars} - {$pagination.end|htmlspecialchars} of {$pagination.total|htmlspecialchars}
</div>
</div>
<div class="bar-right">
<div class="bar-text-right">
{if $pagination.prev}
{if $config.mod_rewrite}
<a href="{$config.base_url}/{$this_category.path|htmlspecialchars}/{if $pagination.prev_page > 1}{$pagination.prev_page|htmlspecialchars}.html{/if}" class="link"><img src="{$config.base_url}/images/go-previous.gif" border="0" alt="" style="position: relative; top: 1px;"> Previous</a>
{else}
<a href="{$config.base_url}/index.php?c={$this_category.category_id|urlencode}&p={$pagination.prev_page|urlencode}" class="link"><img src="{$config.base_url}/images/go-previous.gif" border="0" alt="" style="position: relative; top: 1px;"> Previous</a>
{/if}
&nbsp;
{/if}
{if $pagination.next}
&nbsp;
{if $config.mod_rewrite}
<a href="{$config.base_url}/{$this_category.path|htmlspecialchars}/{$pagination.next_page|htmlspecialchars}.html" class="link">Next <img src="{$config.base_url}/images/go-next.gif" border="0" alt="" style="position: relative; top: 1px;"></a>
{else}
<a href="{$config.base_url}/index.php?c={$this_category.category_id|urlencode}&p={$pagination.next_page|urlencode}" class="link">Next <img src="{$config.base_url}/images/go-next.gif" border="0" alt="" style="position: relative; top: 1px;"></a>
{/if}
{/if}
</div>
</div>
</div>

{* Provide users with other sorting options; uncomment each line to give them that sorting option *}
<!-- <a href="{$config.base_url}/{$this_category.path|htmlspecialchars}/?s=popularity" class="link">Clicks</a> | -->
<!-- <a href="{$config.base_url}/{$this_category.path|htmlspecialchars}/?s=rating" class="link">Rating</a> | -->
<!-- <a href="{$config.base_url}/{$this_category.path|htmlspecialchars}/?s=alpha" class="link">Title</a> | -->
<!-- <a href="{$config.base_url}/{$this_category.path|htmlspecialchars}/?s=added" class="link">Added</a> | -->
<!-- <a href="{$config.base_url}/{$this_category.path|htmlspecialchars}/?s=modified" class="link">Modified</a> -->


<div class="content-section">
{* Loop through the standard links and display them *}
{foreach var=link from=$regular_links}
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
{/if}


{* Only show if this category has 1 or more related categories *}
{if !empty($this_category.related_ids)}
<div class="bar">
<div class="bar-left">
<div class="bar-text-left">
Related Categories
</div>
</div>
<div class="bar-right">
</div>
</div>

{* Load related categories *}
{categories var=related_categories related=true}

<div class="content-section">
{* Loop through the available related categories and display them *}
{foreach var=category from=$related_categories}
{if $config.mod_rewrite}
<a href="{$config.base_url}/{$category.path|htmlspecialchars}/" class="category">{foreach var=part from=$category.path_parts}{$part.name|htmlspecialchars}{if $part.path != $category.path}/{/if}{/foreach}</a> <span class="small grey">({$category.links|number_format::0::$config.dec_point::$config.thousands_sep}/{$category.subcategories|number_format::0::$config.dec_point::$config.thousands_sep})</span><br />    
{else}
<a href="{$config.base_url}/index.php?c={$category.category_id|urlencode}" class="category">{foreach var=part from=$category.path_parts}{$part.name|htmlspecialchars}{if $part.path != $category.path}/{/if}{/foreach}</a> <span class="small grey">({$category.links|number_format::0::$config.dec_point::$config.thousands_sep}/{$category.subcategories|number_format::0::$config.dec_point::$config.thousands_sep})</span><br />    
{/if}
{/foreach}
</div>
{/if}

{include filename="global-footer.tpl"}