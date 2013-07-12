<div id="div-{$divId}" class="control-group">
    <label id="lbl-{$divId}" class="control-label" for="{$nameYear}">{$title}</label>
    <div class="controls">
        <input type="text" id="{$nameYear}" name="{$nameYear}" size="3" value="{$valYear}" class="span1" />
        <select id="{$nameMonth}" name="{$nameMonth}" class="span1">
            {foreach from=$months key=id item=month}
            <option {if $id == $valMonth}selected="selected"{/if} value="{$id}">{$month}</option>
            {/foreach}
        </select>
        <select id="{$nameDay}" name="{$nameDay}" class="span1">
            <option value=""></option>
            {foreach from=$days item=day}
            <option {if $day eq $valDay}selected="selected"{/if} value="{$day}">{$day}</option>
            {/foreach}
        </select>
        <button onclick="$('#div-{$divId}').remove(); return false;" class="btn"><i class="icon-remove"></i></button>
    </div>
</div>