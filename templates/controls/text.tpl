<div id="div-{$part}" class="control-group">
    <label id="lbl-{$part}" class="control-label" for="{$part}">{$title}</label>
    <div class="controls">
        <input type="text" id="{$part}" name="{$part}" size="{$size}" value="{$value}" />
        <button onclick="$('#div-{$part}').remove(); return false;" class="btn"><i class="icon-remove"></i></button>
    </div>
</div>