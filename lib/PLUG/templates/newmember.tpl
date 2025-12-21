{extends file="base.tpl"}
{block name=pagetitle} - Add Member{/block}
{block name=title}Add New Member{/block}
{block name=body}

{if $usercreated}

<p>New member created. <a href="{$submenuitems.ctte.editmember.link}{$newmember->uidNumber}">Edit member {$newmember->uidNumber} to make payment</a>
</p>

{/if}
  <form method="post" action="" enctype="application/x-www-form-urlencoded" class="grid">
    <input name="newmember_form" value="1" type="hidden">
    <input name="nonce" value="{'newmember'|nonce}" type="hidden">

    <label for="givenName">First Name <span class="required">*</span></label>
    <div class="field">
      <input name="givenName" value="{$member.givenName|default}" size="30" type="text">
    </div>

    <label for="sn">Last Name</label>
    <div class="field">
      <input name="sn" value="{$member.sn|default}" size="30" type="text">
    </div>

    <label for="mail">E-mail Address <span class="required">*</span></label>
    <div class="field">
      <input name="mail" value="{$member.mail|default}" size="30" type="text">
    </div>

    <label for="street">Postal Address <span class="required">*</span></label>
    <div class="field">
      <input name="street" value="{$member.street|default}" size="50" type="text">
    </div>

    <label for="homePhone">Home Phone</label>
    <div class="field">
      <input name="homePhone" size="20" type="text" value="{$member.homePhone|default}">
    </div>

    <label for="pager">Work Phone</label>
    <div class="field">
      <td><input name="pager" value="{$member.pager|default}" size="20" type="text">
    </div>

    <label for="mobile">Mobile Phone</label>
    <div class="field">
      <input name="mobile" value="{$member.mobile|default}" size="20" type="text">
    </div>

    <label for="uid">Username <span class="required">*</span></label>
    <div class="field">
      <input name="uid" value="{$member.uid|default}" size="30" type="text">
    </div>

    <label for="password">Password <span class="required">*</span></label>
    <div class="field">
      <input name="password" value="{$member.password|default}" type="password">
    </div>

    <label for="vpassword">Verify Password <span class="required">*</span></label>
    <div class="field">
      <input name="vpassword" value="" type="password">
    </div>

    <label for="notes">Notes</label>
    <div class="field">
      <textarea name="notes" rows="3" cols="40">{$member.description|default}</textarea>
    </div>

    <div class="actions">
      <input name="go_go_button" value="Add New Member" type="submit">
      <input name="reset_button" value="Cancel" type="reset">
    </div>
  </form>

{/block}
