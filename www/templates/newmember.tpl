{extends file="base.tpl"}
{block name=body}

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

          <td><input name="first_name" value="{if isset($member.givenName)}{$member.givenName}{/if}"
          size="30" type="text"></td>
        </tr>

        <tr>
          <th>Last Name</th>

          <td><input name="last_name" value="{if isset($member.sn)}{$member.sn}{/if}" size=
          "30" type="text"></td>
        </tr>

        <tr>
          <th>E-mail Address *</th>

          <td><input name="email_address" value="{if isset($member.mail)}{$member.mail}{/if}" size="30" type="text"></td>
        </tr>

        <tr>
          <th>Postal Address *</th>

          <td><input name="street_address" value="{if isset($member.street)}{$member.street}{/if}"
          size="50" type="text"></td>
        </tr>

        <tr>
          <th>Home Phone</th>

          <td><input name="home_phone" size="20" type="text" value=
          "{if isset($member.homePhone)}{$member.homePhone}{/if}"></td>
        </tr>

        <tr>
          <th>Work Phone</th>

          <td><input name="work_phone" value="{if isset($member.pager)}{$member.pager}{/if}"
          size="20" type="text"></td>
        </tr>

        <tr>
          <th>Mobile Phone</th>

          <td><input name="mobile_phone" value="{if isset($member.mobile)}{$member.mobile}{/if}"
          size="20" type="text"></td>
        </tr>
        <tr>
          <th>Username *</th>

          <td><input name="uid" value="{if isset($member.uid)}{$member.uid}{/if}"
          size="30" type="text"></td>
        </tr>  

        <tr>
          <th>Password *</th>

          <td><input name="password" value="{if isset($member.password)}{$member.password}{/if}"
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
{if isset($member.description)}{$member.description}{/if}
</textarea></td>
        </tr>
      </tbody>
    </table><input name="go_go_button" value=
    "Add New Member" type="submit"> <input name=
    "reset_button" value="Cancel" type="reset">
  </form>

{/block}
