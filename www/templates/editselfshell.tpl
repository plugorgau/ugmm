
  <h2>Shell Account Settings for {$member.uid}</h2>

  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded">
    <input name="nonce" value="{'editselfshell'|nonce}" type="hidden">      
    <input type="hidden" name="edit_selfshell" value="1">

    {if $member.shellEnabled}
    <p>Your shell account is enabled</p>
    <p><input type="submit" name="disable_shell" value=
    "Disable Shell Account"></p>
    
    {else}
    <p>Your shell account is disabled</p>
    <p><input type="submit" name="enable_shell" value=
    "Enable Shell Account"></p>
    
    {/if}    


    <h3>Shell Account Details</h3>

    <table border="0">
      <tr>
        <th>Username</th>

        <td>{$member.uid}</td>
      </tr>

      <tr>
        <th>Unix User ID</th>

        <td>{$member.uidNumber}</td>
      </tr>

      <tr>
        <th>Account expires</th>

        <td>{$member.formattedexpiry}</td>
      </tr>

      <tr>
        <th>Shell</th>

        <td><select name="account_shell">
        {foreach from=$shells item=shell key=name}
          <option {if $shell==$member.loginShell}selected="selected"{/if} value="{$name}" >{$name}</option>
        {/foreach}
        </select></td>
      </tr>
    </table><input type="submit" name="go_go_button" value=
    "Change Shell"> <input type="submit" name="oops_button" value=
    "Cancel">
    
    <!-- byobu-enable-->

    <p><b>Note:</b> It may take up to 10 minutes for these changes
    to take effect.</p>
  </form>

