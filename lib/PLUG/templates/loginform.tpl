{extends file="base.tpl"}
{block name=pagetitle} - Login{/block}
{block name=title}PLUG Members Area{/block}
{block name=body}

<p>This area contains resources available to PLUG financial members.  If you
are not yet a financial member, you may be interested in
<a href="{$external_links.membership}">joining PLUG</a>. (<a href="signup">Signup Form</a>)

</p><h3>Log in</h3>
{if isset($error)}
<div class="ui-widget messagewidget" id="errormessages">
        <div class="ui-state-error ui-corner-all"  style="margin-top: 20px; padding: 0pt 0.7em;" >
                <p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: 0.3em;"></span></p>
                <p><strong>{$error}</strong></p>
        </div>
</div>
{/if}
<form action="" method="POST" class="grid">
  <input name="plug_members_auth" value="1" type="hidden">

  <label for="username">PLUG Username</label>
  <div class="field">
    <input size="15" name="username" type="text">
  </div>

  <label for="password">Password</label>
  <div class="field">
    <input size="15" name="password" type="password">
  </div>

  <div class="actions">
    <input name="submit" value="Log In" type="submit">
    <a href="resetpassword">Forgotten your password?</a>
  </div>
</form>

<p><b>Note:</b> You will need to enable cookies in your browser to log in.
<br>
If you are having problems, please contact {mailto address=$emails.admin encode=hex}.</p>

{/block}
