/**
 * @type: dialog
 * name: event.dialog.viewGuestDialog
 */
import { Dialog, DialogTitle } from '@metafox/dialog';
import { APP_EVENT, EventItemProps } from '@metafox/event';
import { useGlobal, useResourceAction } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { Tab, Tabs } from '@mui/material';
import React, { useRef, useState } from 'react';
import useStyles from './styles';

const useCustomTabs = item => {
  const dataSourceMember = useResourceAction(
    APP_EVENT,
    'event_member',
    'viewAll'
  );
  const dataSourceInvite = useResourceAction(
    APP_EVENT,
    'event_invite',
    'viewAll'
  );

  const tabs = {
    joined: {
      id: 'going',
      value: 'joined',
      itemView: 'event.itemView.guestSmallCard',
      emptyPage: 'core.block.no_content',
      invisible: true,
      dataSource: {
        apiUrl: dataSourceMember.apiUrl,
        apiParams: {
          event_id: item.id,
          view: 'joined'
        }
      },
      total: 'total_member'
    },
    interested: {
      id: 'interested',
      value: 'interested',
      itemView: 'event.itemView.guestSmallCard',
      emptyPage: 'core.block.no_content_with_button',
      emptyPageProps: {
        icon: 'ico-user3-three',
        description: 'no_one_has_been_interested'
      },
      invisible: true,
      dataSource: {
        apiUrl: dataSourceMember.apiUrl,
        apiParams: {
          event_id: item.id,
          view: 'interested'
        }
      },
      total: 'total_interested'
    },
    invited: {
      id: 'invited',
      value: 'invited',
      invisible: item.extra?.can_invite || false,
      itemView: 'event.itemView.inviteCard',
      emptyPage: 'core.block.no_content_with_button',
      emptyPageProps: {
        icon: 'ico-user3-three',
        description: 'no_one_has_been_invited',
        buttonCustom: item.extra?.can_invite
          ? {
              component: 'event.invitedButton',
              props: { item, type: 'event/invitePeopleToCome' }
            }
          : null
      },
      dataSource: {
        apiUrl: dataSourceInvite.apiUrl,
        apiParams: {
          event_id: item.id
        }
      },
      total: 'total_pending_invite'
    }
  };

  return tabs;
};

export default function ViewGuestDialog({
  item,
  defaultTab = 'joined'
}: EventItemProps & { defaultTab: string }) {
  const classes = useStyles();
  const tabs = useCustomTabs(item);
  const [value, setValue] = useState<string>(defaultTab);
  const scrollRef = useRef();
  const { useDialog, i18n, ListView, useGetItem } = useGlobal();
  const { dialogProps } = useDialog();

  const { resource_name, item_id } = item;
  const pagingId = `event_guest/${resource_name}/${item_id}/${value}`;
  const gridContainerProps = { spacing: 0 };

  const itemEvent = useGetItem(item._identity);

  const handleChange = (event: React.ChangeEvent<{}>, newValue: string) => {
    setValue(newValue);
  };

  return (
    <Dialog {...dialogProps} maxWidth="xs" fullWidth>
      <DialogTitle>{i18n.formatMessage({ id: 'guests' })}</DialogTitle>
      <Tabs
        value={value}
        onChange={handleChange}
        className={classes.customTabs}
      >
        {Object.keys(tabs)
          .filter(x => tabs[x].invisible)
          .map(tab => (
            <Tab
              key={tabs[tab].id}
              disableRipple
              label={`${i18n.formatMessage({ id: tabs[tab].id })} (${
                itemEvent.statistic[tabs[tab]?.total] || 0
              })`}
              value={tabs[tab].value}
              aria-label={tabs[tab].value}
            />
          ))}
      </Tabs>
      <div className={classes.dialogContent} ref={scrollRef}>
        <ScrollContainer autoHeightMax={'100%'} autoHide autoHeight>
          <ListView
            dataSource={tabs[value].dataSource}
            pagingId={pagingId}
            canLoadMore
            clearDataOnUnMount
            gridContainerProps={gridContainerProps}
            gridLayout="Friend - Small List"
            itemLayout="Friend - Small List"
            emptyPage={tabs[value].emptyPage}
            emptyPageProps={tabs[value].emptyPageProps}
            itemView={tabs[value].itemView}
          />
        </ScrollContainer>
      </div>
    </Dialog>
  );
}
