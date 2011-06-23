<h3>New Members Awaiting Payment ({$pendingusers|@sizeof})</h3>
<table>
<tbody><tr bgcolor="#8888aa">
 <th>ID</th>
 <th>Username</th>
 <th>First Name</th>
 <th>Last Name</th>

 <th>Email</th>
 <th></th>
</tr>


{foreach from=$pendingusers item=user}
<tr title="{$user.description}" bgcolor="#ddddff">
        <td>{$user.uidNumber}{if $user.description}<sup style="color: rgb(136, 136, 170);">N</sup>{/if}</td>
        <td>{$user.uid}</td>        
        <td>{$user.givenName}</td>                
        <td>{$user.sn}</td>
        <td>{foreach from=$user.mail item=mail}{$mail}<br/>{/foreach}</td>
        <td><a href="editmember?id={$user.uidNumber}">Edit</a></td>                
</tr>        
{/foreach}

</table>


<h3>Current Members ({$currentusers|@sizeof})</h3>
<table>
<tbody><tr bgcolor="#8888aa">
 <th>ID</th>
 <th>Username</th>
 <th>First Name</th>
 <th>Last Name</th>
 <th>Email</th>
 <th>System Groups</th>
 <th>Member Exp</th>
 <th></th>
</tr>


{foreach from=$currentusers item=user}
<tr title="{$user.description}" {if $user.groups}style="elevated_user" bgcolor="#ffdddd"{else}bgcolor="#ddddff"{/if}">
        <td>{$user.uidNumber}{if $user.description}<sup style="color: rgb(136, 136, 170);">N</sup>{/if}</td>
        <td>{$user.uid}</td>        
        <td>{$user.givenName}</td>                
        <td>{$user.sn}</td>
        <td>{$user.mail}</td>
        <td>{foreach from=$user.groups item=group}{$group}<br/>{/foreach}</td>
        <td>{$user.expiry}</td>
        <td><a href="editmember?id={$user.uidNumber}">Edit</a></td>                
</tr>        
{/foreach}
</table>

{if $expiredusers}

<h3>Expired Members ({$expiredusers|@sizeof})</h3>
<table>
<tbody><tr bgcolor="#8888aa">
 <th>ID</th>
 <th>Username</th>
 <th>First Name</th>
 <th>Last Name</th>
 <th>Email</th>
 <th>Type</th>
 <th>System Groups</th> 
 <th>Member Exp</th>
 <th></th>
</tr>


{foreach from=$expiredusers item=user}
<tr title="{$user.description}" {if $user.groups}style="elevated_user" bgcolor="#ffdddd"{else}bgcolor="#ddddff"{/if}">
        <td>{$user.uidNumber}{if $user.description}<sup style="color: rgb(136, 136, 170);">N</sup>{/if}</td>
        <td>{$user.uid}</td>        
        <td>{$user.givenName}</td>                
        <td>{$user.sn}</td>
        <td>{foreach from=$user.mail item=mail}{$mail}<br/>{/foreach}</td>
        <td>{$user.type}</td>
        <td>{foreach from=$user.groups item=group}{$group}<br/>{/foreach}</td>
        <td>{$user.expiry}</td>
        <td><a href="editmember?id={$user.uidNumber}">Edit</a></td>                
</tr>        
{/foreach}
</table>

{else}
<h3>Expired Members</h3>
<p>Expired members are currently hidden. <a href="{$submenuitems.ctte.expiredmembers.link}">Click here to view expired members</a></p>
{/if}
