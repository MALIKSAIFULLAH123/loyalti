import { Link, useGlobal } from '@metafox/framework';
import { PagesItemProps } from '@metafox/pages/types';
import { mappingRelationship } from '@metafox/pages/utils';
import {
  ButtonList,
  FeaturedIcon,
  ItemAction,
  ItemMedia,
  ItemSummary,
  ItemText,
  ItemTitle,
  ItemView,
  SponsorFlag,
  Statistic,
  UserAvatar,
  LineIcon,
  PendingFlag
} from '@metafox/ui';
import {
  Box,
  IconButton,
  useMediaQuery,
  Typography,
  Button
} from '@mui/material';
import { useTheme, styled } from '@mui/material/styles';
import * as React from 'react';

const TypographyStyled = styled(Typography)(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15),
  fontWeight: '600'
}));

const IconButtonWrapper = styled(IconButton, { slot: 'IconButtonWrapper' })(
  ({ theme }) => ({
    width: 'auto',
    padding: theme.spacing(1, 2),
    '& p': {
      marginLeft: theme.spacing(1)
    }
  })
);

const ItemWrapper = styled(Box, { slot: 'ItemTitleWrapper' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center'
}));

const ItemMediaStyled = styled(ItemMedia)(({ theme }) => ({
  height: '100%'
}));

const ItemTextStyled = styled(ItemText)(({ theme }) => ({
  height: '100%'
}));
const ButtonListWrapper = styled(ButtonList, { slot: 'ButtonListWrapper' })(
  ({ theme }) => ({
    marginTop: theme.spacing(1)
  })
);

const LinkStyled = styled(Link, { name: 'Link' })(({ theme }) => ({
  whiteSpace: 'nowrap',
  overflow: 'hidden',
  textOverflow: 'ellipsis',
  display: 'block'
}));

export default function PageItemMainCard({
  item,
  identity,
  handleAction,
  state,
  actions,
  wrapAs,
  wrapProps,
  itemProps
}: PagesItemProps) {
  const { ItemActionMenu, useSession, i18n } = useGlobal();
  const { loggedIn } = useSession();
  const theme = useTheme();
  const isSmallScreen = useMediaQuery(theme.breakpoints.down('sm'));

  if (!item) return null;

  const {
    title,
    statistic,
    is_liked,
    extra,
    link: to,
    is_owner,
    is_pending,
    summary
  } = item;

  const reactButton = mappingRelationship(
    is_owner,
    is_liked,
    extra?.can_unlike || false,
    extra?.can_like || false,
    actions
  );

  return (
    <ItemView
      wrapAs={wrapAs}
      wrapProps={wrapProps}
      testid={`${item.resource_name}`}
      data-eid={identity}
      identity={identity}
    >
      <ItemMediaStyled>
        <Link
          to={to}
          hoverCard={`/page/${item?.id}`}
          sx={{
            '&:hover': {
              textDecoration: 'none'
            }
          }}
          identityTracking={identity}
        >
          <UserAvatar noLink user={item} size={isSmallScreen ? 56 : 80} />
        </Link>
      </ItemMediaStyled>
      <ItemTextStyled>
        <Box>
          <Box sx={{ mb: 1 }}>
            <SponsorFlag
              value={item.is_sponsor}
              variant="itemView"
              item={item}
            />
            <PendingFlag variant="itemView" value={item.is_pending} />
          </Box>
          <ItemWrapper>
            <ItemTitle>
              <LinkStyled
                to={to}
                color={'inherit'}
                hoverCard={`/page/${item?.id}`}
                identityTracking={identity}
              >
                {title}
              </LinkStyled>
            </ItemTitle>
            <FeaturedIcon
              sx={{ mb: 1 }}
              icon="ico-check-circle"
              value={item?.is_featured}
            />
          </ItemWrapper>
          {is_pending && summary ? (
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
          ) : (
            <ItemSummary>
              <Statistic
                values={statistic}
                display={'total_like'}
                skipZero={false}
              />
            </ItemSummary>
          )}
        </Box>
        {loggedIn ? (
          <ItemAction>
            {is_pending && extra?.can_approve ? (
              <ButtonListWrapper sx={isSmallScreen && { flexFlow: 'wrap' }}>
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
                {loggedIn || extra ? (
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
                ) : null}
              </ButtonListWrapper>
            ) : (
              <ButtonListWrapper>
                <IconButtonWrapper
                  aria-label={reactButton.textId}
                  size="medium"
                  color="primary"
                  variant={reactButton.variant}
                  disabled={reactButton.disabled}
                  onClick={reactButton.actions}
                  className={reactButton.textId}
                >
                  <LineIcon icon={reactButton.icon} />
                  <TypographyStyled variant="body1">
                    {i18n.formatMessage({ id: reactButton.textId })}
                  </TypographyStyled>
                </IconButtonWrapper>
                {extra ? (
                  <ItemActionMenu
                    identity={identity}
                    state={state}
                    handleAction={handleAction}
                    size="medium"
                    variant="outlined-square"
                    color="primary"
                    icon="ico-dottedmore-o"
                    tooltipTitle={i18n.formatMessage({ id: 'more_options' })}
                  />
                ) : null}
              </ButtonListWrapper>
            )}
          </ItemAction>
        ) : null}
      </ItemTextStyled>
    </ItemView>
  );
}
