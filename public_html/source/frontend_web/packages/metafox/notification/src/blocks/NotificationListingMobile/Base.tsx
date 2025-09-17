import { getStatusSelector } from '@metafox/core/selectors/status';
import { ListViewBlockProps, useGlobal } from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
import { styled, Tab, Tabs } from '@mui/material';
import React, { useState } from 'react';
import { useSelector } from 'react-redux';
import useStyles from './styles';
import { NOTIFICATION_PAGING_IDS } from '@metafox/notification/constant';

const NOTIFICATIONS = 'notifications';
const FRIENDS = 'friend_requests';

const useCustomTabs = () => {
  const tabs = {
    notifications: {
      id: 'notifications',
      value: NOTIFICATIONS,
      invisible: true,
      itemView: 'notification.itemView.mainCard',
      gridLayout: 'Notification - Small Lists',
      itemLayout: 'Notification - Small Lists',
      emptyPage: 'core.block.no_content_with_icon',
      emptyPageProps: {
        noHeader: true,
        contentStyle: {
          bgColor: '0'
        },
        noBlock: 1,
        title: 'No Notifications',
        image: 'ico-bell2-off-o'
      },
      dataSource: { apiUrl: '/notification' },
      pagingId: NOTIFICATION_PAGING_IDS
    },
    friend_requests: {
      id: 'friend_requests',
      value: FRIENDS,
      itemView: 'friend_pendingRequest.itemView.smallCard',
      gridLayout: 'Friend - Small List',
      itemLayout: 'Friend - Small List - Paper',
      invisible: true,
      dataSource: {
        apiUrl: '/friend/request',
        apiParams: { view: 'pending', limit: 10 }
      },
      pagingId: '/friend/request?view=pending',
      emptyPage: 'core.block.no_content',
      emptyPageProps: {
        title: 'no_friend_request'
      },
      blockProps: {
        contentStyle: {
          sx: {
            pt: 2,
            bgColor: 'red'
          },
          pt: 2,
          bgColor: 'red'
        },
        blockStyle: {
          sx: {
            pt: 2,
            bgColor: 'red'
          }
        }
      },
      total: 'new_friend_request'
    }
  };

  return tabs;
};

const ButtonWrapper = styled('div')(({ theme }) => ({
  margin: theme.spacing(2),
  marginLeft: 0,
  display: 'flex',
  justifyContent: 'flex-end'
}));
const Badge = styled('span')(({ theme }) => ({
  color: theme.palette.error.main
}));

type Props = ListViewBlockProps;

export default function NotificationListingBlockMobile({
  title,
  gridVariant = 'listView',
  gridLayout,
  itemLayout,
  itemView,
  displayLimit,
  ...rest
}: Props) {
  const { ListView, jsxBackend, i18n } = useGlobal();

  const status = useSelector(getStatusSelector);

  const tabs = useCustomTabs();
  const [value, setValue] = useState<string>(NOTIFICATIONS);
  const classes = useStyles();

  const gridContainerProps = { spacing: 0 };

  const MarkAllAsReadButton = jsxBackend.get('notification.markAllAsRead');
  const EditNotificationSettingButton = jsxBackend.get(
    'notification.editNotificationSetting'
  );
  const DeleteAllButton = jsxBackend.get('notification.deleteAll');

  const handleChange = (event: React.ChangeEvent<{}>, newValue: string) => {
    setValue(newValue);
  };

  return (
    <Block {...rest}>
      <Tabs
        centered
        value={value}
        onChange={handleChange}
        className={classes.customTabs}
        variant="fullWidth"
        aria-label="full width tabs example"
      >
        {Object.keys(tabs)
          .filter(x => tabs[x].invisible)
          .map(tab => (
            <Tab
              key={tabs[tab].id}
              disableRipple
              label={
                <span>
                  {i18n.formatMessage({ id: tabs[tab].id })}{' '}
                  {status[tabs[tab].total] ? (
                    <Badge>({status[tabs[tab].total]})</Badge>
                  ) : null}
                </span>
              }
              value={tabs[tab].value}
              aria-label={tabs[tab].value}
            />
          ))}
      </Tabs>

      {tabs[value].value === NOTIFICATIONS ? (
        <ButtonWrapper>
          <MarkAllAsReadButton />
          <DeleteAllButton />
          <EditNotificationSettingButton />
        </ButtonWrapper>
      ) : null}
      <BlockContent>
        <ListView
          dataSource={tabs[value].dataSource}
          canLoadMore
          canLoadSmooth
          clearDataOnUnMount
          gridContainerProps={gridContainerProps}
          gridLayout={tabs[value].gridLayout}
          itemLayout={tabs[value].itemLayout}
          emptyPage={tabs[value].emptyPage}
          emptyPageProps={tabs[value].emptyPageProps}
          itemView={tabs[value].itemView}
          pagingId={tabs[value].pagingId}
        />
      </BlockContent>
    </Block>
  );
}
NotificationListingBlockMobile.displayName = 'NotificationListingBlockMobile';
