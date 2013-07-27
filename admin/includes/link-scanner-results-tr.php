<tr id="<?php echo $item['link_id']; ?>">
  <td>
    <?php if( $item['action'] != 'Deleted' && $item['action'] != 'Blacklisted' ): ?>
    <input type="checkbox" class="checkbox autocb" name="link_id[]" value="<?php echo $item['link_id']; ?>">
    <?php endif; ?>
  </td>
  <td valign="top">
    <a href="<?php echo $item['site_url']; ?>" target="_blank"><?php echo StringChopTooltip($item['site_url'], 90, TRUE); ?></a><br />
    <?php echo $item['message']; ?>
  </td>
  <td valign="top" class="r-<?php echo strtolower($item['action']); ?>">
    <?php echo $item['action']; ?>
  </td>
  <td valign="top">
    <?php echo date(DF_SHORT, strtotime($item['date_scanned'])); ?>
  </td>
  <td style="text-align: right;" class="last" valign="top">
    <?php if( $item['action'] != 'Deleted' && $item['action'] != 'Blacklisted' ): ?>
    <img src="images/search.png" width="12" height="12" alt="Scan" title="Scan" class="click function" onclick="openScan('<?php echo $item['link_id']; ?>')">

    <a href="" onclick="return doToSelected('<?php echo $item['link_id']; ?>', 'active')" class="function">
    <img src="images/disabled.png" width="12" height="12" alt="Click to activate link" title="Click to activate link"></a>

    <a href="" onclick="return doToSelected('<?php echo $item['link_id']; ?>', 'disabled')" class="function">
    <img src="images/enabled.png" width="12" height="12" alt="Click to disable link" title="Click to disable link"></a>

    <a href="index.php?r=lxShEditLink&link_id=<?php echo urlencode($item['link_id']); ?>" class="window function {title: 'Edit Link'}">
    <img src="images/edit.png" width="12" height="12" alt="Edit" title="Edit"></a>
    <a href="index.php?r=lxShMailLink&link_id[]=<?php echo urlencode($item['link_id']); ?>" class="window function {title: 'E-mail Link Submitter'}">
    <img src="images/mail.png" width="12" height="12" alt="E-mail" title="E-mail"></a>
    <a href="" onclick="return deleteSelected('<?php echo $item['link_id']; ?>')" class="function">
    <img src="images/trash.png" width="12" height="12" alt="Delete" title="Delete"></a>
    <?php endif; ?>
  </td>
</tr>