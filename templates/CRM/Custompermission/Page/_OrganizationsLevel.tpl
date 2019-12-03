{foreach from=$organizations item=organization}
    <div class="" data-row="organization_level" data-contact-id="{$organization.id}">
        <div class="row-level">
            <div class="col-1">
                <div class="icons-block">
                    {if $organization.amount_child_organizations}
                        <div title="{ts}Open/Close{/ts}" data-button="view_children" class="arrow-icon"></div>
                    {/if}
                    {if $organization.amount_child_contacts}
                        <div title="{ts}Show participants{/ts}" data-button="view_children_contacts"
                             class="eye-icon"></div>
                    {/if}
                </div>
            </div>
            <div class="col-11">
                <div class="organization-links">
                    <a href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$organization.id`"}"
                       target="_blank">{$organization.display_name}</a>
                </div>
            </div>
        </div>
    </div>
{/foreach}
