{extends file="base.tpl"}
{block name=body}

<p>
Thank you {$newmember.displayName}.<br/>
<br/>
You can now login to the <a href="/ugmm/">members area</a>. However, your account will not be activated until you have paid your membership.</br>
<br/>
You may choose not to pay membership, in which case your PLUG membership and
shell account will not be actived. However, the mailing list is still freely
accessible to non-members.
</p>
<p>
Membership costs are {$FULL_AMOUNT} p.a., or {$CONCESSION_AMOUNT} p.a. for students / concession.
<p>

<p>
There are a number of ways to pay.
<ul>
    <li>Head down to the next PLUG workshop or seminar to pay your dues to
     a committee member (e-mail {mailto address=$emails.committee encode="javascript_charcode"} beforehand to make
     sure there will be somebody there to renew your membership).</li>

    <li>Direct deposit your dues into PLUG's bank account (see
     <a href="/membership">https://www.plug.org.au/membership</a> for details), and email
     {mailto address=$emails.committee encode="javascript_charcode"} to let them know you have deposited it.
     Credit card facilities are available if no other method is
     available to you, just contact the committee to organise.</li>

    <li>Send a money-order (not cash) to PLUG's snail-mail address,
     available at <a href="/contact">https://www.plug.org.au/contact</a> and email
     {mailto address=$emails.committee encode="javascript_charcode"} to let them know you have sent it.
     </li>
</ul>

</p>

{/block}
