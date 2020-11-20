<div id="wrapi-main-wrapper">
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
                <th id="sortable">{ts}Action{/ts}</th>
                <th id="sortable">{ts}Handler{/ts}</th>
                <th id="sortable">{ts}Enabled{/ts}</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$routes key=id item=route_data}
                <tr id="wrapi-route-{$row.id}" data-action="setvalue"
                    class="crm-entity {cycle values="odd-row,even-row"}">
                    <td>{$id}</td>
                    <td>{$route_data.name}</td>
                    <td>{$route_data.action}</td>
                    <td>{$route_data.handler}</td>
                    <td>{if $route_data.enabled}Yes{else}No{/if}</td>
                    <td>{$route_data.actions}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>
{literal}
    <script>
        // Form in pop-up dialog
        CRM.$(function ($) {
            'use strict';
            $(".wrapi-action").on('crmPopupFormSuccess', CRM.refreshParent);
        });

        // Send action in AJAX
        CRM.$(function ($) {
            'use strict';
            $(".wrapi-ajax-action").click(function (event) {

                // Button clicked
                let button = this;

                event.preventDefault();

                // Send AJAX request, expect JSON return
                $.getJSON(button.href, {}, function () {
                    CRM.refreshParent(button);
                });
            });
        });
    </script>
{/literal}
