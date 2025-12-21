{extends file="base.tpl"}
{block name=pagetitle} - Add Member{/block}
{block name=title}Add New Member{/block}
{block name=body}

{if $usercreated}

<p>New member created. <a href="{$submenuitems.ctte.editmember.link}{$newmember->uidNumber}">Edit member {$newmember->uidNumber} to make payment</a>
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

          <td><input name="givenName" value="{$member.givenName|default}"
          size="30" type="text"></td>
        </tr>

        <tr>
          <th>Last Name</th>

          <td><input name="sn" value="{$member.sn|default}" size=
          "30" type="text"></td>
        </tr>

        <tr>
          <th>E-mail Address *</th>

          <td><input name="mail" value="{$member.mail|default}" size="30" type="text"></td>
        </tr>

        <tr>
          <th>Postal Address *</th>

          <td><input name="street" value="{$member.street|default}"
          size="50" type="text"></td>
        </tr>

        <tr>
          <th>Home Phone</th>

          <td><input name="homePhone" size="20" type="text" value=
          "{$member.homePhone|default}"></td>
        </tr>

        <tr>
          <th>Work Phone</th>

          <td><input name="pager" value="{$member.pager|default}"
          size="20" type="text"></td>
        </tr>

        <tr>
          <th>Mobile Phone</th>

          <td><input name="mobile" value="{$member.mobile|default}"
          size="20" type="text"></td>
        </tr>
        <tr>
          <th>Username *</th>

          <td><input name="uid" value="{$member.uid|default}"
          size="30" type="text"></td>
        </tr>

        <tr>
          <th>Password *</th>

          <td><input name="password" value="{$member.password|default}"
           type="password"></td>
        </tr>

        <tr>
          <th>Verify Password *</th>

          <td><input name="vpassword" value=""
           type="password"></td>
        </tr>

        <tr>
          <th valign="top">Notes</th>

          <td>
          <textarea name="notes" rows="3" cols="40">{$member.description|default}</textarea>
          </td>
        </tr>
      </tbody>
    </table><input name="go_go_button" value=
    "Add New Member" type="submit"> <input name=
    "reset_button" value="Cancel" type="reset">
  </form>

{/block}
