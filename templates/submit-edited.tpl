{assign var=$page_title value="Edit a Link"}
{include filename="global-header.tpl"}

<div class="bar">
<div class="bar-left">
<div class="bar-text-left">
Link Updated
</div>
</div>
<div class="bar-right">
</div>
</div>

<div class="content-section">
<table align="center" width="100%" cellpadding="3" cellspacing="2">
<tr>
<td colspan="2">
{* Display information that link edits must be approved *}
{if $config.approve_link_edits}
Thank you for updating your link.  To verify the validity of modified links, they must be reviewed by an administrator.  Once
your link changes have been reviewed and approved, the updates will appear in our link directory.
{* Display standard confirmation *}
{else}
Thank you for updating your link.  The changes to your link will appear in our link directory shortly.
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
{foreach var=category from=$categories}
{foreach var=part from=$category.path_parts}{$part.name|htmlspecialchars}{if $part.path != $category.path}/{/if}{/foreach}
{/foreach}
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
  {if $field.on_edit}
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
</div>

{include filename="global-footer.tpl"}
