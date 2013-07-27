<tr id="<?php echo $item['report_id']; ?>">
  <td valign="top" style="padding-top: 6px;">
    <input class="checkbox autocb" name="report_id[]" value="<?php echo $item['report_id']; ?>" type="checkbox">
  </td>
  <td valign="top">
    <b>Report Message:</b>
    <div style="margin-left: 20px; border: 1px solid #ffe7cb; background-color: #FFFFC8; padding: 5px; margin-top: 4px;">
    <?php echo nl2br(trim($item['message'])); ?><br />
    </div>

    <div style="margin-top: 4px; position: relative;">
    <b>Added:</b>&nbsp;
    <?php echo date(DF_SHORT, strtotime($item['date_added'])); ?>

    <span style="left: 225px; position: absolute;"><b>Submitter IP:</b>&nbsp;
    <?php echo $item['submit_ip']; ?></span>
    </div>

    <div style="margin-top: 4px; position: relative;">
    <b>Link URL:</b>&nbsp;
    <a href="<?php echo $item['site_url']; ?>" target="_blank"><?php echo $item['site_url']; ?></a>
    </div>

    <div style="margin-top: 4px; position: relative;">
    <b>Title:</b>&nbsp;
    <?php echo $item['title']; ?>
    </div>

    <div style="margin-top: 4px; position: relative;">
    <b>Description:</b>&nbsp;
    <?php echo $item['description']; ?>
    </div>

  </td>
  <td style="text-align: right;" class="last" valign="top">
    <a href="index.php?r=lxShScanLink&link_id=<?php echo urlencode($item['link_id']); ?>" class="window {title: 'Scan Link'}">
    <img src="images/search.png" alt="Scan Link" title="Scan Link" class="function"></a>
    <a href="index.php?r=lxShSearchLinks&amp;field=link_id&amp;search=<?php echo urlencode($item['link_id']); ?>">
    <img src="images/go.png" alt="View Link" title="View Link" border="0" class="function"></a>
    <img src="images/blacklist.png" alt="Blacklist" title="Blacklist" border="0" onclick="processReport('<?php echo $item['report_id']; ?>', 'blacklist');" class="function click">
    <img src="images/x.png" alt="Delete" title="Delete" border="0" onclick="processReport('<?php echo $item['report_id']; ?>', 'delete');" class="function click">
    <img src="images/trash.png" alt="Ignore" title="Ignore" border="0" onclick="processReport('<?php echo $item['report_id']; ?>', 'ignore');" class="function click">
  </td>
</tr>