<div class="help">
    {if $edit_mode}
        {ts}Edit WrAPI route{/ts}
    {else}
        {ts}Add new WrAPI route{/ts}
    {/if}
</div>
<div class="crm-block crm-form-block">
    <table class="form-layout">
        <tr>
            <td class="label">{$form.name.label}</td>
            <td class="content">{$form.name.html}<br/>
                <span class="description">{ts}Name of your route{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.selector.label}</td>
            <td class="content">{$form.selector.html}<br/>
                <span class="description">{ts}Selector - routing is based on this parameter{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.handler_class.label}</td>
            <td class="content">{$form.handler_class.html}<br/>
                <span class="description">{ts}Class name of the handler that will handle this request{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.log_level.label}</td>
            <td class="content">{$form.log_level.html}<br/>
                <span class="description">{ts}Set logging level for this route{/ts}</span>
            </td>
        </tr>
        <tr>
            <td class="label">{$form.permissions.label}</td>
            <td class="content">{$form.permissions.html}<br/>
                <span class="description">
                    Required permissions. You can put here many permissions and use AND, OR logic.
                    The format used at routing is valid here. See
                    <a href="https://docs.civicrm.org/dev/en/latest/security/permissions/#implementing-permissions-logic"> Developer Guide</a>.
                    It is possible to leave empty, if you want no permissions, but is not recommended, use "administer CiviCRM" at least.
                </span>
            </td>
        </tr>
    </table>
    <div class="crm-submit-buttons">
        {include file="CRM/common/formButtons.tpl" location="bottom"}
    </div>
</div>
