{extends file="base.tpl"}
{block name=pagetitle} - Editing Member Details{/block}
{block name=title}Edit Member Details{/block}
{block name=body}

  <h2>Editing member details for {$member->displayName}</h2>

  <h3>Personal Details</h3>

  <ul>
    <li>Required fields are marked with a <sup>*</sup>.</li>
  </ul>

  <form method="post" action="" class="grid">
    <input type="hidden" name="nonce" value="{'editselfdetails'|nonce}">
    <input type="hidden" name="edit_selfmember" value="1">
    <input type="hidden" name="forced" value="0">

    <label for="email_address">E-mail Address <span class="required">*</span></label>
    <div class="field">
      <input type="email" name="email_address" value="{$member->mail}" size="30">
    </div>

    <label for="street_address">Postal Address <span class="required">*</span></label>
    <div class="field">
      <input type="text" name="street_address" value="{$member->street|default}" size="50">
    </div>

    <label for="home_phone">Home Phone</label>
    <div class="field">
      <input type="tel" name="home_phone" value="{$member->homePhone|default}" size="20">
    </div>

    <label for="work_phone">Work Phone</label>
    <div class="field">
      <input type="tel" name="work_phone" value="{$member->pager|default}" size="20">
    </div>

    <label for="mobile_phone">Mobile Phone</label>
    <div class="field">
      <input type="tel" name="mobile_phone" value="{$member->mobile|default}" size="20">
    </div>

    <div class="actions">
      <input type="submit" name="go_go_button" value="Update">
      <input type="submit" name="oops_button" value="Cancel">
    </div>
  </form>

{/block}
