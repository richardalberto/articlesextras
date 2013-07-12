<table id="citations" class="table table-striped">
    <thead>
	<tr>
	  <th>Nro.</th>
	  <th>Cita</th>
          <th style="width: 40px;">&nbsp;</th>
	</tr>
    </thead>
    <tbody>
	{assign var="nro" value=0}
	{foreach from=$citations item=citation}
	{assign var="nro" value=$nro+1}
	<tr id="{$nro}">
	  <td>{$nro}</td>
	  <td>{$citation}</th>
          <td><a class="btnEditCitation" id="{$nro}" href="#"><i class="icon-edit"></i></a>&nbsp;<a class="btnDeleteCitation" id="{$nro}" href="#"><i class="icon-remove"></i></a></td>
	</tr>	
	{/foreach}
	{if $nro eq 0}
	<tr>
		<td colspan="3"><i>{translate key="plugins.generic.articlesExtras.form.nocurrentcitations"}</i></td>
	</tr>
	{/if}
    </tbody>
</table>
