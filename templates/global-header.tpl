<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
  <title>Link Directory - {$page_title|htmlspecialchars}</title>
  <link rel="stylesheet" type="text/css" href="{$config.base_url}/templates/style.css" />
  {if $link.link_id}
  {assign var=$keywords value=str_replace(' ', ',', $link.keywords)}
  <meta name="description" content="{$link.description|htmlspecialchars}" />
  <meta name="keywords" content="{$keywords|htmlspecialchars}" />
  {elseif $this_category.category_id}
  <meta name="description" content="{$this_category.meta_description|htmlspecialchars}" />
  <meta name="keywords" content="{$this_category.meta_keywords|htmlspecialchars}" />
  {else}
  <meta name="description" content="YOUR GLOBAL DESCRIPTION" />
  <meta name="keywords" content="YOUR GLOBAL KEYWORDS" />
  {/if}
  {if $page_rss == 'c'}
  <link rel="alternate" title="Links In {$this_category.name|htmlspecialchars}" href="{$config.base_url}/rss.php?c={$this_category.category_id|urlencode}&p={$page_num|urlencode}" type="application/rss+xml" />
  {elseif $page_rss}
  <link rel="alternate" title="{$page_title|htmlspecialchars}" href="{$config.base_url}/rss.php?p={$page_rss|urlencode}" type="application/rss+xml" />
  {/if}
</head>
<body>

<div id="non-footer">
<div id="tab-container">
<div id="tabs">
    <div class="tab-l{if $index_page} on-l{/if}"></div>
    <div class="tab-r{if $index_page} on-r{/if}">
      <a href="{$config.base_url}/">Home</a>
    </div>
    <div class="tab-l{if $page_new} on-l{/if}"></div>
    <div class="tab-r{if $page_new} on-r{/if}">
      <a href="{$config.base_url}/{if $config.mod_rewrite}{$config.page_new}{else}index.php?c={$config.page_new|urlencode}{/if}">New Links</a>
    </div>
    <div class="tab-l{if $page_top} on-l{/if}"></div>
    <div class="tab-r{if $page_top} on-r{/if}">
      <a href="{$config.base_url}/{if $config.mod_rewrite}{$config.page_top}{else}index.php?c={$config.page_top|urlencode}{/if}">Top Links</a>
    </div>
    <div class="tab-l{if $page_popular} on-l{/if}"></div>
    <div class="tab-r{if $page_popular} on-r{/if}">
      <a href="{$config.base_url}/{if $config.mod_rewrite}{$config.page_popular}{else}index.php?c={$config.page_popular|urlencode}{/if}">Popular Links</a>
    </div>
    {if !$config.logged_in}
    <div class="tab-l{if $page_login} on-l{/if}"></div>
    <div class="tab-r{if $page_login} on-r{/if}">
      <a href="{$config.base_url}/account.php?r=login">Login</a>
    </div>
    {else}
    <div class="tab-l{if $page_myaccount} on-l{/if}"></div>
    <div class="tab-r{if $page_myaccount} on-r{/if}">
      <a href="{$config.base_url}/account.php?r=dologin">My Account</a>
    </div>
    <div class="tab-l"></div>
    <div class="tab-r">
      <a href="{$config.base_url}/account.php?r=logout">Logout</a>
    </div>
    {/if}   
    <div class="tab-l{if $page_add} on-l{/if}"></div>
    <div class="tab-r{if $page_add} on-r{/if}">
      {if !$index_page && isset($this_category.category_id)}
        <a href="{$config.base_url}/submit.php?c={$this_category.category_id}" class="tab">Add a Link</a>
      {else}
        <a href="{$config.base_url}/submit.php" class="tab">Add a Link</a>
      {/if}
    </div>
</div>
<!-- END tabs -->
</div>
<!-- END tab-container -->

<div id="search-bar">
{if $index_page}
<div style="float: right; position: relative; display: inline; right: 15px; font-size: 8pt; text-align: right;">
<b>Categories:</b> {$total_categories|number_format::0::$config.dec_point::$config.thousands_sep}<br />
<b>Links:</b> {$total_links|number_format::0::$config.dec_point::$config.thousands_sep}
</div>
<form method="GET" action="{$config.base_url}/search.php" style="display: inline; position: relative; left: 50px;">
{else}
<form method="GET" action="{$config.base_url}/search.php">
{/if}
<input type="text" name="s" size="40" value="{nocache}{$search_term|htmlspecialchars}{/nocache}">
<input type="image" src="{$config.base_url}/images/search-button.gif" id="search-button">
</form>

</div>
<!-- END search-bar -->

<br />

<div id="main-content">
<div id="centered-content" class="max-width">