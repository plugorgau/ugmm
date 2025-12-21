{extends file="base.tpl"}
{block name=pagetitle} - Signup{/block}
{block name=title}Membership Signup{/block}
{block name=body}

{literal}
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', () => {
    let uid = document.getElementById('uid');
    let check = document.getElementById('uidcheck');
    let loading = document.getElementById('uidcheckLoading');
    let controller;

    uid.addEventListener('blur', () => {
        loading.classList.add('fade-in');
        if (controller)
            controller.abort();
        controller = new AbortController();
        (async (signal) => {
            try {
                response = await fetch('ajax.php', {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: new URLSearchParams({
                        ajax: 'checkusername', uid: uid.value}),
                    signal: signal,
                });
                if (response.ok) {
                    check.classList.add('fade-in');
                    check.innerHTML = await response.text();
                }
            } catch (err) {
                console.log(err);
            } finally {
                loading.classList.remove('fade-in');
                controller = null;
            }
        })(controller.signal);
    });
});
</script>
{/literal}

<a href="memberself">Back to Members Area Login</a> | <a href="{$external_links.home}">Back to main PLUG website</a>
<p>
If you would like to become a financial PLUG member, please fill in the following details. Becoming a member gives you the benefits listed at <a href="{$external_links.membership}">{$external_links.membership}</a>. You <strong>DO NOT</strong> need to be a member to access our <a href="{$external_links.lists}">mailing list</a> or to attend our normal <a href="{$external_links.events}">events</a>.
</p>
<p>
Membership costs are {$FULL_AMOUNT} p.a., or {$CONCESSION_AMOUNT} p.a. for students / concession.
<p>

  <form method="post" action="" enctype=
  "application/x-www-form-urlencoded" class="grid">
    <input name="membersignup_form" value="1" type="hidden">

    <p class="hints"><strong>Your Details</strong></p>

    <label for="givenName">First name</label>
    <div class="field">
      <input id="givenName" name="givenName" type="text" size="30" placeholder="First Name" required autofocus value="{$newmember.givenName|default}"/>
    </div>

    <label for="sn">Last name</label>
    <div class="field">
      <input id="sn" name="sn" type="text" size="30" placeholder="Surname" value="{$newmember.sn|default}"/>
    </div>

    <label for="mail">Email</label>
    <div class="field">
      <input id="mail" name="mail" type="email" size="30" placeholder="name@example.com" required value="{$newmember.mail|default}"/>
    </div>

    <label for="street">Postal Address</label>
    <div class="field">
      <input id="street" name="street" type="text" size="50" required value="{$newmember.street|default}"/>
    </div>

    <label for="homePhone">Home Phone</label>
    <div class="field">
      <input id="homePhone" name="homePhone" type="tel" size="20" value="{$newmember.homePhone|default}"/>
    </div>

    <label for="pager">Work Phone</label>
    <div class="field">
      <input id="pager" name="pager" type="tel" size="20" value="{$newmember.pager|default}"/>
    </div>

    <label for="mobile">Mobile Phone</label>
    <div class="field">
      <input id="mobile" name="mobile" type="tel" size="20" value="{$newmember.mobile|default}"/>
    </div>

    <p class="hints">
      <strong>Account Details</strong><br>
      These account details are not related to your mailing list subscription.
    </p>

    <label for="uid">Username</label>
    <div class="field">
      <input id="uid" name="uid" type="text" size="30" required value="{$newmember.uid|default}" placeholder="Choose a username"/><div id="uidcheckLoading"></div>
      <div id="uidcheck"></div>
      <div>Your username becomes the start of your @members.plug.org.au email address</div>
    </div>


    <label for="password">Password</label>
    <div class="field">
      <input id="password" name="password" type="password" placeholder="Choose a password"/>
      <div>If no password is chosen, your account will be locked until you set a password using the password reset facility</div>
    </div>


    <label for="vpassword">Verify Password</label>
    <div class="field">
      <input id="vpassword" name="vpassword" type="password" placeholder="Verify Password"/>
    </div>

    <p class="hints">
      <strong>Other Details</strong>
    </p>

    <label for="notes">Other notes?</label>
    <div class="field">
      <textarea id="notes" name="notes" rows="3" cols="40" placeholder="Any other details we may need to know?">{$newmembernotes|default}</textarea>
    </div>

    <div class="actions">
      <button name="go_go_button" value="Signup" type="submit">Signup</button>
    </div>
  </form>

{/block}
