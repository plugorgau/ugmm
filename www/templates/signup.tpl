{literal}
<script type="text/javascript">
$(document).ready(function () {
    $('#uid').blur(function(){
        $("#uidcheckLoading").show();
        $.post("ajax.php", {
            uid: $('#uid').val(),
            ajax: 'checkusername'},
            function(response){
                $('#uidcheckLoading').fadeOut();
                $('#uidcheck').html(unescape(response)).show();
            }
        );
        return false;
    });
});
</script>
{/literal}
  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded" id="membersignup">
    <input name="membersignup_form" value="1" type="hidden">


    <fieldset>
        <legend>Your Details</legend>
        <ol>
            <li>
                <label for="firstname">First name</label>
                <input id="firstname" name="firstname" type="text" placeholder="First Name" required autofocus value="{$newmember.givenName}"/>
            </li>
            <li>
                <label for="sname">Last name</label>
                <input id="sname" name="sname" type="text" placeholder="Surname" value="{$newmember.sn}"/>
            </li>
            
            <li>
                <label for="email">Email</label>
                <input id="email" name="email" type="email" placeholder="name@example.com" required value="{$newmember.mail}"/>
            </li>            

            <li>
                <label for="streetaddress">Postal Address</label>
                <input id="address" name="address" type="text" required value="{$newmember.street}"/>
            </li> 
            
            <li>
                <label for="hph">Home Phone</label>
                <input id="hph" name="hph" type="tel" value="{$newmember.homePhone}"/>
            </li>                        
            <li>
                <label for="wph">Work Phone</label>
                <input id="wph" name="wph" type="tel" value="{$newmember.pager}"/>
            </li>                        
            <li>
                <label for="mph">Mobile Phone</label>
                <input id="mph" name="mph" type="tel" value="{$newmember.mobile}"/>
            </li>                                    
        </ol>
    </fieldset>

    <fieldset>
        <legend>Account Details</legend>
        <ol>
            <li>
                <label for="uid">Username</label>
                <input id="uid" name="uid" type="text" required value="{$newmember.uid}" /><div id="uidcheckLoading"></div>
                <div id="uidcheck"></div>                
                <p>Your username becomes the start of your @members.plug.org.au email address</p>
            </li>         
            <li>
                
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="Choose a password"/><p>If no password is choosen, your account will be locked until you set a password using the password reset facility</p>
            </li>                
            <li>
                <label for="vpassword">Verify Password</label>
                <input id="vpassword" name="vpassword" type="password" placeholder="Verify Password"/>
            </li>                
        </ol>
    </fieldset>

    <fieldset>
        <legend>Other Details</legend>
        <ol>        
            <li>
                <label for="notes">Other notes?</label>
                <textarea id="notes" name="notes" rows="4" placeholder="Any other details we may need to know?">{$newmembernotes}</textarea>
            </li>      
        </ol>
    </fieldset> 
    <fieldset>           
        <button name="go_go_button" value=
    "Signup" type="submit">Signup</button><button name=
    "reset_button" value="Cancel" type="reset">Cancel</button>
    </fieldset>
  </form>

