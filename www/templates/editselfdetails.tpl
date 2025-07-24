{extends file="base.tpl"}
{block name=pagetitle} - Editing Member Details{/block}
{block name=title}Edit Member Details{/block}
{block name=body}

  <h2>Editing member details for {$member.displayName}</h2>

  <h3>Personal Details</h3>

  <ul>
    <li>Required fields are marked with a <sup>*</sup>.</li>
  </ul>

  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded">
    <input name="nonce" value="{'editselfdetails'|nonce}" type="hidden">      
    <input type="hidden" name="edit_selfmember" value="1"><input type=
    "hidden" name="forced" value="0">

    <table border="0">
      <tr>
        <th>E-mail Address<sup>*</sup></th>

        <td><input type="text" name="email_address" value=
        "{$member.mail}" size="30"></td>
      </tr>

      <tr>
        <th>Postal Address<sup>*</sup></th>

        <td><input type="text" name="street_address" value=
        "{$member.street}" size="50"></td>
      </tr>

      <tr>
        <th>Home Phone</th>

        <td><input type="text" name="home_phone" size="20" value="{if isset($member.homePhone)}{$member.homePhone}{/if}"></td>
      </tr>

      <tr>
        <th>Work Phone</th>

        <td><input type="text" name="work_phone" value=
        "{if isset($member.pager) and $member.pager}{$member.pager}{/if}" size="20"></td>
      </tr>

      <tr>
        <th>Mobile Phone</th>

        <td><input type="text" name="mobile_phone" value=
        "{if isset($member.mobile)}{$member.mobile}{/if}" size="20"></td>
      </tr>
    </table><input type="submit" name="go_go_button" value=
    "Update"> <input type="submit" name="oops_button" value=
    "Cancel">
  </form>

{/block}
