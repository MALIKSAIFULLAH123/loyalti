/**
 * @type: ui
 * name: notification.ui.notificationPopper
 * chunkName: boot
 */
import { RefOf, useGlobal } from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { NOTIFICATION_POPPER_PAGING_IDS } from '@metafox/notification/constant';
import { Popper } from '@metafox/ui';
import { Box, Paper, PopperProps, Typography } from '@mui/material';
import React from 'react';

export default function NotificationPopper({
  anchorRef,
  open,
  closePopover,
  ...rest
}: PopperProps & {
  anchorRef: RefOf<HTMLDivElement>;
  closePopover: () => void;
}) {
  const { ListView, i18n, dispatch, ItemActionMenu, useActionControl } =
    useGlobal();
  const [handleAction, state] = useActionControl(null, {});
  const pagingId = NOTIFICATION_POPPER_PAGING_IDS;
  const dataSource = { apiUrl: '/notification', apiParams: { limit: 10 } };

  React.useEffect(() => {
    if (open) {
      dispatch({
        type: 'core/status/clearNotification'
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open]);

  return (
    <Popper
      id="notification"
      data-testid="notifications"
      anchorEl={anchorRef.current}
      open={open}
      popperOptions={{
        strategy: 'fixed'
      }}
      {...rest}
    >
      <Paper
        sx={{
          width: 360,
          overflow: 'hidden',
          userSelect: 'none'
        }}
      >
        <Box
          sx={{
            p: [1.5, 2],
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between'
          }}
        >
          <Typography variant="h4" sx={{ flex: 1 }}>
            {i18n.formatMessage({ id: 'notifications' })}
          </Typography>
          <ItemActionMenu
            appName="notification"
            menuName="notificationSettingsMenu"
            handleAction={handleAction}
            state={state}
            disablePortal
          />
        </Box>
        <ScrollContainer
          autoHide
          autoHeight
          autoHeightMax={400}
          autoHeightMin={40}
        >
          <ListView
            limitItemsLoadSmooth={2}
            canLoadMore
            canLoadSmooth
            dataSource={dataSource}
            pagingId={pagingId}
            clearDataOnUnMount
            itemView={'notification.itemView.smallCard'}
            gridLayout="Notification - Small Lists"
            itemLayout="Notification - Small Lists"
            emptyPage="core.block.no_content"
            emptyPageProps={{
              title: i18n.formatMessage({ id: 'no_notifications' })
            }}
          />
        </ScrollContainer>
      </Paper>
    </Popper>
  );
}
