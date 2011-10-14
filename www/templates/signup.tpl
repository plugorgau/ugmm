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

<h1>Membership Signup</h1>

<a href="/ugmm/">Back to Members Area Login</a> | <a href="http://plug.org.au">Back to main PLUG website</a>
<p>
If you would like to become a financial PLUG member, please fill in the following details. Becoming a member gives you the benefits listed at <a href="http://plug.org.au/membership">http://plug.org.au/membership</a>. You <strong>DO NOT</strong> need to be a member to access our <a href="http://www.plug.org.au/resources/mailing-list">mailing list</a> or to attend our normal <a href="http://www.plug.org.au/events">events</a>.
</p>

  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded" id="membersignup">
    <input name="membersignup_form" value="1" type="hidden">



    <fieldset>
        <legend>Your Details</legend>
        <ol>
            <li>
                <label for="givenName">First name</label>
                <input id="givenName" name="givenName" type="text" placeholder="First Name" required autofocus value="{$newmember.givenName}"/>
            </li>
            <li>
                <label for="sn">Last name</label>
                <input id="sn" name="sn" type="text" placeholder="Surname" value="{$newmember.sn}"/>
            </li>
            
            <li>
                <label for="mail">Email</label>
                <input id="mail" name="mail" type="email" placeholder="name@example.com" required value="{$newmember.mail}"/>
            </li>            

            <li>
                <label for="street">Postal Address</label>
                <input id="street" name="street" type="text" required value="{$newmember.street}"/>
            </li> 
            
            <li>
                <label for="homePhone">Home Phone</label>
                <input id="homePhone" name="homePhone" type="tel" value="{$newmember.homePhone}"/>
            </li>                        
            <li>
                <label for="pager">Work Phone</label>
                <input id="pager" name="pager" type="tel" value="{$newmember.pager}"/>
            </li>                        
            <li>
                <label for="mobile">Mobile Phone</label>
                <input id="mobile" name="mobile" type="tel" value="{$newmember.mobile}"/>
            </li>                                    
        </ol>
    </fieldset>

    <fieldset>
        <legend>Account Details</legend>
        These account details are not related to your mailing list subscription.
        <ol>
            <li>
                <label for="uid">Username</label>
                <input id="uid" name="uid" type="text" required value="{$newmember.uid}" placeholder="Choose a username"/><div id="uidcheckLoading"></div>
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
    "Signup" type="submit">Signup</button>
    </fieldset>
  </form>

