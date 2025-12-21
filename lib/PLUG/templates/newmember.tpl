{extends file="base.tpl"}
{block name=pagetitle} - Add Member{/block}
{block name=title}Add New Member{/block}
{block name=body}

{if $usercreated}

<p>New member created. <a href="{$submenuitems.ctte.editmember.link}{$newmember->uidNumber}">Edit member {$newmember->uidNumber} to make payment</a>
</p>

{/if}
  <form method="post" action="" class="grid">
    <input type="hidden" name="newmember_form" value="1">
    <input type="hidden" name="nonce" value="{'newmember'|nonce}">

    <label for="givenName">First Name <span class="required">*</span></label>
    <div class="field">
      <input type="text" name="givenName" value="{$member.givenName|default}" size="30">
    </div>

    <label for="sn">Last Name</label>
    <div class="field">
      <input type="text" name="sn" value="{$member.sn|default}" size="30">
    </div>

    <label for="mail">E-mail Address <span class="required">*</span></label>
    <div class="field">
      <input type="email" name="mail" value="{$member.mail|default}" size="30">
    </div>

    <label for="street">Postal Address <span class="required">*</span></label>
    <div class="field">
      <input type="text" name="street" value="{$member.street|default}" size="50">
    </div>

    <label for="homePhone">Home Phone</label>
    <div class="field">
      <input type="text" name="homePhone" value="{$member.homePhone|default}" size="20">
    </div>

    <label for="pager">Work Phone</label>
    <div class="field">
      <td><input type="tel" name="pager" value="{$member.pager|default}" size="20">
    </div>

    <label for="mobile">Mobile Phone</label>
    <div class="field">
      <input type="tel" name="mobile" value="{$member.mobile|default}" size="20">
    </div>

    <label for="uid">Username <span class="required">*</span></label>
    <div class="field">
      <input type="text" name="uid" value="{$member.uid|default}" size="30">
    </div>

    <label for="password">Password <span class="required">*</span></label>
    <div class="field">
      <input type="password" name="password" value="{$member.password|default}">
    </div>

    <label for="vpassword">Verify Password <span class="required">*</span></label>
    <div class="field">
      <input type="password" name="vpassword" value="">
    </div>

    <label for="notes">Notes</label>
    <div class="field">
      <textarea name="notes" rows="3" cols="40">{$member.description|default}</textarea>
    </div>

    <div class="actions">
      <input type="submit" name="go_go_button" value="Add New Member">
      <input type="reset" name="reset_button" value="Cancel">
    </div>
  </form>

{/block}
