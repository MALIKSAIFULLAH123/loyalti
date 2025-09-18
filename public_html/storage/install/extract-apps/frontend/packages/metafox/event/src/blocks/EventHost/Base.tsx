import { useGlobal, useResourceAction } from '@metafox/framework';
import {
  Block,
  BlockContent,
  BlockHeader,
  usePageParams
} from '@metafox/layout';
import { EventDetailViewProps as Props } from '@metafox/event/types';
import React from 'react';
import { APP_EVENT } from '@metafox/event';
import { Button, styled } from '@mui/material';
import { camelCase } from 'lodash';

const InviteButton = styled('div')(({ theme }) => ({
  margin: theme.spacing(0, 2, 2, 2),
  padding: theme.spacing(0, 0, 2, 0),
  '& button': {
    borderColor: theme.palette.primary.main
  }
}));

export default function EventHost({
  item,
  user,
  title,
  gridVariant = 'listView',
  itemView = 'event.itemView.hostSmallCard',
  itemLayout = 'Profile Friends Small Lists',
  gridLayout = 'Profile Friends Small Lists',
  displayLimit,
  ...rest
}: Props) {
  const { ListView, i18n, dispatch } = useGlobal();
  const { _identity } = item;
  const dataSource = useResourceAction(APP_EVENT, 'event_member', 'viewHosts');
  const pageParams = usePageParams();
  const pagingId = `event-member?event_id=${pageParams.id}&view=host`;

  if (!item) return null;

  const handleClickInviteHost = () => {
    dispatch({
      type: 'event/inviteToComeHost',
      payload: { identity: _identity }
    });
  };

  return (
    <Block {...rest}>
      <BlockHeader title={title} />
      <BlockContent>
        <ListView
          displayLimit={displayLimit}
          itemView="event.itemView.hostSmallCard"
          itemLayout="Profile Friends Small Lists"
          gridLayout={gridLayout}
          pagingId={pagingId}
          gridVariant={gridVariant}
          dataSource={dataSource}
        />
        {item?.extra?.can_invite && item?.extra?.can_manage_host && (
          <InviteButton>
            <Button
              data-testid={camelCase('event host invite button')}
              variant="outlined"
              color="primary"
              fullWidth
              onClick={handleClickInviteHost}
            >
              {i18n.formatMessage({ id: 'invite_hosts' })}
            </Button>
          </InviteButton>
        )}
      </BlockContent>
    </Block>
  );
}
EventHost.displayName = 'EventHost';
