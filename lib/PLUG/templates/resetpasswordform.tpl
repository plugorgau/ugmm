{extends file="base.tpl"}
{block name=pagetitle} - Reset Password{/block}
{block name=title}Reset Password{/block}
{block name=body}

{if $successform}
Please continue to <a href="memberself">login page to login</a> with your new password
{* TODO: link to login page *}

{elseif $resetform}
<p>Set a new password for {$username}.</p>
<form method="post" action="" class="grid">
  <input type="hidden" name="newpasswordreset_form" value="1">

  <label for="newpassword">Password</label>
  <div class="field">
    <input type="password" name="newpassword" value=""/>
  </div>

  <label for="newpasswordconfirm">Password again</label>
  <div class="field">
    <input type="password" name="newpasswordconfirm" value=""/>
  </div>

  <div class="actions">
    <input type="submit" value="Change Password"/>
  </div>
</form>
{else}
<form method="post" action="" class="grid">
  <input type="hidden" name="resetpassword_form" value="1">

  <label for="email">Email Address</label>
  <div class="field">
    <input type="email" name="email" value=""/>
  </div>

  <div class="actions">
    <input type="submit" value="Send Reset Email"/>
  </div>
</form>
{/if}

{/block}