{if $usercreated}

<p>New member created. <a href="{$submenuitems.ctte.editmember.link}{$newmember.uidNumber}">Edit member {$newmember.uidNumber} to make payment</a>
</p>

{/if}
  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded">
    <input name="newmember_form" value="1" type="hidden">
    <input name="nonce" value="{'newmember'|nonce}" type="hidden">    

    <table border="0">
      <tbody>
       

        <tr>
          <th>First Name *</th>

          <td><input name="first_name" value="{$member.givenName}"
          size="30" type="text"></td>
        </tr>

        <tr>
          <th>Last Name</th>

          <td><input name="last_name" value="{$member.sn}" size=
          "30" type="text"></td>
        </tr>

        <tr>
          <th>E-mail Address *</th>

          <td><input name="email_address" value="{$member.mail}" size="30" type="text"></td>
        </tr>

        <tr>
          <th>Postal Address *</th>

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
          <th>Username *</th>

          <td><input name="uid" value="{$member.uid}"
          size="30" type="text"></td>
        </tr>  

        <tr>
          <th>Password *</th>

          <td><input name="password" value="{$member.password}"
           type="password"></td>
        </tr>  
        
        <tr>
          <th>Verify Password *</th>

          <td><input name="verifypassword" value=""
           type="password"></td>
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
    "Add New Member" type="submit"> <input name=
    "reset_button" value="Cancel" type="reset">
  </form>

