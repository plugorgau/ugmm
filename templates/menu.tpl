<div id="toplevelmenu" class="menu">
    <ul>
        {foreach from=$topmenu item=menu}
        <li><a href="{$menu.link}">{$menu.label}</a></li>
        {/foreach}
    </ul>
</div>
<div id="secondlevelmenu" class="menu">
    <ul>
        {foreach from=$submenu item=menu}
        {if $menu.label}{* Don't display menu items without labels, so they can be used by other templates without being show *}
            <li><a href="{$menu.link}">{$menu.label}</a></li>
        {/if}
        {/foreach}
    </ul>
</div>    
