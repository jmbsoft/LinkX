<tr id="<?php echo $item['category_id']; ?>">
  <td>
    <input class="checkbox autocb" name="category_id[]" value="<?php echo $item['category_id']; ?>" type="checkbox">
  </td>
  <td>
    <?php
    $path = unserialize($original['path_parts']);

    // Generate category path
    $parts = array();
    foreach( $path as $part )
    {
        $parts[] = htmlspecialchars($part['name'], ENT_QUOTES);
    }

    $item['full_path'] = join(' / ', $parts);

    ?>
    <?php echo StringChopTooltip($item['full_path'], 100, true, ' ... '); ?>
    </span>
  </td>
  <td>
    <?php echo number_format($item['subcategories'], 0, $C['dec_point'], $C['thousands_sep']); ?>
  </td>
  <td>
    <?php echo number_format($item['links'], 0, $C['dec_point'], $C['thousands_sep']); ?>
  </td>
  <td style="text-align: right;" class="last">
    <a href="index.php?r=lxShBrowse&c=<?php echo urlencode($item['category_id']); ?>">
    <img src="images/go.png" border="0" alt="Browse" title="Browse" class="function"></a>
    <a href="index.php?r=lxShEditCategory&category_id=<?php echo urlencode($item['category_id']); ?>" class="window function {title: 'Edit Category'}">
    <img src="images/edit.png" alt="Edit Category" title="Edit Category" border="0" class="function"></a>
    <a href="javascript:void(0)" onclick="return deleteSelected('<?php echo htmlspecialchars($item['category_id']); ?>');">
    <img src="images/trash.png" alt="Delete" title="Delete" border="0" class="function"></a>
  </td>
</tr>