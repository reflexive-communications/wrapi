<div class="help">
    Add or edit WrAPI routes
</div>
<div class="crm-block crm-form-block">
    <table class="form-layout">
        <tr class="crm-path-form-block-uploadDir">
            <td class="label">{$form.name.label}</td>
            <td>{$form.name.html}<br/>
                <span class="description">{ts}Name of your route{/ts}</span>
            </td>
        </tr>
        <tr class="crm-path-form-block-uploadDir">
            <td class="label">{$form.action.label}</td>
            <td>{$form.action.html}<br/>
                <span class="description">{ts}Action - routing is based on this parameter{/ts}</span>
            </td>
        </tr>
        <tr class="crm-path-form-block-uploadDir">
            <td class="label">{$form.handler_class.label}</td>
            <td>{$form.handler_class.html}<br/>
                <span class="description">{ts}Class name of the handler that will handle this request{/ts}</span>
            </td>
        </tr>
    </table>
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
    <div class="action-link">
        <a class="button new-option" href="{$main_form_url}">
            <span><i class="crm-i fa-times"></i> {ts}Cancel{/ts}</span>
        </a>
    </div>
</div>
