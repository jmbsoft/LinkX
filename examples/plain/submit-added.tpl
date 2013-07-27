{assign var=$page_title value="Add a Link"}
{include filename="global-header.tpl"}

<table align="center" width="800" cellpadding="0" cellspacing="0">
<tr>
<td>
<div class="infobar" style="margin-top: 8px; margin-bottom: 8px;">
Link Added
</div>
</td>
</tr>
</table>

<table align="center" width="800" cellpadding="3" cellspacing="2">
<tr>
<td colspan="2">
{* Display information when confirming links through e-mail *}
{if $status == 'unconfirmed'}
Thank you for submitting your link.  An e-mail message has been sent to {$link.email|htmlspecialchars} with instructions
on how you can now confirm and activate your link.  Your link will not become active until you follow the instructions
provided in that e-mail message.
{* Display information when requiring link approval *}
{elseif $status == 'pending'}
Thank you for submitting your link.  To verify the validity of new links, they must be reviewed by an administrator.  Once
your link has been reviewed you will receive a confirmation e-mail message with further information.
{* Display standard confirmation *}
{else}
Thank you for submitting your link.  Your link is now active and will appear in our link directory shortly.
{/if}
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Name</b>
</td>
<td>
{$link.name|htmlspecialchars}
</td>
</tr>
<tr>
<td width="150" align="right">
<b>E-mail Address</b>
</td>
<td>
{$link.email|htmlspecialchars}
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Site URL</b>
</td>
<td>
{$link.site_url|htmlspecialchars}
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Recip URL</b>
</td>
<td>
{$link.recip_url|htmlspecialchars}
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Title</b>
</td>
<td>
{$link.title|htmlspecialchars}
</td>
</tr>
<tr>
<td width="150" align="right" valign="top">
<b>Description</b>
</td>
<td>
{$link.description|htmlspecialchars}
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Keywords</b>
</td>
<td>
{$link.keywords|htmlspecialchars}
</td>
</tr>
<tr>
<td width="150" align="right" valign="top">
<b>Category</b>
</td>
<td>
{foreach var=part from=$category.path_parts}{$part.name|htmlspecialchars}{if $part.path != $category.path}/{/if}{/foreach}
</td>
</tr>

{if $link.password}
<tr>
<td width="150" align="right">
<b>Password</b>
</td>
<td>
{$link.password|htmlspecialchars}
</td>
</tr>
{/if}

{* Show the user defined fields *}
{foreach var=field from=$user_fields}
  {if $field.on_submit}
    {if $field.type == FT_CHECKBOX}
<tr>
<td width="150" align="right">
&nbsp;
</td>
<td>
{if $field.value}
<img src="{$config.base_url}/images/check.gif" border="0">
{else}
<img src="{$config.base_url}/images/uncheck.gif" border="0">
{/if}
<b>{$field.label|htmlspecialchars}</b>
</td>
</tr>
    {else}
<tr>
<td width="150" align="right">
<b>{$field.label|htmlspecialchars}</b>
</td>
<td>
{$field.value|htmlspecialchars}
</td>
</tr>
    {/if}
  {/if}
{/foreach}
</table>

{include filename="global-footer.tpl"}