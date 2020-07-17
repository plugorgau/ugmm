<h1>Password Reset</h1>
{include file="messages.tpl"}

{if $successform}
Please continue to <a href="/ugmm">login page to login</a> with your new password
{* TODO: link to login page *}

{elseif $resetform}
<p>Set a new password for {$username}.</p>
<form method="post" action="" enctype="application/x-www-form-urlencoded">
    <input name="newpasswordreset_form" value="1" type="hidden">
    Password: <input type="password" name="newpassword" value=""/><br/>
    Password again: <input type="password" name="newpasswordconfirm" value=""/>    <br/>
    <input type="submit" value="Change Password"/>
</form>
{else}
<form method="post" action="" enctype="application/x-www-form-urlencoded">
    <input name="resetpassword_form" value="1" type="hidden">
    Email Address: <input name="email" value=""/>
    <input type="submit" value="Send Reset Email"/>
</form>
{/if}
