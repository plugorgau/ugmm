{extends file="base.tpl"}
{block name=pagetitle} - Resend Acknowledgement{/block}
{block name=title}Edit Member{/block}
{block name=body}

<h3>Resend Payment Acknowledgement</h3>

{if ! $success}
<form method="post" action="?member_id={$member.uidNumber}&amp;payment_id={$payment.id}" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="resend_ack_form" value="1"  />
    <input name="nonce" value="{'resendack'|nonce}" type="hidden">        
    <input type="hidden" name="member_id" value="{$member.uidNumber}"  />
    <input type="hidden" name="payment_id" value="{$payment.id}"  />
    <p>Are you sure you want to send an acknowledgement for this payment to {$member.displayName} ?</p>
    <p>Payment of {$payment.formattedamount} for {$payment.years} year{if $payment.years > 1}s{/if} as a {$payment.formattedtype} member. Paid {$payment.formatteddate}.</p>
    <input type="submit" name="go_go_button" value="Yes, I&#39;m Sure" />
    <input type="submit" name="oops_button" value="No, Don&#39;t!" />
</form>
{/if}
<a href="{$submenuitems.ctte.editmember.link}{$member.uidNumber}">Return to {$member.displayName} details</a>

{/block}
