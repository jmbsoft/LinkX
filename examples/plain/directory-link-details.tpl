{assign var=$page_title value=$link.title}
{include filename="global-header.tpl"}

<table align="center" width="800" cellpadding="5" cellspacing="0" border="0">
<tr>
<td colspan="2">
<span style="font-size: 13pt; font-weight: bold">
<a href="{$config.base_url}/click.php?id={$link.link_id|urlencode}&u={$link.site_url|urlencode}" class="link" target="_blank">{$link.title|htmlspecialchars}</a>
</span>
</td>
</tr>
<tr>
<td width="100" align="right" valign="top">
<b>Categories:</b>
</td>
<td>
{foreach var=category from=$categories}
{if $config.mod_rewrite}
<a href="{$config.base_url}/{$category.path|htmlspecialchars}/" class="category">{foreach var=part from=$category.path_parts}{$part.name|htmlspecialchars}{if $part.path != $category.path}/{/if}{/foreach}</a><br />
{else}
<a href="{$config.base_url}/index.php?c={$category.category_id|urlencode}" class="category">{foreach var=part from=$category.path_parts}{$part.name|htmlspecialchars}{if $part.path != $category.path}/{/if}{/foreach}</a><br />
{/if}
{/foreach}
</td>
</tr>
<tr>
<td width="100" align="right" valign="top">
<b>URL:</b>
</td>
<td>
{$link.site_url|htmlspecialchars}
</td>
</tr>
<tr>
<td width="100" align="right" valign="top">
<b>Description:</b>
</td>
<td>
{$link.description|htmlspecialchars}
</td>
</tr>
<tr>
<td width="100" align="right" valign="top">
<b>Title:</b>
</td>
<td>
{$link.title|htmlspecialchars}
</td>
</tr>
<tr>
<td width="100" align="right" valign="top">
<b>Clicks:</b>
</td>
<td>
{$link.clicks|number_format::0::$config.dec_point::$config.thousands_sep}
</td>
</tr>
<tr>
<td width="100" align="right" valign="top">
<b>Link Added:</b>
</td>
<td>
{$link.date_added|tdate::'m-d-Y'}
</td>
</tr>
{foreach var=field from=$user_fields}
{if $field.on_details}
<tr>
<td width="100" align="right" valign="top">
<b>{$field.label|htmlspecialchars}:</b>
</td>
<td>
{$field.value|htmlspecialchars}
</td>
</tr>
{/if}
{/foreach}

<tr>
<td colspan="2" style="padding-top: 10px;" align="center">
Bad link? <a href="{$config.base_url}/report.php?id={$link.link_id|htmlspecialchars}" class="link">Let us know about it!</a>
</td>
</tr>
</table>

<table align="center" width="800" cellpadding="5" cellspacing="0" border="0">
<tr>
<td colspan="3">
<div class="infobar">
Rating
</div>
</td>
</tr>
<td width="375" valign="top">

<table cellpadding="5" cellspacing="0" border="0">
<tr>
<td width="100" align="right">
<b>Ratings:</b>
</td>
<td>
{$link.ratings|number_format::0::$config.dec_point::$config.thousands_sep}
</td>
</tr>
<tr>
<td width="100" align="right">
<b>Average Rating:</b>
</td>
<td>
<img src="{$config.base_url}/images/{$link.rating_avg|tnearest_half}.gif" border="0">
{$link.rating_avg|round::2}
</td>
</tr>
</table>

</td>
<td width="325" valign="top">

{nocache}
{if $message == 'rated'}
<div class="notice" style="margin-top: 5px; margin-bottom: 5px;">
Your rating has been recorded and will appear during the next update
</div>
{/if}
{if $config.user_for_rate && !$config.logged_in}
You must be <a href="{$config.base_url}/account.php?r=login&u={$ref_url|urlencode}" class="link">logged in</a> to rate links
{else}
<form action="rate.php" method="POST">
<b>Select Rating</b><br />
<select name="rating">
{range start=1 end=$config.max_rating counter=rating}
  <option value="{$rating|htmlspecialchars}"{if ceil($config.max_rating/2) == $rating} selected{/if}>{$rating|htmlspecialchars}{if $rating == 1} - Worst{elseif $rating == $config.max_rating} - Best{/if}</option>
{/range}
</select>

<button type="submit">Rate</button>

{if $config.rate_captcha}
<br /><br />
<b>Verification</b><br />
<img src="{$config.base_url}/code.php?c=rate" border="0">
<input type="text" name="captcha" size="20" /><br />
<span class="small">Copy the characters from the image into the text box for verification</span>
<br /><br />
{/if}

<input type="hidden" name="link_id" value="{$link.link_id|htmlspecialchars}">
</form>
{/if}
{/nocache}

</td>
</tr>
</table>


<table align="center" width="800" cellpadding="5" cellspacing="0" border="0">
<tr>
<td colspan="2">
<div class="infobar">
Comments
</div>
</td>
</tr>
<tr>
<td width="375" valign="top">
{if count($comments)}
{foreach var=comment from=$comments}
<div style="margin-bottom: 10px;">
<b>{$comment.name|htmlspecialchars} says:</b><br />
{$comment.comment|htmlspecialchars|nl2br}<br /><br />
<span class="small">{$comment.date_added|tdate::"m-d-Y"}</span>
</div>
{/foreach}
{else}
No comments have been posted yet, be the first to add yours!
{/if}
&nbsp;
</td>
<td width="325" valign="top">

{nocache}
{if $message == 'commented'}
<div class="notice" style="margin-top: 5px; margin-bottom: 5px;">
Your comment has been recorded and will appear during the next update
</div>
{/if}
{if $config.user_for_comments && !$config.logged_in}
You must be <a href="{$config.base_url}/account.php?r=login&u={$ref_url|urlencode}" class="link">logged in</a> to leave comments
{else}
<form action="comment.php" method="POST">
<b>Your Name</b><br />
<input type="text" size="30" name="name"><br /><br />

<b>Your E-mail</b> <span class="small">(will not be displayed)</span><br />
<input type="text" size="40" name="email"><br /><br />

<b>Your Comment</b><br />
<textarea name="comment" rows="6" cols="60"></textarea><br /><br />

{if $config.comments_captcha}
<b>Verification</b><br />
<img src="{$config.base_url}/code.php?c=comment" border="0">
<input type="text" name="captcha" size="20" /><br />
<span class="small">Copy the characters from the image into the text box for verification</span>
<br /><br />
{/if}

<button type="submit">Add Comment</button>
<input type="hidden" name="link_id" value="{$link.link_id|htmlspecialchars}">
</form>
{/if}
{/nocache}
</td>
</tr>
</table>

{include filename="global-footer.tpl"}
