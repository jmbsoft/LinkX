<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 0 Transitional//EN" "http://www.worg/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <title>Link Directory - {$page_title|htmlspecialchars}</title>
<link rel="stylesheet" type="text/css" href="{$config.base_url}/templates/style.css" />
</head>
<body>

<form method="GET" action="{$config.base_url}/search.php">
<table cellpadding="0" cellspacing="0" align="center" width="800">
<tr>
<td colspan="3" class="border-bottom">
<a href="{$config.base_url}/" class="tab">Home</a>
{if $config.mod_rewrite}
<a href="{$config.base_url}/{$config.page_new}" class="tab">New Links</a>
<a href="{$config.base_url}/{$config.page_top}" class="tab">Top Links</a>
<a href="{$config.base_url}/{$config.page_popular}" class="tab">Popular Links</a>
{else}
<a href="{$config.base_url}/index.php?c={$config.page_new|urlencode}" class="tab">New Links</a>
<a href="{$config.base_url}/index.php?c={$config.page_top|urlencode}" class="tab">Top Links</a>
<a href="{$config.base_url}/index.php?c={$config.page_popular|urlencode}" class="tab">Popular Links</a>
{/if}
{nocache}
{if !$config.logged_in}
<a href="{$config.base_url}/account.php?r=login" class="tab">Login</a>
{else}
<a href="{$config.base_url}/account.php?r=dologin" class="tab">My Account</a>
<a href="{$config.base_url}/account.php?r=logout" class="tab">Logout</a>
{/if}
{/nocache}
{if !$index_page && isset($this_category.category_id)}
<a href="{$config.base_url}/submit.php?c={$this_category.category_id}" class="tab">Add a Link</a>
{else}
<a href="{$config.base_url}/submit.php" class="tab">Add a Link</a>
{/if}
</td>
</tr>
<tr class="searchbar">
<td valign="middle" align="right" style="padding: 8px 5px 5px 5px;" width="450" class="border-left">
<input type="text" name="s" size="40" value="" />
</td>
<td style="padding-top: 5px;">
<input type="image" src="{$config.base_url}/images/search.gif">
</td>
<td style="font-size: 8pt; padding-right: 5px;" align="right" class="border-right">
{if $index_page}
<b>Categories:</b> {$total_categories|number_format::0::$config.dec_point::$config.thousands_sep}<br />
<b>Links:</b> {$total_links|number_format::0::$config.dec_point::$config.thousands_sep}
{else}
&nbsp;
{/if}
</td>
</tr>
<tr>
<td colspan="3" class="border-top" style="height: 1px; font-size: 1px;">
&nbsp;
</td>
</tr>
</table>
</form>
