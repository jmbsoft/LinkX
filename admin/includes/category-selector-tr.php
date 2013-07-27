<tr>
  <td class="last">
    <?php
    $path = unserialize(html_entity_decode($item['path_parts']));

    // Generate category path
    $parts = array();
    foreach( $path as $part )
    {
        $parts[] = htmlspecialchars($part['name'], ENT_QUOTES);
    }

    $item['full_path'] = join(' / ', $parts);

    ?>
    <span class="category-expander" onclick="expandCategoryDeep('<?php echo $item['category_id']; ?>')"><?php echo StringChopTooltip($item['full_path'], 100, true, ' ... '); ?></span>
    </span>
  </td>
</tr>