/**
 * @type: ui
 * name: friend_request.ui.friendRequestsPopper
 * chunkName: boot
 */
import {
  RefOf,
  useGlobal,
  Link,
  GlobalState,
  getPagingSelector
} from '@metafox/framework';
import { ScrollContainer } from '@metafox/layout';
import { Box, Paper, PopperProps, Typography } from '@mui/material';
import { useSelector } from 'react-redux';
import React, { useEffect } from 'react';
import { isEmpty } from 'lodash';
import { Popper } from '@metafox/ui';

export default function FriendRequestsPopper({
  anchorRef,
  open,
  ...rest
}: PopperProps & { anchorRef: RefOf<HTMLDivElement> }) {
  const { i18n, dispatch, ListView } = useGlobal();
  const pagingId = '/friend/request?view=pending&popper';
  const dataSource = {
    apiUrl: '/friend/request',
    apiParams: { view: 'pending', limit: 10 }
  };
  const pagingData = useSelector((state: GlobalState) =>
    getPagingSelector(state, pagingId)
  );

  useEffect(() => {
    if (open) {
      dispatch({
        type: 'core/status/clearFriend'
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open]);

  return (
    <Popper
      id="requests"
      data-testid="popupNewFiendRequest"
      open={open}
      anchorEl={anchorRef.current}
      popperOptions={{
        strategy: 'fixed'
      }}
      {...rest}
    >
      <Paper sx={{ width: 360, overflow: 'hidden', userSelect: 'none' }}>
        <Box
          sx={{
            p: [1.5, 2],
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'space-between'
          }}
        >
          <Typography variant="h4" sx={{ flex: 1 }}>
            {i18n.formatMessage({ id: 'friend_requests' })}
          </Typography>
          {!isEmpty(pagingData?.ids) && (
            <Link color="primary" to={'/friend/requests'}>
              <Typography variant="body2">
                {i18n.formatMessage({ id: 'all_requests' })}
              </Typography>
            </Link>
          )}
        </Box>
        <ScrollContainer autoHide autoHeight autoHeightMax={400}>
          <ListView
            canLoadMore
            gridLayout="Friend - Small List"
            itemLayout="Friend - Small List"
            dataSource={dataSource}
            pagingId={pagingId}
            clearDataOnUnMount
            itemView="friend_pendingRequest.itemView.smallCard"
            emptyPage="core.block.no_content"
            emptyPageProps={{
              variant: 'popover',
              title: 'no_friend_request'
            }}
          />
        </ScrollContainer>
      </Paper>
    </Popper>
  );
}
