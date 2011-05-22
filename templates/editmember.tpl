<h3>Personal Details</h3>
<form method="post" action="/members/committee/membership-edit?id=162" enctype="application/x-www-form-urlencoded">
<input name="personals_form" value="1" type="hidden"><input name="id" value="162" type="hidden"><table border="0">
<tbody>
<tr>
    <th>Member ID</th>   
    <td>{$member.uidNumber}</td>
</tr>
<tr>
    <th>First Name</th>
    <td><input name="first_name" value="{$member.givenName}" size="30" type="text"></td>
</tr>
<tr>
    <th>Last Name</th>
    <td><input name="last_name" value="{$member.sn}" size="30" type="text"></td>
</tr>
<tr>
    <th>E-mail Address</th>
    <td>{foreach from=$member.mail item=mail}<input name="email_address[]" value="{$mail}" size="30" type="text"><br/>{/foreach}</td>
</tr>
<tr>
    <th>Postal Address</th>
    <td><input name="street_address" value="{$member.street}" size="50" type="text"></td>
</tr>
<tr>
    <th>Home Phone</th>
    <td><input name="home_phone" size="20" type="text" value="{$member.homePhone}"></td>
</tr>
<tr>
    <th>Work Phone</th>
    <td><input name="work_phone" value="{$member.pager}" size="20" type="text"></td>
</tr>
<tr>
    <th>Mobile Phone</th>
    <td><input name="mobile_phone" value="{$member.mobile}" size="20" type="text"></td>
</tr>
<tr>
    <th>Membership Expires</th>
    <td><input name="membership_expiry" value="{$member.expiry}" size="10" type="text"> (dd/mm/yy)</td>
</tr>
<tr>
    <th>Groups</th>
    <td>{foreach from=$member.groups item=group}{$group}, {/foreach}</td>
</tr>
<tr><th valign="top">Notes</th>
<td><textarea name="notes" rows="3" cols="40">{$member.description}</textarea></td>
</tr></tbody></table><input name="go_go_button" value="Update Personal Details" type="submit"> <input name="reset_button" value="Reset Fields" type="reset"> <input name="oops_button" value="Cancel" type="submit"></form>

<h3>Make Membership Payment</h3>

<form method="post" action="/members/committee/membership-edit?id=162" enctype="application/x-www-form-urlencoded">

<input name="payment_form" value="1" type="hidden"><input name="id" value="162" type="hidden">Receive payment for <label><input name="membership_type" value="1" checked="checked" type="radio">Full ($10.00/yr)</label> <label><input name="membership_type" value="2" type="radio">Concession ($5.00/yr)</label> for <input name="years" value="1" size="2" type="text"> year(s).<br>
Backdate this payment to <input name="payment_date" size="10" type="text"> (dd/mm/yy) (leave blank for "now").<br>
Receipt # (or comment) <input name="receipt_number" size="30" type="text"><br>
<input name="go_go_button" value="Make Payment" type="submit"> <label><input name="payment_ack" value="1" checked="checked" type="checkbox">E-mail acknowledgement to member</label><br>

<div><input name=".cgifields" value="payment_ack" type="hidden"><input name=".cgifields" value="membership_type" type="hidden"></div></form>


<h4>Past Payments</h4>
<table><tbody>

<tr bgcolor="#8888aa">
    <th>Date</th>
    <th>Amount</th>
    <th>Membership Type</th>
    <td># Years</td>
    <th>Description</th>
    <th></th>
</tr>
{foreach from=$member.payments item=payment}
<tr bgcolor="#ddddff">
    <td>{$payment.date}</td>
    <td>{$payment.amount}</td>
    <td>{$payment.type}</td>
    <td>{$payment.years}</td>
    <td>{$payment.description}</td>
    <td><a href="resend-payment-ack?member_id=162&amp;payment_id=0">Resend Ack</a></td>
</tr>
{/foreach}
</tbody></table>

<h3>E-mail Forwarding</h3>
<p></p><form method="post" action="/members/committee/membership-edit?id=162" enctype="application/x-www-form-urlencoded">
<input name="email_form" value="1" type="hidden"><input name="id" value="162" type="hidden">E-mail forwarded to <input name="email_forward" value="weirdit@gmail.com" size="30" type="text"> <input name="go_go_button" value="Update Forwarding" type="submit"><br>
A blank address means that email is delivered to their PLUG home directory.<p></p></form><h3>Shell Access (linuxalien)</h3>

This member's account is not locked. The member has enabled their account.
<form method="post" action="/members/committee/membership-edit?id=162" enctype="application/x-www-form-urlencoded">
<input name="lock_form" value="1" type="hidden"><input name="id" value="162" type="hidden"><input name="go_go_button" value="Lock Account" type="submit"></form><h3>Reset Password</h3><form method="post" action="/members/committee/membership-edit?id=162" enctype="application/x-www-form-urlencoded">
<input name="password_form" value="1" type="hidden"><input name="id" value="162" type="hidden"><table><tbody>
<tr><th>New Password</th>
<td><input name="new_password" size="20" type="password"> </td>
</tr>

<tr><th>Verify Password</th>
<td><input name="verify_password" size="20" type="password"> </td>
</tr>

<tr>
<td></td>
<td><label><input name="force_pw_change" value="1" type="checkbox">Force change on next login</label></td>
</tr>

<tr>
<td></td>
<td><input name="go_go_button" value="Reset Password" type="submit"><br>
</td>
</tr></tbody></table>
<div><input name=".cgifields" value="force_pw_change" type="hidden"></div></form><h3>Delete Member</h3>
<form method="post" action="/members/committee/membership-edit?id=162" enctype="application/x-www-form-urlencoded">

<input name="delete_form" value="1" type="hidden"><input name="id" value="162" type="hidden">If you are sure you want to delete this member, enter the text "<tt>Yes I am sure.</tt>" into the box below, and press Delete.<br>
Very rarely should actual members be deleted. Instead accounts should be locked, or accounts and memberships will lapse and do so automatically.<br>
<input name="delete_verification" size="30" type="text"> <input name="go_go_button" value="Delete Member" type="submit"><br>
</form></td>
