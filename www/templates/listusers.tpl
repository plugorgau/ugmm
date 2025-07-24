{extends file="base.tpl"}
{block name=body}

{function member_table}
<table class="membertable">
  <thead>
    <tr>
      <th>ID</th>
      <th>Username</th>
      <th>Name</th>
      <th>Email</th>
      <th>System Groups</th>
      <th>Member Exp</th>
      <th>Shell</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
{foreach from=$users item=user}
    <tr{if isset($user.description)} title="{$user.description}"{/if}{if $user.groups} class="elevated_user"{/if}>
      <td>{$user.uidNumber}{if isset($user.description)}{if $user.description}<sup style="color: rgb(136, 136, 170);">N</sup>{/if}{/if}</td>
      <td>{$user.uid}</td>
      <td>{$user.displayName}</td>
      <td>{$user.mail}{if isset($user.mailForward) and $user.mailForward}<br/><strong>Fwd: {$user.mailForward}</strong>{/if}</td>
      <td>{foreach from=$user.groups item=group}{$group}<br/>{/foreach}</td>
      <td>{if $user.expiry_raw > 1}{$user.expiry}{/if}</td>
      <td>{if $user.shellEnabled}T{/if}</td>
      <td><a href="{$submenuitems.ctte.editmember.link}{$user.uidNumber}">Edit</a></td>
    </tr>
{/foreach}
  </tbody>
</table>
{/function}

<h3>New Members Awaiting Payment ({$pendingusers|@sizeof})</h3>
{member_table users=$pendingusers}


<h3>Current Members ({$currentusers|@sizeof})</h3>
{member_table users=$currentusers}

<h3>Overdue Members ({$overdueusers|@sizeof})</h3>
{member_table users=$overdueusers}

{if $expiredusers}

<h3>Expired Members ({$expiredusers|@sizeof})</h3>
{member_table users=$expiredusers}

{else}
<h3>Expired Members</h3>
<p>Expired members are currently hidden. <a href="{$submenuitems.ctte.expiredmembers.link}">Click here to view expired members</a></p>
{/if}
{/block}
