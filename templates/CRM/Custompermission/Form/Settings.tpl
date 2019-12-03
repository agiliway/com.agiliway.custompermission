<div class="crm-block crm-form-block crm-custompermission-settings-form-block">
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
  </div>

  <h3>{ts}Main{/ts}</h3>
  <table class="form-layout-compressed">
    <tbody>
      <tr>
        <td class="label">{$form.hierarchy_main_organization.label}</td>
        <td>{$form.hierarchy_main_organization.html}</td>
      </tr>
    </tbody>
  </table>

  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
