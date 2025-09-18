/**
 * @type: dialog
 * name: event.dialog.viewHostDialog
 */
import { Dialog, DialogTitle } from '@metafox/dialog';
import { APP_EVENT, EventItemProps } from '@metafox/event';
import { useGlobal, useResourceAction, useGetItem } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { compactData } from '@metafox/utils';
import { Tab, Tabs } from '@mui/material';
import React, { useRef, useState } from 'react';
import useStyles from './styles';

const useCustomTabs = item => {
  const dataSourceHost = useResourceAction(
    APP_EVENT,
    'event_member',
    'viewHosts'
  );

  const dataSourceInvite = useResourceAction(
    APP_EVENT,
    'event_host_invite',
    'viewAll'
  );

  const mappingHost = compactData(dataSourceHost.apiParams, { id: item.id });
  const mappingInvite = compactData(dataSourceInvite?.apiParams, {
    id: item.id
  });

  const tabs = {
    list_host: {
      id: 'host_list',
      value: 'list_host',
      itemView: 'event.itemView.hostCard',
      invisible: true,
      dataSource: {
        apiUrl: dataSourceHost?.apiUrl,
        apiParams: mappingHost
      },
      emptyPage: 'core.block.no_content',
      total: 'total_host'
    },
    invited: {
      id: 'invited',
      value: 'invited',
      invisible: item.extra?.can_invite && item.extra.can_manage_host,
      itemView: 'event.itemView.hostInviteCard',
      dataSource: {
        apiUrl: dataSourceInvite?.apiUrl,
        apiParams: mappingInvite
      },
      emptyPage: 'core.block.no_content_with_button',
      emptyPageProps: {
        icon: 'ico-user3-three',
        description: 'no_one_has_been_invited',
        buttonCustom: {
          component: 'event.invitedButton',
          props: { item, type: 'event/inviteToComeHost' }
        }
      },
      total: 'total_pending_host_invite'
    }
  };

  return tabs;
};

export default function ViewHostDialog({
  item,
  defaultTab = 'list_host'
}: EventItemProps & { defaultTab: string }) {
  const classes = useStyles();
  const tabs = useCustomTabs(item);
  const [value, setValue] = useState<string>(defaultTab);
  const scrollRef = useRef();
  const { useDialog, i18n, ListView } = useGlobal();
  const { dialogProps } = useDialog();

  const { resource_name, item_id } = item;
  const pagingId = `event_host/${resource_name}/${item_id}/${value}`;
  const gridContainerProps = { spacing: 0 };

  const itemEvent = useGetItem(item._identity);

  const handleChange = (_: React.ChangeEvent<{}>, newValue: string) => {
    setValue(newValue);
  };

  return (
    <Dialog {...dialogProps} maxWidth="xs" fullWidth>
      <DialogTitle>
        {i18n.formatMessage({ id: 'manage_host_list' })}
      </DialogTitle>
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
            gridContainerProps={gridContainerProps}
            gridLayout="Friend - Small List"
            itemLayout="Friend - Small List"
            itemView={tabs[value].itemView}
            emptyPage={tabs[value].emptyPage}
            emptyPageProps={tabs[value].emptyPageProps}
          />
        </ScrollContainer>
      </div>
    </Dialog>
  );
}
