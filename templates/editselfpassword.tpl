  <h2>Change your PLUG password</h2>

  <p>Your PLUG password is used to access the members area of the
  website and login to PLUG machines. It is not associated with
  your PLUG mailing list subscriptions in any way. Changes to your
  PLUG password takes effect immediately.</p>
  
  Your password must:

  <ul>
    <li>be at least 8 characters in length</li>

    <li>not consist of solely lowercase letters or solely
    digits</li>
  </ul>Your password may:

  <ul>
    <li>contain any alphanumeric characters, punctuation,
    spaces</li>

    <li>be as long as you like (100 characters is not an
    unreasonable limit)</li>
  </ul>
  
  <p>Accounts detected with weak passwords will be disabled</p>

  <form method="post" action="/members/member-password" enctype=
  "application/x-www-form-urlencoded">
    <input name="nonce" value="{'editselfpassword'|nonce}" type="hidden">      
    <input type="hidden" name="member_password" value="1">

    <table border="0">
      <tr>
        <th>Current Password</th>

        <td><input type="password" name="current_password" size=
        "30"></td>
      </tr>

      <tr>
        <th>New Password</th>

        <td><input type="password" name="new_password" size=
        "30"></td>
      </tr>

      <tr>
        <th>Verify Password</th>

        <td><input type="password" name="verify_password" size=
        "30"></td>
      </tr>
    </table><input type="submit" name="go_go_button" value=
    "Change Password"> <input type="submit" name="oops_button"
    value="Cancel">
  </form>

