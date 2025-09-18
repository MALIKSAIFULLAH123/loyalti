import { Link, useGlobal } from '@metafox/framework';
import { PagesItemProps } from '@metafox/pages/types';
import {
  ButtonList,
  FeaturedFlag,
  ItemAction,
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  LineIcon,
  SponsorFlag,
  UserAvatar
} from '@metafox/ui';
import { Box, Button, useMediaQuery, styled, Typography } from '@mui/material';
import { useTheme } from '@mui/material/styles';
import * as React from 'react';

const ButtonListWrapper = styled(ButtonList, { slot: 'ButtonListWrapper' })(
  ({ theme }) => ({
    marginLeft: theme.spacing(1)
  })
);
const ButtonListAction = styled(ButtonList, { slot: 'ButtonListWrapper' })(
  ({ theme }) => ({
    marginTop: theme.spacing(1)
  })
);

export default function PendingPageItemMainCard({
  item,
  identity,
  actions,
  wrapAs,
  wrapProps,
  handleAction,
  state
}: PagesItemProps) {
  const { useSession, i18n, ItemActionMenu } = useGlobal();
  const { loggedIn } = useSession();
  const theme = useTheme();
  const isSmallScreen = useMediaQuery(theme.breakpoints.down('sm'));

  if (!item) return null;

  const { title, link: to, summary } = item;

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemMedia>
        <UserAvatar user={item} size={isSmallScreen ? 56 : 80} />
      </ItemMedia>
      <ItemText>
        <Box>
          <ItemTitle>
            <Box
              sx={{
                display: { md: 'inline', sm: 'block' },
                marginBottom: theme.spacing(0.75)
              }}
            >
              <FeaturedFlag value={item.is_featured} variant="itemView" />
              <SponsorFlag
                value={item.is_sponsor}
                variant="itemView"
                item={item}
              />
            </Box>
            <Link to={to} color={'inherit'}>
              {title}
            </Link>
          </ItemTitle>
          {summary ? (
            <ItemSummary>
              <Typography
                variant="body2"
                component="div"
                data-testid="itemSummary"
                color="text.secondary"
              >
                {summary}
              </Typography>
            </ItemSummary>
          ) : null}
        </Box>
        <ItemAction>
          {loggedIn && (
            <ButtonListAction sx={isSmallScreen && { flexFlow: 'wrap' }}>
              <Button
                variant="contained"
                size="small"
                onClick={actions.approvePendingPage}
                startIcon={<LineIcon icon={'ico-check'} />}
              >
                {i18n.formatMessage({ id: 'approve' })}
              </Button>
              <Button
                variant="outlined"
                color="error"
                size="small"
                startIcon={<LineIcon icon={'ico-close'} />}
                onClick={actions.declinePendingPage}
              >
                {i18n.formatMessage({ id: 'decline' })}
              </Button>
              {loggedIn || !item.extra ? (
                <ItemAction>
                  <ButtonListWrapper>
                    <ItemActionMenu
                      identity={identity}
                      state={state}
                      handleAction={handleAction}
                      size={'small'}
                      variant="outlined-square"
                      color="primary"
                      icon="ico-dottedmore-o"
                      tooltipTitle={i18n.formatMessage({
                        id: 'more_options'
                      })}
                    />
                  </ButtonListWrapper>
                </ItemAction>
              ) : null}
            </ButtonListAction>
          )}
        </ItemAction>
      </ItemText>
    </ItemView>
  );
}
