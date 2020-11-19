<div class="help">
    Here you can view and manage WrAPI routes
</div>
<div class="crm-block crm-form-block">
    <div class="crm-accordion-wrapper civirule-view-wrapper">
        <div class="crm-accordion-header crm-master-accordion-header">{ts}Settings{/ts}</div>
        <div class="crm-accordion-body">
            <table class="form-layout-compressed civirule-view-table">
                <tr>
                    <td class="label">{$form.enable_debug.label}</td>
                    <td class="content">{$form.enable_debug.html}</td>
                </tr>
            </table>
            <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl"}</div>
        </div>
    </div>
    <div class="action-link">
        <a class="button new-option" href="{$add_route_url}">
            <span><i class="crm-i fa-plus-circle"></i> {ts}Add Route{/ts}</span>
        </a>
    </div>
</div>
<h3>Routes</h3>
<div id="wrapi-route-wrapper" class="crm-search-results">
    {include file="CRM/common/jsortable.tpl"}
    <table id="wrapi-routes" class="row-highlight display">
        <thead>
        <tr>
            <th>{ts}ID{/ts}</th>
            <th>{ts}Name{/ts}</th>
            <th>{ts}Action{/ts}</th>
            <th>{ts}Handler{/ts}</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$routes item=row}
            <tr id="wrapi-route-{$row.id}" data-action="setvalue"
                class="crm-entity {cycle values="odd-row,even-row"}">
                <td>{$row.id}</td>
                <td>{$row.name}</td>
                <td>{$row.action}</td>
                <td>{$row.handler}</td>
                <td></td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>
