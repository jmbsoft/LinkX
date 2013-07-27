{php} echo '<?xml  version="1.0" ?>'; {/php}

<rss version="2.0">
  <channel>
    <title><![CDATA[ {foreach var=part from=$this_category.path_parts counter=$counter}{if $counter > 1} / {/if}{$part.name|htmlspecialchars}{/foreach} ]]></title>
    <description>{$this_category.description|htmlspecialchars}</description>
    <link>http://www.yoursite.com/</link>

{links var=featured_links type=featured}

{foreach var=$l from=$featured_links}
    <item>
      <title>{$l.title|htmlspecialchars}</title>
      <link>{$l.site_url|htmlspecialchars}</link>
      <description>{$l.description|htmlspecialchars}</description>
      <pubDate>{$l.date_added|tdate::'D, d M Y H:i:s -0600'}</pubDate>
    </item>
{/foreach}

{links var=regular_links type=regular perpage=20}

{foreach var=$l from=$regular_links}
    <item>
      <title>{$l.title|htmlspecialchars}</title>
      <link>{$l.site_url|htmlspecialchars}</link>
      <description>{$l.description|htmlspecialchars}</description>
      <pubDate>{$l.date_added|tdate::'D, d M Y H:i:s -0600'}</pubDate>
    </item>
{/foreach}

  </channel>
</rss>