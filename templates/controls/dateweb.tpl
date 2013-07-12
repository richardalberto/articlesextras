<div id="div-{$divId}" class="control-group">
    <label id="lbl-{$divId}" class="control-label" for="{$nameYear}">{$title}</label>
    <div class="controls">
        <small>Desde</small>
        <input type="text" id="siteDateFrom" name="siteDateFrom" size="3" class="span1" />
        <small>Hasta</small>
        <input type="text" id="siteDateTo" name="siteDateTo" size="3" class="span1" />
        <button onclick="$('#div-{$divId}').remove(); return false;" class="btn"><i class="icon-remove"></i></button>
    </div>
</div>