{extends file="base.tpl"}
{block name=pagetitle} - Edit Member{/block}
{block name=title}Edit Member{/block}
{block name=body}

  <h3>Personal Details</h3>

  <form method="post" action="" enctype="application/x-www-form-urlencoded" class="grid">
    <input name="personals_form" value="1" type="hidden">
    <input name="nonce" value="{'editmember'|nonce}" type="hidden">
    <input name="id" value="{$member->uidNumber}" type="hidden">

    <div class="label">Member ID</div>
    <div class="field">{$member->uidNumber}</div>

    <label for="uid">Username</label>
    <div class="field">
      <input name="uid" value="{$member->uid}" size="30" type="text">
    </div>

    <label for="givenName">First Name</label>
    <div class="field">
      <input name="givenName" value="{$member->givenName}" size="30" type="text">
    </div>

    <label for="sn">Last Name</label>
    <div class="field">
      <input name="sn" value="{$member->sn}" size="30" type="text">
    </div>

    <label for="mail">E-mail Address</label>
    <div class="field">
      <input name="mail" value="{$member->mail}" size="30" type="email">
    </div>

    <label for="street">Postal Address</label>
    <div class="field">
      <input name="street" value="{$member->street|default}" size="50" type="text">
    </div>

    <label for="homePhone">Home Phone</label>
    <div class="field">
      <input name="homePhone" size="20" type="tel" value="{$member->homePhone|default}">
    </div>

    <label for="pager">Work Phone</label>
    <div class="field">
      <input name="pager" value="{$member->pager|default}" size="20" type="tel">
    </div>

    <label for="mobile">Mobile Phone</label>
    <div class="field">
      <input name="mobile" value="{$member->mobile|default}" size="20" type="tel">
    </div>

    <label for="membership_expiry">Membership Expires</label>
    <div class="field">
      <input name="membership_expiry" value="{$member->expiry}" size="10" type="text" disabled>
    </div>

    <label>Groups</label>
    <div class="field">{foreach from=$member->groups item=group name=groups}{$group}{if ! $smarty.foreach.groups.last},{/if}
          {/foreach}</div>

    <label for="notes">Notes</label>
    <div class="field">
      <textarea name="notes" rows="3" cols="40">{$member->description|default}</textarea>
    </div>

    <div class="actions">
      <input name="go_go_button" value="Update Personal Details" type="submit">
      <input name="reset_button" value="Reset Fields" type="reset">
    </div>
  </form>

  <h3>Make Membership Payment</h3>

  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded">
    <input name="payment_form" value="1" type="hidden">
    <input name="nonce" value="{'makepayment'|nonce}" type="hidden">
    <input name="id" value="{$member->uidNumber}" type="hidden">
    Receive payment for <label><input name="membership_type" value=
    "1" checked="checked" type="radio">Full ({$FULL_AMOUNT}/yr)</label>
    <label><input name="membership_type" value="2" type=
    "radio">Concession ({$CONCESSION_AMOUNT}/yr)</label> for <input name="years"
    value="1" size="2" type="number"> year(s).<br>
    Backdate this payment to <input name="payment_date" size="10"
    type="date"> (leave blank for "now").<br>
    Receipt # (or comment) <input name="receipt_number" size="30"
    type="text"><br>
    <input name="go_go_button" value="Make Payment" type="submit">
    <label><input name="payment_ack" value="1" checked="checked"
    type="checkbox">E-mail acknowledgement to member</label><br>

    <div>
      <input name=".cgifields" value="payment_ack" type=
      "hidden"><input name=".cgifields" value="membership_type"
      type="hidden">
    </div>
  </form>

  <h4>Past Payments</h4>

  <table id="past-payments" class="payments">
    <thead>
      <tr>
        <th>Date</th>
        <th>Amount</th>
        <th>Membership Type</th>
        <th># Years</th>
        <th>Description</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
{foreach from=$member->payments item=payment}
      <tr>
        <td>{$payment->formatteddate}</td>
        <td>{$payment->formattedamount}</td>
        <td>{$payment->formattedtype}</td>
        <td>{$payment->years}</td>
        <td>{$payment->description}</td>
        <td><a href=
        "{$submenuitems.ctte.resendack.link}{$member->uidNumber}&payment_id={$payment->id}">Resend
        Ack</a></td>
      </tr>
{/foreach}
    </tbody>
  </table>

  <h3>E-mail Forwarding</h3>

  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded">
    <input name="email_form" value="1" type="hidden">
    <input name="nonce" value="{'updateemailforwarding'|nonce}" type="hidden">
    <input name="id" value="{$member->uidNumber}" type="hidden">
    E-mail forwarded to <input name="email_forward" value="{if isset($member->mailForward) and $member->mailForward}{$member->mailForward}{/if}" size="30" type="email">
    <input name="go_go_button" value="Update Forwarding" type="submit"><br>
    A blank address means that email is delivered to their PLUG
    home directory. (Currently a blank email will deliver email to
    their email address in the user details section)
  </form>

  <h3>Shell Access ({$member->uid})</h3>
  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded">
    <input name="shell_form" value="1" type="hidden">
    <input name="nonce" value="{'updateshelllock'|nonce}" type="hidden">
    <input name="id" value="{$member->uidNumber}" type="hidden">

{if ! $member->shellEnabled}
  This users shell account is currently disabled.

 <input name="unlock_button" value="Enable Shell Account" type="submit">
{else}
  This member's shell account is enabled.

 <input name="lock_button" value="Disable Shell Account" type="submit">
{/if}

  </form>

  <h3>Reset Password</h3>

  <p>It is recommended that you force users to change their password using the password reset facility. You can direct the user to <a href="resetpassword">Password Reset</a> or can change the password to something random and force a reset via email.</p>
  <p>If locking an account due to abuse, disable the shell account as well</p>

  <form method="post" action="" enctype="application/x-www-form-urlencoded">
    <input name="passwordlock_form" value="1" type="hidden">
    <input name="nonce" value="{'lockpassword'|nonce}" type="hidden">
    <input name="id" value="{$member->uidNumber}" type="hidden">

    <input type="submit" name="force_pw_change" value="Disable password and force reset"/><p>This will disable the account by resetting the password to an invalid value and disable shell access. Please direct the user to <a href="resetpassword">Password Reset</a> to renable access.</p>
</form>

  <form method="post" action="" enctype="application/x-www-form-urlencoded" class="grid">
    <input name="password_form" value="1" type="hidden">
    <input name="nonce" value="{'updatepassword'|nonce}" type="hidden">
    <input name="id" value="{$member->uidNumber}" type="hidden">

    <label for="new_password">New Password</label>
    <div class="field">
      <input name="new_password" size="20" type="password">
    </div>

    <label for="verify_password">Verify Password</label>
    <div class="field">
      <input name="verify_password" size="20" type="password">
    </div>

    <div class="actions">
      <input name="go_go_button" value="Reset Password" type="submit">
    </div>
  </form>

  <em>Delete User Not Implemented at this time</em>
{*
  <h3>Delete Member</h3>

  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded">
    <input name="delete_form" value="1" type="hidden"> <input name=
    "id" value="{$member->uidNumber}" type="hidden"> If you are sure
    you want to delete this member, enter the text "<tt>Yes I am
    sure.</tt>" into the box below, and press Delete.<br>
    Very rarely should actual members be deleted. Instead accounts
    should be locked, or accounts and memberships will lapse and do
    so automatically.<br>
    <input name="delete_verification" size="30" type="text">
    <input name="go_go_button" value="Delete Member" type=
    "submit"><br>
  </form>
*}

{/block}
