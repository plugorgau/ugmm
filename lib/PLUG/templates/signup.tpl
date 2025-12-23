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
</p>

  <form method="post" action="" class="grid">
    <input type="hidden" name="membersignup_form" value="1">

    <div class="hints"><h2>Your Details</h2></div>

    <label for="givenName">First name <span class="required">*</span></label>
    <div class="field">
      <input type="text" name="givenName" value="{$newmember.givenName|default}" size="30" placeholder="First Name" required autofocus/>
    </div>

    <label for="sn">Last name</label>
    <div class="field">
      <input type="text" name="sn" value="{$newmember.sn|default}" size="30" placeholder="Surname"/>
    </div>

    <label for="mail">Email <span class="required">*</span></label>
    <div class="field">
      <input type="email" name="mail" value="{$newmember.mail|default}" size="30" placeholder="name@example.com" required/>
      <div>We are required to collect contact information for the
      membership register. Your email address will be used as a
      recovery option for your account, and to send out notice of
      general meetings.</div>
    </div>

    <label for="uid">Username <span class="required">*</span></label>
    <div class="field">
      <input type="text" name="uid" value="{$newmember.uid|default}" size="30" required placeholder="Choose a username"/>
      <div id="uidcheckLoading"></div>
      <div id="uidcheck"></div>
      <div>Your username is used to log into the membership management system</div>
    </div>

    <label for="password">Password <span class="required">*</span></label>
    <div class="field">
      <input type="password" name="password" placeholder="Choose a password"/>
      <div>If no password is chosen, your account will be locked until you set a password using the password reset facility</div>
    </div>

    <label for="vpassword">Verify Password <span class="required">*</span></label>
    <div class="field">
      <input type="password" name="vpassword" placeholder="Verify Password"/>
    </div>

    <div class="hints">
      <h2>Other Details</h2>
      The following details are optional. If provided, we may use them to
      contact you where email is not appropriate.
    </div>

    <label for="street">Postal Address</label>
    <div class="field">
      <input type="text" name="street" value="{$newmember.street|default}" size="50"/>
    </div>

    <label for="homePhone">Home Phone</label>
    <div class="field">
      <input type="tel" name="homePhone" value="{$newmember.homePhone|default}" size="20"/>
    </div>

    <label for="pager">Work Phone</label>
    <div class="field">
      <input type="tel" name="pager" value="{$newmember.pager|default}" size="20"/>
    </div>

    <label for="mobile">Mobile Phone</label>
    <div class="field">
      <input type="tel" name="mobile" value="{$newmember.mobile|default}" size="20"/>
    </div>

<label for="notes">Other notes?</label>
    <div class="field">
      <textarea name="notes" rows="3" cols="40" placeholder="Any other details we may need to know?">{$newmembernotes|default}</textarea>
    </div>

    <div class="actions">
      <button type="submit" name="go_go_button" value="Signup">Signup</button>
    </div>
  </form>

{/block}
