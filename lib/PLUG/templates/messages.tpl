{if $errors}
<div id="errormessages" class="messages error">
  <ul>{foreach from=$errors item=msg}<li>{$msg}</li>{/foreach}</ul>
</div>
{/if}

{if $success}
<div id="successmessages" class="messages notice">
  <ul>{foreach from=$success item=msg}<li>{$msg}</li>{/foreach}</ul>
</div>
{/if}

