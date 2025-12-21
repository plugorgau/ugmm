{extends file="base.tpl"}
{block name=pagetitle} - Editing Member Email Forwarding{/block}
{block name=title}Edit Member Email Forwarding{/block}
{block name=body}

  <h2>Change e-mail forwarding for {$member->uid}@members.plug.org.au</h2>

  <form method="post" action="" enctype="application/x-www-form-urlencoded" class="grid">
    <input name="nonce" value="{'editselfforwarding'|nonce}" type="hidden">
    <input type="hidden" name="edit_selfforwarding" value="1">

    <label for="email_forward">Redirect email to</label>
    <div class="field">
      <input type="text" name="email_forward" value="{$member->mailForward|default}" size="30">
      <div>If you would not like your PLUG email to be redirected, the
      above field should be blank.</div>
    </div>


    <div class="actions">
      <input type="submit" name="go_go_button" value="Change">
      <input type="submit" name="oops_button" value="Cancel">
    </div>
  </form>

{/block}
