{assign var=$page_title value="Edit a Link"}
{include filename="global-header.tpl"}

<table align="center" width="800" cellpadding="0" cellspacing="0">
<tr>
<td>
<div class="infobar" style="margin-top: 8px; margin-bottom: 8px;">
Edit a Link
</div>
</td>
</tr>
</table>

<form method="POST" action="{$config.base_url}/submit.php">

<table align="center" width="800" cellpadding="3" cellspacing="2">
{if $errors}
<tr>
<td colspan="2" class="error">
{$errors}
</td>
</tr>
{/if}
<tr>
<td width="150" align="right">
<b>Name</b>
</td>
<td>
<input type="text" size="40" name="name" value="{$link.name|htmlspecialchars}" />
</td>
</tr>
<tr>
<td width="150" align="right">
<b>E-mail Address</b>
</td>
<td>
<input type="text" size="50" name="email" value="{$link.email|htmlspecialchars}" />
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Site URL</b>
</td>
<td>
<input type="text" size="80" name="site_url" value="{$link.site_url|htmlspecialchars}" />
</td>
</tr>
<tr>
<td width="150" align="right" valign="top">
<b>Recip URL</b>
</td>
<td>
<input type="text" size="80" name="recip_url" value="{$link.recip_url|htmlspecialchars}" /><br />
<span class="small">This is the page at your site where you will be placing the link to our site</span>
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Title</b>
</td>
<td>
<input type="text" size="70" name="title" value="{$link.title|htmlspecialchars}" />
</td>
</tr>
<tr>
<td width="150" align="right" valign="top">
<b>Description</b>
</td>
<td>
<textarea name="description" rows="6" cols="90">{$link.description|htmlspecialchars}</textarea>
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Keywords</b>
</td>
<td>
<input type="text" size="80" name="keywords" value="{$link.keywords|htmlspecialchars}" />
</td>
</tr>
<tr>
<td width="150" align="right" valign="top">
<b>Category</b>
</td>
<td>
{foreach var=category from=$categories}
{foreach var=part from=$category.path_parts}{$part.name|htmlspecialchars}{if $part.path != $category.path}/{/if}{/foreach}<br />
{/foreach}
</td>
</tr>

{* Allow user to update password *}
{if !$config.user_for_links && !$account}
<tr>
<td width="150" align="right" valign="top">
<b>Password</b>
</td>
<td>
<input type="password" size="20" name="password" value="{$link.password|htmlspecialchars}" />
<br />
<span class="small">Only fill this in if you want to change the current password</span>
</td>
</tr>
<tr>
<td width="150" align="right">
<b>Confirm Password</b>
</td>
<td>
<input type="password" size="20" name="confirm_password" value="{$link.confirm_password|htmlspecialchars}" />
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
{field from=$field value=$field.value}
<b><label for="{$field.name|htmlspecialchars}">{$field.label|htmlspecialchars}</label></b>
</td>
</tr>
    {else}
<tr>
<td width="150" align="right">
<b>{$field.label|htmlspecialchars}</b>
</td>
<td>
{field from=$field value=$field.value}
</td>
</tr>
    {/if}
  {/if}
{/foreach}

<tr>
<td align="center" colspan="2">
<button type="submit">Update Link</button>
</td>
</tr>
</table>

<input type="hidden" name="r" value="edit">
<input type="hidden" name="editing" value="1">
<input type="hidden" name="link_id" value="{$link.link_id|htmlspecialchars}">
{if $noaccount}
<input type="hidden" name="noaccount" value="1">
<input type="hidden" name="login_site_url" value="{$login_site_url|htmlspecialchars}">
<input type="hidden" name="login_email" value="{$login_email|htmlspecialchars}">
<input type="hidden" name="login_password" value="{$login_password|htmlspecialchars}">
{/if}
</form>

{include filename="global-footer.tpl"}