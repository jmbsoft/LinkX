{php} echo '<?xml  version="1.0" ?>'; {/php}

<rss version="2.0">
  <channel>
    <title>Most Visited Links</title>
    <description>Your site description</description>
    <link>http://www.yoursite.com/</link>

{links type=popular var=links amount=20}

{foreach var=$l from=$links}
    <item>
      <title>{$l.title|htmlspecialchars}</title>
      <link>{$l.site_url|htmlspecialchars}</link>
      <description>{$l.description|htmlspecialchars}</description>
      <pubDate>{$l.date_added|tdate::'D, d M Y H:i:s -0600'}</pubDate>
    </item>
{/foreach}

  </channel>
</rss>