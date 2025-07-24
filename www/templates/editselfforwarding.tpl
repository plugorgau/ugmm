{extends file="base.tpl"}
{block name=body}

  <h2>Change e-mail forwarding for {$member.uid}@members.plug.org.au</h2>

  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded">
    <input name="nonce" value="{'editselfforwarding'|nonce}" type="hidden">      
    <input type="hidden" name="edit_selfforwarding" value="1">

    <table border="0">
      <tr>
        <th>Redirect email to</th>

        <td><input type="text" name="email_forward" value=
        "{if isset($member.mailForward) and $member.mailForward}{$member.mailForward}{/if}" size="30"></td>
      </tr>
    </table>If you would not like your PLUG email to be redirected,
    the above field should be blank.<br>
    <input type="submit" name="go_go_button" value="Change">
    <input type="submit" name="oops_button" value="Cancel">
  </form>

{/block}
