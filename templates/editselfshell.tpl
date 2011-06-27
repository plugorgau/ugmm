
  <h2>Shell Account Settings for linuxalien</h2>

  <form method="post" action="/members/member-shell" enctype=
  "application/x-www-form-urlencoded">
    <input name="nonce" value="{'editselfshell'|nonce}" type="hidden">      
    <input type="hidden" name="member_shell" value="1">

    <p>Your shell account is enabled.</p>

    <p><input type="submit" name="disable_shell" value=
    "Disable Shell Account"></p>

    <h3>Shell Account Details</h3>

    <table border="0">
      <tr>
        <th>Username</th>

        <td>linuxalien</td>
      </tr>

      <tr>
        <th>Unix User ID</th>

        <td>10062</td>
      </tr>

      <tr>
        <th>Account expires</th>

        <td>Tuesday, 11 February 2014</td>
      </tr>

      <tr>
        <th>Shell</th>

        <td><select name="account_shell">
          <option selected="selected" value="/bin/bash">
            bash
          </option>

          <option value="/usr/bin/zsh">
            zsh
          </option>
        </select></td>
      </tr>
    </table><input type="submit" name="go_go_button" value=
    "Change Shell"> <input type="submit" name="oops_button" value=
    "Cancel">
    byobu-enable

    <p><b>Note:</b> It may take up to 10 minutes for these changes
    to take effect.</p>
  </form>

