<tr id="<?php echo $item['news_id']; ?>">
  <td valign="top">
    <input type="checkbox" class="checkbox autocb" name="news_id[]" value="<?php echo $item['news_id']; ?>">
  </td>
  <td valign="top">
    <b><?php echo StringChopTooltip($item['headline'], 100); ?></b><br />
    <div style="margin-left: 20px;"><?php echo nl2br(StringChop($item['body'], 500)); ?></div>
  </td>
  <td valign="top">
    <?php echo date(DF_SHORT, strtotime($item['date_added'])); ?>
  </td>
  <td style="text-align: right;" class="last" valign="top">
    <a href="index.php?r=lxShEditNews&news_id=<?php echo urlencode($item['news_id']); ?>" class="window function {title: 'Edit News Item'}">
    <img src="images/edit.png" width="12" height="12" alt="Edit" title="Edit"></a>
    <a href="" onclick="return deleteSelected('<?php echo $item['news_id']; ?>')" class="function">
    <img src="images/trash.png" width="12" height="12" alt="Delete" title="Delete"></a>
  </td>
</tr>