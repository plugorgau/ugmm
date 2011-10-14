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
          "{$member.expiry}" size="10" type="text" disabled></td>
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
    type="text"> (YYYY-MM-DD) (leave blank for "now").<br>
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
        "{$submenuitems.ctte.resendack.link}{$member.uidNumber}&payment_id={$payment.id}">Resend
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
  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded">
    <input name="shell_form" value="1" type="hidden">
    <input name="nonce" value="{'updateshelllock'|nonce}" type="hidden">        
    <input name="id" value="{$member.uidNumber}" type="hidden">  
    
{if ! $member.shellEnabled}
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
  
  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded">
    <input name="passwordlock_form" value="1" type="hidden">
    <input name="nonce" value="{'lockpassword'|nonce}" type="hidden">            
    <input name="id" value="{$member.uidNumber}" type="hidden">

    <input type="submit" name="force_pw_change" value="Disable password and force reset"/><p>This will disable the account by resetting the password to an invalid value and disable shell access. Please direct the user to <a href="resetpassword">Password Reset</a> to renable access.</p>
</form>

  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded">
    <input name="password_form" value="1" type="hidden">
    <input name="nonce" value="{'updatepassword'|nonce}" type="hidden">            
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

          <td><input name="go_go_button" value="Reset Password"
          type="submit"><br></td>
        </tr>
      </tbody>
    </table>

  </form>
  
  <em>Delete User Not Implemented at this time</em>
{*
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
*}
