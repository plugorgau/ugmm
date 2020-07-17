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

{include file="messages.tpl"}

<a href="/ugmm/">Back to Members Area Login</a> | <a href="/">Back to main PLUG website</a>
<p>
If you would like to become a financial PLUG member, please fill in the following details. Becoming a member gives you the benefits listed at <a href="/membership">https://plug.org.au/membership</a>. You <strong>DO NOT</strong> need to be a member to access our <a href="/resources/mailing-list">mailing list</a> or to attend our normal <a href="/events">events</a>.
</p>
<p>
Membership costs are {$FULL_AMOUNT} p.a., or {$CONCESSION_AMOUNT} p.a. for students / concession.
<p>

  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded" id="membersignup">
    <input name="membersignup_form" value="1" type="hidden">



    <fieldset>
        <legend>Your Details</legend>
        <ol>
            <li>
                <label for="givenName">First name</label>
                <input id="givenName" name="givenName" type="text" placeholder="First Name" required autofocus value="{if isset($newmember)}}{$newmember.givenName}{/if}"/>
            </li>
            <li>
                <label for="sn">Last name</label>
                <input id="sn" name="sn" type="text" placeholder="Surname" value="{if isset($newmember)}{$newmember.sn}{/if}"/>
            </li>
            
            <li>
                <label for="mail">Email</label>
                <input id="mail" name="mail" type="email" placeholder="name@example.com" required value="{if isset($newmember)}{$newmember.mail}{/if}"/>
            </li>            

            <li>
                <label for="street">Postal Address</label>
                <input id="street" name="street" type="text" required value="{if isset($newmember)}{$newmember.street}{/if}"/>
            </li> 
            
            <li>
                <label for="homePhone">Home Phone</label>
                <input id="homePhone" name="homePhone" type="tel" value="{if isset($newmember)}{$newmember.homePhone}{/if}"/>
            </li>                        
            <li>
                <label for="pager">Work Phone</label>
                <input id="pager" name="pager" type="tel" value="{if isset($newmember)}{$newmember.pager}{/if}"/>
            </li>                        
            <li>
                <label for="mobile">Mobile Phone</label>
                <input id="mobile" name="mobile" type="tel" value="{if isset($newmember)}{$newmember.mobile}{/if}"/>
            </li>                                    
        </ol>
    </fieldset>

    <fieldset>
        <legend>Account Details</legend>
        These account details are not related to your mailing list subscription.
        <ol>
            <li>
                <label for="uid">Username</label>
                <input id="uid" name="uid" type="text" required value="{if isset($newmember)}{$newmember.uid}{/if}" placeholder="Choose a username"/><div id="uidcheckLoading"></div>
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
                <textarea id="notes" name="notes" rows="4" placeholder="Any other details we may need to know?">{if isset($newmember)}{$newmembernotes}{/if}</textarea>
            </li>      
        </ol>
    </fieldset> 
    <fieldset>           
        <button name="go_go_button" value=
    "Signup" type="submit">Signup</button>
    </fieldset>
  </form>

