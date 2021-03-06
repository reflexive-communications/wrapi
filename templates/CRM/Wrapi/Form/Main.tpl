<div id="wrapi-main-wrapper" class="crm-container">
    <div class="help">
        {ts}Here you can view and manage WrAPI routes{/ts}
    </div>
    <div class="crm-block crm-form-block">
        <div class="crm-accordion-wrapper collapsed">
            <div class="crm-accordion-header crm-master-accordion-header">{ts}Settings{/ts}</div>
            <div class="crm-accordion-body">
                <table class="form-layout-compressed">
                    <tr class="crm-path-form-block-uploadDir">
                        <td class="label">{$form.enable_debug.label}</td>
                        <td class="content">{$form.enable_debug.html}<br/>
                            <span class="description">{ts}Produce verbose error messages to the request. Don't enable in production!{/ts}</span>
                        </td>
                    </tr>
                </table>
                <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl"}</div>
            </div>
        </div>
        <div class="action-link">
            <a class="button new-option crm-popup wrapi-action" href="{$add_route_url}">
                <span><i class="crm-i fa-plus-circle"></i> {ts}Add Route{/ts}</span>
            </a>
        </div>
    </div>

    <h3>Routes</h3>
    <div class="crm-search-results">
        {include file="CRM/common/jsortable.tpl"}
        <table id="wrapi-routes" class="row-highlight display">
            <thead class="sticky">
            <tr>
                <th id="sortable">{ts}ID{/ts}</th>
                <th id="sortable">{ts}Name{/ts}</th>
                <th id="sortable">{ts}Selector{/ts}</th>
                <th id="sortable">{ts}Handler{/ts}</th>
                <th id="sortable">{ts}Log level{/ts}</th>
                <th id="sortable">{ts}Permissions{/ts}</th>
                <th id="sortable">{ts}Options{/ts}</th>
                <th id="sortable">{ts}Enabled{/ts}</th>
                <th>{ts}Actions{/ts}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$routes key=id item=route_data}
                <tr class="crm-entity {if !$route_data.enabled}disabled{/if} {cycle values="odd-row,even-row"}">
                    <td class="centered">{$id}</td>
                    <td class="centered">{$route_data.name}</td>
                    <td class="centered">{$route_data.selector}</td>
                    <td class="centered">{$route_data.handler}</td>
                    <td class="centered">
                        {if $route_data.log == 0}
                            {ts}No logging{/ts}
                        {elseif $route_data.log == 7}
                            {ts}Debug{/ts}
                        {elseif $route_data.log == 6}
                            {ts}Info{/ts}
                        {elseif $route_data.log == 3}
                            {ts}Error{/ts}
                        {else}
                            {$route_data.log}
                        {/if}
                    </td>
                    <td class="centered">
                        {foreach from=$route_data.perms item=permission}
                            <span class="crm-tag-item margin-small">{$permission}</span>
                        {/foreach}
                    <td>
                        {foreach from=$route_data.opts key=opt_key item=opt_value}
                            <div>{$opt_key}={$opt_value}</div>
                        {/foreach}
                    </td>
                    <td class="centered">{if $route_data.enabled}Yes{else}No{/if}</td>
                    <td class="centered">{$route_data.actions}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>
