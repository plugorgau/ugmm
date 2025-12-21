{extends file="base.tpl"}
{block name=pagetitle} - Editing Member Password{/block}
{block name=title}Edit Member Password{/block}
{block name=body}

  <h2>Change your PLUG password</h2>

  <p>Your PLUG password is used to access the members area of the
  website and login to PLUG machines. It is not associated with
  your PLUG mailing list subscriptions in any way. Changes to your
  PLUG password takes effect immediately.</p>

  Your password must:

  <ul>
    <li>be at least 8 characters in length</li>

    <li>not consist of solely lowercase letters or solely
    digits</li>
  </ul>Your password may:

  <ul>
    <li>contain any alphanumeric characters, punctuation,
    spaces</li>

    <li>be as long as you like (100 characters is not an
    unreasonable limit)</li>
  </ul>

  <p>Accounts detected with weak passwords will be disabled</p>

  <form method="post" action="" enctype="application/x-www-form-urlencoded" class="grid">
    <input name="nonce" value="{'editselfpassword'|nonce}" type="hidden">
    <input type="hidden" name="edit_selfpassword" value="1">

    <label for="password">Current Password</label>
    <div class="field">
      <input type="password" name="current_password" size="30">
    </div>

    <label for="newpassword">New Password</label>
    <div class="field">
      <input type="password" name="newpassword" size="30">
    </div>

    <label for="newpasswordconfirm">Verify Password</label>
    <div class="field">
      <input type="password" name="newpasswordconfirm" size="30">
    </div>

    <div class="actions">
      <input type="submit" name="go_go_button" value="Change Password">
      <input type="submit" name="oops_button" value="Cancel">
    </div>
  </form>

{/block}
