<tr id="<?php echo $item['comment_id']; ?>" class="<?php echo $item['status']; ?>">
  <td valign="top">
    <input class="checkbox autocb" name="comment_id[]" value="<?php echo $item['comment_id']; ?>" type="checkbox">
  </td>
  <td valign="top" id="<?php echo $item['comment_id']; ?>_comment">
    <div style="margin-top: 4px; position: relative;">
    <b>Added:</b>&nbsp;
    <?php echo date(DF_SHORT, strtotime($item['date_added'])); ?>

    <span style="left: 225px; position: absolute;"><b>Submit IP:</b>&nbsp;
    <?php echo $item['submit_ip']; ?></span>

    <span style="left: 475px; position: absolute;"><b>E-mail:</b>&nbsp;
    <a href="mailto:<?php echo $item['email']; ?>"><?php echo $item['email']; ?></a></span>
    </div>

    <div style="margin-top: 4px; position: relative;">
    <b>Name:</b>&nbsp;
    <?php echo $item['name']; ?>

    <span style="left: 225px; position: absolute;"><b>Username:</b>&nbsp;
    <?php echo $item['username'] ? htmlspecialchars($item['username']) : '-'; ?></span>
    </div>

    <div style="margin-left: 20px; border: 1px solid #ffe7cb; background-color: #FFFFC8; padding: 5px; margin-top: 4px;">
    <?php echo $item['comment']; ?>
    </div>

  </td>
  <td style="text-align: right;" class="last" valign="top">
    <a href="index.php?r=lxShEditComment&comment_id=<?php echo urlencode($item['comment_id']); ?>" class="window function {title: 'Edit Comment'}">
    <img src="images/edit.png" width="12" height="12" alt="Edit" title="Edit"></a>

    <?php if( $item['status'] == 'pending' ): ?>
    <a href="javascript:void(0)" onclick="return approveSelected('<?php echo $item['comment_id']; ?>', 'approve')">
    <img src="images/check.png" border="0" width="12" height="12" alt="Approve Comment" title="Approve Comment" class="function"></a>
    <a href="javascript:void(0)" onclick="return rejectSelected('<?php echo $item['comment_id']; ?>', 'reject')">
    <img src="images/x.png" border="0" width="12" height="12" alt="Reject Comment" title="Reject Comment" class="function"></a>
    <?php
    endif;
    if( $item['username'] ):
    ?>
    <a href="index.php?r=lxShUsers&amp;field=username&amp;search=<?php echo urlencode($item['username']); ?>">
    <img src="images/user.png" alt="View User" title="View User" border="0" class="function"></a>
    <?php endif; ?>
    <a href="index.php?r=lxShSearchLinks&amp;field=link_id&amp;search=<?php echo urlencode($item['link_id']); ?>">
    <img src="images/go.png" alt="View Link" title="View Link" border="0" class="function"></a>
    <a href="javascript:void(0)" onclick="return deleteSelected('<?php echo $item['comment_id']; ?>');">
    <img src="images/trash.png" alt="Delete" title="Delete" border="0" class="function"></a>
</td>
</tr>
