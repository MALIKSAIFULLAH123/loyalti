import { Link, useGlobal } from '@metafox/framework';
import { PagesItemProps } from '@metafox/pages/types';
import {
  FeaturedFlag,
  ItemMedia,
  ItemText,
  ItemTitle,
  ItemView,
  SponsorFlag,
  UserAvatar,
  ItemAction,
  ButtonList,
  LineIcon,
  PendingFlag,
  ItemSummary
} from '@metafox/ui';
import { Box, useMediaQuery, Button, styled, Typography } from '@mui/material';
import { useTheme } from '@mui/material/styles';
import * as React from 'react';
import LoadingSkeleton from './LoadingSkeleton';

const ButtonListWrapper = styled(ButtonList, { slot: 'ButtonListWrapper' })(
  ({ theme }) => ({
    marginTop: theme.spacing(1)
  })
);

const ItemTitleWrapper = styled(ItemTitle, { slot: 'ItemTitleWrapper' })(
  ({ theme }) => ({
    '& .MuiTypography-root': {
      display: 'flex',
      flexDirection: 'column'
    },
    '& .MuiLink-root': {
      display: 'block',
      overflow: 'hidden',
      textOverflow: 'ellipsis',
      whiteSpace: 'nowrap'
    }
  })
);

const ItemMediaStyled = styled(ItemMedia)(({ theme }) => ({
  height: '100%'
}));

export default function MyPendingPageItemMainCard({
  item,
  identity,
  handleAction,
  state,
  actions,
  wrapAs,
  wrapProps
}: PagesItemProps) {
  const theme = useTheme();
  const isSmallScreen = useMediaQuery(theme.breakpoints.down('sm'));
  const { ItemActionMenu, i18n } = useGlobal();

  if (!item) return null;

  const { title, link: to, extra, summary } = item;

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
    >
      <ItemMediaStyled>
        <UserAvatar user={item} size={isSmallScreen ? 56 : 80} />
      </ItemMediaStyled>
      <ItemText>
        <Box>
          <ItemTitleWrapper>
            <Box
              sx={{
                display: { md: 'inline', sm: 'block' },
                marginBottom: theme.spacing(0.75)
              }}
            >
              <PendingFlag variant="itemView" value={item.is_pending} />
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
          </ItemTitleWrapper>
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
          <ButtonListWrapper>
            {extra?.can_approve && (
              <>
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
              </>
            )}
            <ItemActionMenu
              identity={identity}
              state={state}
              handleAction={handleAction}
              size={extra?.can_approve ? 'small' : 'medium'}
              variant="outlined-square"
              color="primary"
              icon="ico-dottedmore-o"
              tooltipTitle={i18n.formatMessage({ id: 'more_options' })}
            />
          </ButtonListWrapper>
        </ItemAction>
      </ItemText>
    </ItemView>
  );
}

MyPendingPageItemMainCard.LoadingSkeleton = LoadingSkeleton;
