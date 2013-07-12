{assign var="pageTitle" value="plugins.generic.articlesExtras.displayName"}
{assign var="pageCrumbTitle" value="plugins.generic.articlesExtras.displayName"}
{include file="common/header.tpl"}

<br/>

<h3>{translate key="plugins.generic.articlesExtras.editContent"}</h3>
<ul class="plain">
	<li>&#187; <a href="{url page="ArticlesExtrasPlugin" op="listIssues" path="body"}">{translate key="plugins.generic.articlesExtras.body"}</a></li>
    <li>&#187; <a href="{url page="ArticlesExtrasPlugin" op="listIssues" path="images"}">{translate key="plugins.generic.articlesExtras.images"}</a></li>
	<li>&#187; <a href="{url page="ArticlesExtrasPlugin" op="listIssues" path="citations"}">{translate key="plugins.generic.articlesExtras.citations"}</a></li>
</ul>

{include file="common/footer.tpl"}
