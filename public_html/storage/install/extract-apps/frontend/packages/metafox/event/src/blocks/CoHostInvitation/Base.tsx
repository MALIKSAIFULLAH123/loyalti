import { useGlobal, getItemSelector, GlobalState } from '@metafox/framework';
import { Block, BlockContent, BlockHeader } from '@metafox/layout';
import { EventDetailViewProps as Props } from '@metafox/event/types';
import React from 'react';
import { Box, Button, CardActions, styled } from '@mui/material';
import { ItemSummary, LineIcon } from '@metafox/ui';
import ProfileLink from '@metafox/feed/components/FeedItemView/ProfileLink';
import { useSelector } from 'react-redux';
import { camelCase } from 'lodash';

const HeadlineSpan = styled('span', { name: 'HeadlineSpan' })(({ theme }) => ({
  paddingRight: theme.spacing(0.5),
  color: theme.palette.text.secondary
}));

const StyledLink = styled('span')(({ theme }) => ({
  '& a': {
    fontWeight: theme.typography.fontWeightBold,
    color: theme.palette.text.primary
  }
}));

const BlockContentStyled = styled(BlockContent, { name: 'BlockContentStyled' })(
  ({ theme }) => ({
    display: 'flex',
    padding: 16
  })
);

export default function CoHostInvitation({
  item,
  user,
  title,
  gridVariant = 'listView',
  actions,
  ...rest
}: Props) {
  const { i18n } = useGlobal();
  const pending_host_invite = useSelector((state: GlobalState) =>
    getItemSelector(state, item?.pending_host_invite)
  );

  if (!pending_host_invite) return null;

  return (
    <Block {...rest}>
      <BlockHeader title={title} />
      <BlockContentStyled>
        <Box pr={2}>
          <LineIcon sx={{ fontSize: 32 }} icon="ico-envelope-opened-o" />
        </Box>
        <Box>
          <ItemSummary>
            <HeadlineSpan>
              {i18n.formatMessage(
                {
                  id: 'person_who_invite_me_co_host'
                },
                {
                  value: (
                    <StyledLink>
                      <ProfileLink user={user} className="" />
                    </StyledLink>
                  )
                }
              )}
            </HeadlineSpan>
          </ItemSummary>
          <CardActions
            data-testid={camelCase('event co-host invitation action')}
            sx={{ paddingTop: 2, paddingLeft: 0 }}
          >
            <Button
              data-testid={camelCase('event co-host invitation accept')}
              sx={{ width: '100px' }}
              variant="contained"
              size="small"
              component="h5"
              onClick={actions.approveCoHostInvite}
            >
              {i18n.formatMessage({ id: 'accept' })}
            </Button>
            <Button
              data-testid={camelCase('event co-host invitation decline')}
              sx={{ width: '100px' }}
              variant="outlined"
              size="small"
              component="h5"
              onClick={actions.denyCoHostInvite}
            >
              {i18n.formatMessage({ id: 'decline' })}
            </Button>
          </CardActions>
          <Box
            data-testid={camelCase(
              'event co-host invitation expired description'
            )}
            color="text.hint"
          >
            {pending_host_invite?.expired_description}
          </Box>
        </Box>
      </BlockContentStyled>
    </Block>
  );
}
