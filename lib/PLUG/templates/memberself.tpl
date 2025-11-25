{extends file="base.tpl"}
{block name=pagetitle} - Member Details{/block}
{block name=title}Your Membership Details{/block}
{block name=body}

<h2>Welcome, {$memberself->displayName}</h2>

<p>Here you may check and update your current details in the PLUG membership
database, enable or disable your shell account, configure email forwarding, and more.</p>

Your membership will expire on {$memberself->formattedexpiry}.
{*
<p>You are also a member of the following groups:
<ul>
{foreach from=$memberself->groups item=group}
    <li><a href="#grouplink">{$group}</a></li>
{/foreach}

*}
{*<li>Administrators: <a href="admin/">Admin Area</a> [<a href="admin/aliases">Mail Aliases</a>] [<a href="admin/groups">Unix Groups</a>]

<li>Committee: <a href="committee/">Committee Area</a> [<a href="committee/membership-list">View Members</a>] [<a href="committee/membership-edit">Add Member</a>]
<li>Webmasters: <a href="webslave/">Webmasters Area</a>*}
{*
</ul>
</p>
*}

<h3><a name="personal"></a>Personal Details</h3>

<p>If the details below are out of date or incorrect, please use the link below
to edit them.

</p>

<table border="0">
<tr><th>E-mail Address</th><td>{foreach from=$memberself->mail item=mail}{$mail}<br/>{/foreach}</td></tr>
<tr><th>Postal Address</th><td>{$memberself->street|default:'N/A'}</td></tr>
<tr><th>Home Phone</th><td>{$memberself->homePhone|default:'N/A'}</td></tr>
<tr><th>Work Phone</th><td>{$memberself->pager|default:'N/A'}</td></tr>
<tr><th>Mobile Phone</th><td>{$memberself->mobile|default:'N/A'}</td></tr>

</table>

<p>
<ul><li><a href="{$submenuitems.home.editselfdetails.link}">Edit your personal details</a>
<li>Requests to change your name should be sent to {mailto address=$emails.committee encode="hex"}.</ul></p>
<h3><a name="email"></a>E-mail Forwarding</h3>
{if $memberself->mailForward}
<p>Mail sent to your PLUG email address ({$memberself->uid}@members.plug.org.au) is being redirected to {$memberself->mailForward}.</p>
{else}
<p>Mail sent to your PLUG email address ({$memberself->uid}@members.plug.org.au) is currently being delivered to your home directory</p>
{/if}

<p>
<ul><li><a href="{$submenuitems.home.editselfforwarding.link}">Change your e-mail forwarding</a></ul></p>
<h3><a name="shell"></a>Shell Account Details</h3>
{if $memberself->shellEnabled && $memberself->membershipCurrent}
<p>Your shell account is enabled</p>
{elseif $memberself->shellEnabled && ! $memberself->membershipCurrent}
<p>Your shell account is enabled but your membership is not current. You will not be able to login to services until your membership is current</p>
{else}
<p>Your shell account is disabled. You will not be able to login to any services other than this members area.</p>
{/if}

<table border="0">
<tr><th>Username</th><td>{$memberself->uid}</td></tr>
<tr><th>Unix User ID</th><td>{$memberself->uidNumber}</td></tr>
<tr><th>Shell</th><td>{$memberself->loginShell}</td></tr>
<tr><th>Account expires</th><td>{$memberself->formattedexpiry}</td></tr>
</table>
<p>If you do not require your PLUG account (including POP3/IMAP access), you
may wish to disable it using the link below. You might want to ensure your
<a href="#email">mail forwarding</a> is set up before doing so, to avoid
missing any emails.

</p>
<p>
<ul><li><a href="{$submenuitems.home.editselfshell.link}">Change your shell account settings</a></ul></p>
<h3><a name="password"></a>Your password</h3>
Your PLUG password is used to access the members area of the website and login
to PLUG machines. It is not associated with your PLUG mailing list
subscriptions in any way.
<p>
<ul><li><a href="{$submenuitems.home.editselfpassword.link}">Change your PLUG password</a></ul></p>

{/block}
