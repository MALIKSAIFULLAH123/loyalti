/**
 * @type: itemView
 * name: sevent_ticket.itemView.mainCard
 */
import actionCreators from '@ft/sevent/actions/seventItemActions';
// types
import { SeventItemProps as ItemProps } from '@ft/sevent/types';
import { connectItemView, useGlobal, Link } from '@metafox/framework';
import Grid from '@mui/material/Grid';
import HtmlViewer from '@metafox/html-viewer';
// components
import {
  FormatDate,
  ItemAction,
  ItemView,
  ItemText,
  HtmlViewerWrapper,
  ItemTitle
} from '@metafox/ui';
import { styled, Box, Button } from '@mui/material';
import Buy from './Buy';
import React from 'react';

const name = 'SeventTicketItemView';
const SeventDescription = styled('span', {
  slot: 'FlagWrapper',
  name: 'SeventDescription'
})(({ theme }) => ({
  display: 'inline-flex',
  fontSize: '14px',
  lineHeight: '22px',
  color: theme.palette.text.secondary,
  marginTop: theme.spacing(1),
  marginBottom: theme.spacing(4)
}));
const ItemTitleStyled = styled(ItemTitle, {
  name,
  slot: 'ItemTitleStyled',
  shouldForwardProp: prop => prop !== 'isMobile'
})<{ isMobile: boolean }>(({ theme, isMobile }) => ({
  maxHeight: 'auto',
  fontSize: '16px',
  fontWeight: 'bold',
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  '& h4': {
    height: 'auto',
    maxHeight: '100%',
    fontWeight: 'bold'
  }
}));

export function SeventTicketItemView({
  identity,
  item,
  wrapProps,
  wrapAs,
  actions,
  user,
  state,
  handleAction
}: ItemProps) {
  const {
    ItemActionMenu,
    useIsMobile,
    useTheme,
    useLoggedIn
  } = useGlobal();
  const isMobile = useIsMobile();
  const theme = useTheme();
  const { i18n, apiClient } = useGlobal();
  const isLogged = useLoggedIn();

  const canBuy = !item.is_ticket_expiry && !item.event_is_expiry && (item.remaining_qty > 0 || item.is_unlimited);

  if (!item || !user) return null;

  const ItemViewBox = styled('div', { name: 'ItemViewBox', slot: 'ItemViewBox' })(
      ({ theme }) => ({
        width: '100%',
        borderBottom: theme.mixins.border('secondary'),
        marginBottom: theme.spacing(1),
        paddingBottom: theme.spacing(1),
        justifyContent: 'space-between',
        [theme.breakpoints.down('sm')]: {
          flexDirection: 'column-reverse',
          marginBottom: theme.spacing(1),
          paddingBottom: theme.spacing(1)
        }
      })
    );
  const ItemTextStyled = styled(ItemText, { name: 'ItemTextStyled', slot: 'ItemViewBox' })(
      ({ theme }) => ({
        [theme.breakpoints.down('sm')]: {
          paddingLeft: 0,
          paddingRight: 0,
          width: '100%'
        }
      })
    );
    
  return (
    <ItemView wrapAs={wrapAs} wrapProps={wrapProps} testid="blog" sx={{ br: 0, margin: 0, padding: 0 }}>
    <ItemViewBox>
      <Grid container spacing={2}>
      {!isMobile ? (
        <Grid item md={1} xl={1} style={{ textAlign: 'center' }}>
            <i className='ico ico-ticket-o' style={{ color: theme.palette.primary.main, 
              fontSize: isMobile ? '30px' : '50px' }}/>
        </Grid>
      ) : null}
      <Grid item xs={12} md={11} xl={11}>
        <ItemTextStyled>
          <Box display='flex'>
            <ItemTitleStyled lines={3} isMobile={isMobile}>
              <span style={{ fontWeight: 'bold' }}>
                {item.title} 
              </span>
            </ItemTitleStyled>
          </Box>
          <div style={{ color: theme.palette.text.secondary, marginTop: '4px' }}>
          <span style={{ fontWeight: 'bold', fontSize: '14px' }}>
                {item.format_amount}
            </span>,&nbsp;
            {i18n.formatMessage({ id: 'sevent_will_expiry' })}:&nbsp;
            <FormatDate
                data-testid="expiryDate"
                value={item.expiry_date}
                format="ll"
              />
          </div>
            <ItemAction
              placement={'bottom-end'}
              spacing="normal"
              sx={isMobile ? { marginBottom: '4px', top: 0, right: 0 } : { top: 0, right: 0 }}
            >
              <ItemActionMenu
                identity={identity}
                icon={'ico-dottedmore-o'}
                state={state}
                handleAction={handleAction}
              />
            </ItemAction>
          <SeventDescription>
            <HtmlViewerWrapper style={{ margin: 0, padding: 0 }}>
                <HtmlViewer sx={{ margin: 0, padding: 0 }} html={(item?.description || '')} />
            </HtmlViewerWrapper>
          </SeventDescription>
          <div style={{ marginTop: '-24px', marginBottom: '16px' }}>
            {item.is_ticket_expiry ? (
              <div style={{ marginBottom: '4px', fontWeight: 'bold' }}>
                {i18n.formatMessage({ id: 'sevent_expired' })}
              </div>
            ) : (
                <>
                  {i18n.formatMessage({ id: 'sevent_left_quantity' })}:&nbsp;
                  {item.is_unlimited ? (
                    <span style={{ fontWeight: 'bold' }}>
                      {i18n.formatMessage({ id: 'sevent_unlimited' })}
                    </span>
                  ) : 
                  (
                    <span>
                      {item.remaining_qty > 0 ? (
                        <>
                          <span style={{ fontWeight: 'bold' }}>
                            {item.remaining_qty}
                          </span>
                        </>
                      ) : (
                        <>
                          <b>{i18n.formatMessage({ id: 'sevent_sold_out' })}</b>
                        </>
                      )}
                    </span>
                  )
                  }
                    {item.total_sales > 0 ? (
                      <>
                        ,&nbsp;
                        {i18n.formatMessage({ id: 'sevent_total_sales' })}:&nbsp;
                          <span style={{ fontWeight: 'bold' }}>
                            {item.total_sales}
                          </span>
                      </>
                    ) : null}
                </>
              )}
          </div>
          {isLogged ? (
              <>
              {canBuy ? (
                <div style={{ width: isMobile ? '100%' : '50%' }}>
                 <Buy
                    i18n={i18n}
                    item={item}
                    identity={identity}
                    actions={actions}
                    apiClient={apiClient}
                  />
                </div>
              ) : null}
            </>
           ) : (
            <div style={{ marginBottom: '8px' }}>
                <Button as={Link} href={`/login?returnUrl=/sevent/${item.sevent_id}`} 
                  variant='contained' color='primary'>
                {i18n.formatMessage({ id: 'sevent_buy_button' })}
              </Button>
            </div>
          )}
        </ItemTextStyled>
      </Grid>
    </Grid>
    </ItemViewBox>
    </ItemView>
  );
}

export default connectItemView(SeventTicketItemView, actionCreators, {});
