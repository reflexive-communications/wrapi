<h1>WrAPI</h1>
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
</div>
<div class="action-link">
    <a class="button new-option" href="{$add_handler_url}">
        <span><i class="crm-i fa-plus-circle"></i> {ts}Add Handler{/ts}</span>
    </a>
</div>
