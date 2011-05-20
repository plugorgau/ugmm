<h2>Welcome, {$memberself.displayName}</h2>

<table style="margin-left: 0%;" width="100%" border="0"><tbody><tr>
<td style="text-align: center; background: none repeat scroll 0% 0% rgb(17, 51, 68); color: white;" width="20%"><b>Home</b></td>
<td style="text-align: center; background: none repeat scroll 0% 0% rgb(204, 204, 204); color: black;" width="20%"><a href="https://secure.plug.org.au/members/library-index">Library</a></td>
<td style="text-align: center; background: none repeat scroll 0% 0% rgb(204, 204, 204); color: black;" width="20%"><a href="https://secure.plug.org.au/members/admin/">Admin</a></td>
<td style="text-align: center; background: none repeat scroll 0% 0% rgb(204, 204, 204); color: black;" width="20%"><a href="https://secure.plug.org.au/members/committee/">Committee</a></td>
<td style="text-align: center; background: none repeat scroll 0% 0% rgb(204, 204, 204); color: black;" width="20%"><a href="https://secure.plug.org.au/members/logout">Log out</a></td>
</tr></tbody></table>

<p>Here you may check and update your current details in the PLUG membership
database, enable or disable your shell account, configure email forwarding, and more.</p>

Your membership will expire on {$memberself.formattedexpiry}.
<p>You are also a member of the following groups:
<ul>
{foreach from=$memberself.groups item=group}
    <li><a href="grouplink">{$group}</a></li>
{/foreach}
<li>Administrators: <a href="admin/">Admin Area</a> [<a href="admin/aliases">Mail Aliases</a>] [<a href="admin/groups">Unix Groups</a>] 

<li>Committee: <a href="committee/">Committee Area</a> [<a href="committee/membership-list">View Members</a>] [<a href="committee/membership-edit">Add Member</a>]
<li>Webmasters: <a href="webslave/">Webmasters Area</a>
</ul>
</p>
<h3><a name="personal"></a>Personal Details</h3>

<p>If the details below are out of date or incorrect, please use the link below
to edit them.

</p>

<table border="0">
<tr><th>E-mail Address</th><td>{foreach from=$memberself.mail item=mail}{$mail}<br/>{/foreach}</td></tr>
<tr><th>Postal Address</th><td>{$memberself.street}</td></tr>
<tr><th>Home Phone</th><td>{$memberself.homePhone}</td></tr>
<tr><th>Work Phone</th><td>{$memberself.pager}</td></tr>
<tr><th>Mobile Phone</th><td>{$memberself.mobile}</td></tr>

</table>

<p>
<ul><li><a href="member-edit">Edit your personal details</a>
<li>Requests to change your name should be sent to <a href="mailto:&#99;&#111;&#109;&#109;&#105;&#116;&#116;&#101;&#101;&#64;&#112;&#108;&#117;&#103;&#46;&#111;&#114;&#103;&#46;&#97;&#117;">&#99;&#111;&#109;&#109;&#105;&#116;&#116;&#101;&#101;&#64;&#112;&#108;&#117;&#103;&#46;&#111;&#114;&#103;&#46;&#97;&#117;</a>.</ul></p>
<h3><a name="email"></a>E-mail Forwarding</h3>
<p>Mail sent to your PLUG email address (linuxalien@plug.org.au) is being redirected to weirdit@gmail.com.</p>
<p>
<ul><li><a href="member-email">Change your e-mail forwarding</a></ul></p>
<h3><a name="shell"></a>Shell Account Details</h3>

<table border="0">
<tr><th>Username</th><td>{$memberself.uid}</td></tr>
<tr><th>Unix User ID</th><td>{$memberself.uidNumber}</td></tr>
<tr><th>Shell</th><td>{$memberself.loginShell}</td></tr>
<tr><th>Account expires</th><td>{$memberself.formattedexpiry}</td></tr>
</table>
<p>If you do not require your PLUG account (including POP3/IMAP access), you
may wish to disable it using the link below. You might want to ensure your
<a href="#email">mail forwarding</a> is set up before doing so, to avoid
missing any emails.

</p>
<p>
<ul><li><a href="member-shell">Change your shell account settings<a></ul></p>
<h3><a name="password"></a>Your password</h3>
Your PLUG password is used to access the members area of the website and login
to PLUG machines. It is not associated with your PLUG mailing list
subscriptions in any way.
<p>
<ul><li><a href="member-password">Change your PLUG password</a></ul></p>
</td>
</tr>
</table>
