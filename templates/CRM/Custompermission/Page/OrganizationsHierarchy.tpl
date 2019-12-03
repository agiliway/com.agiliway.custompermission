<div class="view-content">
  <div class="crm-form-block crm-organization-hierarchy-block">
    <div class="hierarchy-wrapper">
      {assign var=count value=0}
      {foreach from=$organizationsHierarchy item=organizationsLevel}
        {counter start=0 skip=1 print=false}
        <div class="level_{$count++}" data-row="organization_level">
          <div class="row-level">
            <div class="col-1">
              <div class="icons-block">
                {if $organizationsLevel.children.organizations}
                  <div title="{ts}Open/Close{/ts}" data-button="view_children" class="arrow-icon active"></div>
                {/if}
                {if $organizationsLevel.children.contacts}
                  <div title="{ts}Show participants{/ts}" data-button="view_children_contacts" class="eye-icon"></div>
                {/if}
              </div>
            </div>
            <div class="col-11">
              <div class="organization-links">
                <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$organizationsLevel.id`"}" target="_blank">{$organizationsLevel.display_name}</a>
              </div>
            </div>
          </div>

          <div class="row-level hiddenElement" data-row="organization_children_contacts">
            {foreach from=$organizationsLevel.children.contacts item=contact}
              <div class="col-11">
                <div class="contacts-links">
                  <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$contact.id`"}" target="_blank">{$contact.display_name}</a>
                </div>
              </div>
            {/foreach}
          </div>

          {if $organizationsLevel.children.organizations}
            <div class="row-level level_{$count++}" data-row="organization_children">
              <div class="col-11">
                {include file='CRM/Custompermission/Page/_OrganizationsLevel.tpl' organizations=$organizationsLevel.children.organizations}
              </div>
            </div>
          {/if}
        </div>
      {/foreach}
    </div>
  </div>
</div>

{literal}
<script type="text/javascript">
  CRM.$(function ($) {
    $(document).on('click', '[data-button="view_children"]', function () {
      loadChildrenContacts(function ($button) {
        $button.toggleClass('active');

        var $organizationLevel = $button.closest('[data-row="organization_level"]');
        var $organizationChildren = $organizationLevel.find('[data-row="organization_children"]').first();

        $organizationChildren.toggle(300, 'swing');
      }, $(this));
    });

    $(document).on('click', '[data-button="view_children_contacts"]', function () {
      loadChildrenContacts(function ($button) {
        $button.toggleClass('active');

        var $organizationLevelContact = $button.closest('[data-row="organization_level"]');
        var $organizationChildrenContact = $organizationLevelContact.find('[data-row="organization_children_contacts"]').first();

        $organizationChildrenContact.toggle(300, 'swing');
      }, $(this));
    });

    function loadChildrenContacts(callback, $button) {
      var $organizationLevel = $button.closest('[data-row="organization_level"]');

      var contactId = $organizationLevel.attr('data-contact-id');
      var $organizationChildren = $organizationLevel.children('[data-row="organization_children"]')[0];

      if (contactId && !$organizationChildren) {
        $.ajax({
          url: CRM.url('civicrm/custompermission/ajax/child-contacts'),
          method: 'GET',
          data: {cid: contactId},
          dataType: 'json',
          success: function (data) {
            var childOrganizations = [];
            var childContacts = [];

            for (var i = 0; i < data.organizations.length; i++) {
              var icons = [];

              if (data.organizations[i].amount_child_organizations > 0) {
                icons.push($('<div/>', {
                  'title': '{/literal}{ts}Open/Close{/ts}{literal}',
                  'data-button': 'view_children',
                  'class': 'arrow-icon'
                }));
              }

              if (data.organizations[i].amount_child_contacts > 0) {
                icons.push($('<div/>', {
                  'title': '{/literal}{ts}Show participants{/ts}{literal}',
                  'data-button': 'view_children_contacts',
                  'class': 'eye-icon'
                }));
              }

              childOrganizations.push($('<div/>', {
                'data-row': 'organization_level',
                'data-contact-id': data.organizations[i].id,
                'html': [
                  $('<div/>', {
                    'class': 'row-level',
                    'html': [
                      $('<div/>', {
                        'class': 'col-1',
                        'html': [
                          $('<div/>', {
                            'class': 'icons-block',
                            'html': icons
                          })
                        ]
                      }),
                      $('<div/>', {
                        'class': 'col-11',
                        'html': [
                          $('<div/>', {
                            'class': 'organization-links',
                            'html': [
                              $('<a/>', {
                                'href': CRM.url('civicrm/contact/view', {reset: 1, cid: data.organizations[i].id}),
                                'target': '_blank',
                                'text': data.organizations[i].display_name
                              })
                            ]
                          })
                        ]
                      })
                    ]
                  })
                ]
              }));
            }

            for (var i = 0; i < data.contacts.length; i++) {
              childContacts.push($('<div/>', {
                'class': 'col-11',
                'html': [
                  $('<div/>', {
                    'class': 'contacts-links',
                    'html': [
                      $('<a/>', {
                        'href': CRM.url('civicrm/contact/view', {reset: 1, cid: data.contacts[i].id}),
                        'target': '_blank',
                        'text': data.contacts[i].display_name
                      })
                    ]
                  })
                ]
              }));
            }

            var $organizationChildrenRow = $('<div/>', {
              'class': 'row-level hiddenElement',
              'data-row': 'organization_children',
              'html': [
                $('<div/>', {
                  'class': 'col-1'
                }),
                $('<div/>', {
                  'class': 'col-11',
                  'html': childOrganizations
                })
              ]
            });
            var $contactsChilrenRow = $('<div/>', {
              'class': 'row-level hiddenElement',
              'data-row': 'organization_children_contacts',
              'html': childContacts
            });

            $organizationLevel.append($contactsChilrenRow);
            $organizationLevel.append($organizationChildrenRow);

            callback($button);
          }
        });
      }
      else {
        callback($button);
      }
    }
  });
</script>
{/literal}
