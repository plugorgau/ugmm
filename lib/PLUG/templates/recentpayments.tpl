{extends file="base.tpl"}
{block name=pagetitle} - Recent Payments{/block}
{block name=title}Recent Payments{/block}
{block name=body}

<table class="payments">
  <thead>
    <tr>
      <th>Date</th>
      <th>Member</th>
      <th>Amount</th>
      <th>Type</th>
      <th># Years</th>
      <th>Description</th>
    </tr>
  </thead>
  <tbody>
{foreach from=$payments item=payment}
    <tr>
      <td>{$payment->formatteddate}</td>
      <td>{$member = $payment->member}<a href="{$submenuitems.ctte.editmember.link}{$member->uidNumber}">{$member->displayName}</a></td>
      <td>{$payment->formattedamount}</td>
      <td>{$payment->formattedtype}</td>
      <td>{$payment->years}</td>
      <td>{$payment->description}</td>
    </tr>
{/foreach}
  </tbody>
</table>
{/block}