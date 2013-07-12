<div id="div-{$divId}" class="control-group">
    <label id="lbl-{$divId}" class="control-label" for="{$part}page_initial">{$title}</label>
    <div class="controls">
        <small>Inicial</small>
        <input type="text" id="{$part}page_initial" name="{$part}page_initial" size="3" value="{$initial}" class="span1" />
        <small>Final</small>
        <input type="text" id="{$part}page_final" name="{$part}page_final" size="3" value="{$final}" class="span1" />
        <button onclick="$('#div-{$divId}').remove(); return false;" class="btn"><i class="icon-remove"></i></button>
    </div>
</div>