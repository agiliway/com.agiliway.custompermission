<table>
  <tr class="crm-{$entityInClassFormat}-form-block-is_permission_a_b" data-tr="is_permission_a_b" style="display: none">
    <td class="label">
      {$form.is_permission_a_b.label}
    </td>
    <td>
      {$form.is_permission_a_b.html}
    </td>
  </tr>
  <tr class="crm-{$entityInClassFormat}-form-block-is_permission_b_a" data-tr="is_permission_b_a" style="display: none">
    <td class="label">
      {$form.is_permission_b_a.label}
    </td>
    <td>
      {$form.is_permission_b_a.html}
    </td>
  </tr>
</table>

{literal}
<script type="text/javascript">
  (function() {
    CRM.$(document).ready(function () {
      var $isPermissionABTr = CRM.$('[data-tr="is_permission_a_b"]');
      var $isPermissionBATr = CRM.$('[data-tr="is_permission_b_a"]');
      var $contactTypesB = CRM.$('.crm-relationshiptype-form-block-contact_types_b');

      $contactTypesB.after($isPermissionBATr);
      $contactTypesB.after($isPermissionABTr);

      $isPermissionABTr.show();
      $isPermissionBATr.show();
    });
  })();
</script>
{/literal}
