/**
 * @type: dialog
 * name: marketplace.dialog.viewInvitedDialog
 */
import { Dialog, DialogTitle, DialogContent } from '@metafox/dialog';
import { useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import React from 'react';

export default function ViewGuestDialog({ item, dataSource }) {
  const { useDialog, i18n, ListView, useIsMobile } = useGlobal();
  const { dialogProps } = useDialog();
  const isMobile = useIsMobile();
  const { resource_name, item_id } = item;
  const pagingId = `marketplace/${resource_name}/${item_id}`;
  const gridContainerProps = { spacing: 0 };

  return (
    <Dialog {...dialogProps} maxWidth="sm" fullWidth>
      <DialogTitle>{i18n.formatMessage({ id: 'invited_people' })}</DialogTitle>
      <DialogContent>
        <ScrollContainer
          autoHide
          autoHeight
          autoHeightMax={isMobile ? '100%' : 300}
        >
          <ListView
            dataSource={dataSource}
            pagingId={pagingId}
            canLoadMore
            clearDataOnUnMount
            gridContainerProps={gridContainerProps}
            gridLayout="Friend - Small List"
            itemLayout="User - List"
            itemView={'marketplace.itemView.peopleCard'}
            canLoadSmooth
            numberOfItemsPerPage={10}
            emptyPage='core.block.no_content_with_button'
            emptyPageProps={{
              icon: 'ico-user3-three',
              description: 'no_one_has_been_invited',
              buttonCustom: item?.extra?.can_invite
                ? {
                    component: 'marketplace.invitedButton',
                    props: { item, type: 'marketplace/invitePeopleToCome' }
                  }
                : null
            }}
          />
        </ScrollContainer>
      </DialogContent>
    </Dialog>
  );
}
