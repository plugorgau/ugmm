{extends file="base.tpl"}
{block name=pagetitle} - Editing Member Shell{/block}
{block name=title}Edit Member Shell{/block}
{block name=body}

  <h2>Shell Account Settings for {$member->uid}</h2>

  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded">
    <input type="hidden" name="nonce" value="{'editselfshell'|nonce}">
    <input type="hidden" name="edit_selfshell" value="1">

    {if $member->shellEnabled}
    <p>Your shell account is enabled</p>
    <p><input type="submit" name="disable_shell" value=
    "Disable Shell Account"></p>

    {else}
    <p>Your shell account is disabled</p>
    <p><input type="submit" name="enable_shell" value=
    "Enable Shell Account"></p>
    {/if}

    <h3>Shell Account Details</h3>

    <div class="grid">
      <div class="label">Username</div>
      <div class="field">{$member->uid}</div>

      <div class="label">Unix User ID</div>
      <div class="field">{$member->uidNumber}</div>

      <div class="label">Account expires</div>
      <div class="field">{$member->formattedexpiry}</div>

      <label for="account_shell">Shell</label>
      <div class="field">
        <select name="account_shell">
        {foreach from=$shells item=shell key=name}
          <option value="{$name}" {if $shell==$member->loginShell}selected{/if}>{$name}</option>
        {/foreach}
        </select>
      </div>

      <div class="actions">
        <input type="submit" name="go_go_button" value="Change Shell">
        <input type="submit" name="oops_button" value="Cancel">
      </div>
    </div>

    <!-- byobu-enable-->

    <p><b>Note:</b> It may take up to 10 minutes for these changes
    to take effect.</p>
  </form>

{/block}
