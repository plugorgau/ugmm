  <h3>Personal Details</h3>

  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded">
    <input name="personals_form" value="1" type="hidden">
    <input name="nonce" value="{'editmember'|nonce}" type="hidden">    
    <input name="id" value="{$member.uidNumber}" type="hidden">

    <table border="0">
      <tbody>
        <tr>
          <th>Member ID</th>

          <td>{$member.uidNumber}</td>
        </tr>
        
        <tr>
          <th>Username</th>

          <td><input name="uid" value="{$member.uid}"
          size="30" type="text"></td>
        </tr>        

        <tr>
          <th>First Name</th>

          <td><input name="first_name" value="{$member.givenName}"
          size="30" type="text"></td>
        </tr>

        <tr>
          <th>Last Name</th>

          <td><input name="last_name" value="{$member.sn}" size=
          "30" type="text"></td>
        </tr>

        <tr>
          <th>E-mail Address</th>

          <td><input name="email_address" value="{$member.mail}" size="30" type="text"></td>
        </tr>

        <tr>
          <th>Postal Address</th>

          <td><input name="street_address" value="{$member.street}"
          size="50" type="text"></td>
        </tr>

        <tr>
          <th>Home Phone</th>

          <td><input name="home_phone" size="20" type="text" value=
          "{$member.homePhone}"></td>
        </tr>

        <tr>
          <th>Work Phone</th>

          <td><input name="work_phone" value="{$member.pager}"
          size="20" type="text"></td>
        </tr>

        <tr>
          <th>Mobile Phone</th>

          <td><input name="mobile_phone" value="{$member.mobile}"
          size="20" type="text"></td>
        </tr>

        <tr>
          <th>Membership Expires</th>

          <td><input name="membership_expiry" value=
          "{$member.expiry}" size="10" type="text" disabled> (dd/mm/yy)</td>
        </tr>

        <tr>
          <th>Groups</th>

          <td>{foreach from=$member.groups item=group name=groups}{$group}{if ! $smarty.foreach.groups.last},{/if}
          {/foreach}</td>
        </tr>

        <tr>
          <th valign="top">Notes</th>

          <td>
          <textarea name="notes" rows="3" cols="40">
{$member.description}
</textarea></td>
        </tr>
      </tbody>
    </table><input name="go_go_button" value=
    "Update Personal Details" type="submit"> <input name=
    "reset_button" value="Reset Fields" type="reset">
  </form>

  <h3>Make Membership Payment</h3>

  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded">
    <input name="payment_form" value="1" type="hidden">
    <input name="nonce" value="{'makepayment'|nonce}" type="hidden">    
    <input name="id" value="{$member.uidNumber}" type="hidden">
    Receive payment for <label><input name="membership_type" value=
    "1" checked="checked" type="radio">Full ({$FULL_AMOUNT}/yr)</label>
    <label><input name="membership_type" value="2" type=
    "radio">Concession ({$CONCESSION_AMOUNT}/yr)</label> for <input name="years"
    value="1" size="2" type="text"> year(s).<br>
    Backdate this payment to <input name="payment_date" size="10"
    type="text"> (dd/mm/yy) (leave blank for "now").<br>
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

  <table>
    <tbody>
      <tr bgcolor="#8888AA">
        <th>Date</th>

        <th>Amount</th>

        <th>Membership Type</th>

        <td># Years</td>

        <th>Description</th>

        <th></th>
      </tr>
{foreach from=$member.payments item=payment} 
      <tr bgcolor="#DDDDFF">
        <td>{$payment.formatteddate}</td>

        <td>{$payment.formattedamount}</td>

        <td>{$payment.formattedtype}</td>

        <td>{$payment.years}</td>

        <td>{$payment.description}</td>

        <td><a href=
        "resendack?member_id={$member.uidNumber}&payment_id={$payment.id}">Resend
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
    <input name="id" value="{$member.uidNumber}" type="hidden">
    E-mail forwarded to <input name="email_forward" value="{$member.mailForward}" size="30" type="text">
    <input name="go_go_button" value="Update Forwarding" type="submit"><br>
    A blank address means that email is delivered to their PLUG
    home directory. (Currently a blank email will deliver email to
    their email address in the user details section)
  </form>

  <h3>Shell Access ({$member.uid})</h3>
  
{if $member.loginShell eq "/bin/false"}
  This users shell account is currently locked.
  
  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded">
    <input name="unlock_form" value="1" type="hidden"> <input name=
    "id" value="{$member.uidNumber}" type="hidden"> <input name=
    "go_go_button" value="Unlock Account" type="submit">
  </form>
{else}  
  This member's account is not
  locked. The member has enabled their account.

  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded">
    <input name="lock_form" value="1" type="hidden"> <input name=
    "id" value="{$member.uidNumber}" type="hidden"> <input name=
    "go_go_button" value="Lock Account" type="submit">
  </form>
{/if}

  <h3>Reset Password</h3>

  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded">
    <input name="password_form" value="1" type="hidden">
    <input name="id" value="{$member.uidNumber}" type="hidden">

    <table>
      <tbody>
        <tr>
          <th>New Password</th>

          <td><input name="new_password" size="20" type=
          "password"></td>
        </tr>

        <tr>
          <th>Verify Password</th>

          <td><input name="verify_password" size="20" type=
          "password"></td>
        </tr>

        <tr>
          <td></td>

          <td><label><input name="force_pw_change" value="1" type=
          "checkbox">Force change on next login</label></td>
        </tr>

        <tr>
          <td></td>

          <td><input name="go_go_button" value="Reset Password"
          type="submit"><br></td>
        </tr>
      </tbody>
    </table>

    <div>
      <input name=".cgifields" value="force_pw_change" type=
      "hidden">
    </div>
  </form>

  <h3>Delete Member</h3>

  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded">
    <input name="delete_form" value="1" type="hidden"> <input name=
    "id" value="{$member.uidNumber}" type="hidden"> If you are sure
    you want to delete this member, enter the text "<tt>Yes I am
    sure.</tt>" into the box below, and press Delete.<br>
    Very rarely should actual members be deleted. Instead accounts
    should be locked, or accounts and memberships will lapse and do
    so automatically.<br>
    <input name="delete_verification" size="30" type="text">
    <input name="go_go_button" value="Delete Member" type=
    "submit"><br>
  </form>

