/**
 * @type: itemView
 * name: sevent.itemView.userTicketCard
 */
import actionCreators from '@ft/sevent/actions/seventItemActions';
// types
import { SeventItemProps as ItemProps } from '@ft/sevent/types';
import { connectItemView, useGlobal, Link } from '@metafox/framework';
import Grid from '@mui/material/Grid';
// components
import {
  FormatDate,
  ItemView,
  DotSeparator,
  ItemText,
  ItemTitle
} from '@metafox/ui';
import { styled, Box, Button, Dialog, DialogTitle, DialogContent } from '@mui/material';
import React, { useState } from 'react';

const QRCodeModal = ({ open, onClose, qrImage, i18n }) => (
  <Dialog open={open} onClose={onClose} maxWidth="sm" fullWidth>
    <DialogTitle>{i18n.formatMessage({ id: 'sevent_qr_code' })}</DialogTitle>
    <DialogContent>
      <Box display="flex" justifyContent="center" alignItems="center" height="100%">
        <img src={qrImage} alt={i18n.formatMessage({ id: 'sevent_qr_code' })} style={{ width: '300px' }} />
      </Box>
    </DialogContent>
  </Dialog>
);

const name = 'SeventTicketItemView';
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
  item,
  identity,
  wrapProps,
  wrapAs
}: ItemProps) {
  const {
    useIsMobile,
    useTheme,
    useGetItem
  } = useGlobal();
  const isMobile = useIsMobile();
  const theme = useTheme();
  const { i18n, dispatch } = useGlobal();
  const [isQRCodeOpen, setQRCodeOpen] = useState(false);
  const event = useGetItem(item.event);

  if (!event || !item || !item.ticket)
    return;

  const openQRCode = () => setQRCodeOpen(true);
  const closeQRCode = () => setQRCodeOpen(false);

  const downloadPdf = () => {
    dispatch({ type: 'sevent/downloadItem', payload: { identity } });
  };

  const ItemViewBox = styled('div', { name: 'ItemViewBox', slot: 'ItemViewBox' })(
      ({ theme }) => ({
        width: '100%',
        borderBottom: theme.mixins.border('secondary'),
        marginBottom: theme.spacing(1),
        paddingBottom: theme.spacing(1),
        justifyContent: 'space-between',
        backgroundColor: theme.mixins.backgroundColor('paper'),
        padding: '16px',
        borderRadius: '8px',
        [theme.breakpoints.down('sm')]: {
          flexDirection: 'column-reverse',
          marginBottom: theme.spacing(1),
          paddingBottom: theme.spacing(1)
        }
      })
    );
  const ItemTextStyled = styled(ItemText, { name: 'ItemTextStyled', slot: 'ItemViewBox' })(
      ({ theme }) => ({
        display: 'flex',
        flexDirection: 'column',
        gap: '6px',
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
      <Grid container spacing={1}>
        <Grid item xs={2} style={{ textAlign: 'center' }}>
          <i className='ico ico-ticket-o' style={{ color: theme.palette.primary.main, fontSize: 
            isMobile ? '30px' : '50px' }}/>
        </Grid>
      <Grid item xs={10}>
        <ItemTextStyled>
          <Box display='flex'>
            <ItemTitleStyled lines={3} isMobile={isMobile}>
              <Link to={event.link}>
                <span style={{ fontWeight: 'bold' }}>
                  {event.title} 
                </span>
              </Link>
            </ItemTitleStyled>
          </Box>
          <div style={{ color: theme.palette.text.secondary, fontSize: '12px' }}>
            <DotSeparator>
              <span>
                {i18n.formatMessage({ id: 'sevent_ticket_number' })}:&nbsp;
                <span style={{ color: 'indianred' }}>#{item.number}</span>
              </span>
              <span>
                {i18n.formatMessage({ id: 'sevent_event_date' })}:&nbsp;
                <b>
                  <FormatDate
                    data-testid="startDate"
                    value={item.event.start_date}
                    format="lll"
                  />
                </b>
              </span>
            </DotSeparator>
          </div>
          <div style={{ color: theme.palette.text.secondary, fontSize: '12px' }}>
            <DotSeparator>
              <span>
                {i18n.formatMessage({ id: 'sevent_ticket' })}:&nbsp;
                {item.ticket.title}
              </span>
              <span>
                {i18n.formatMessage({ id: 'sevent_paid_at' })}:&nbsp;
                <FormatDate
                    data-testid="paidAt"
                    value={item.paid_at}
                    format="ll"
                  />
              </span>
            </DotSeparator>
          </div>
          <Box display='flex' gap='16px' style={{ marginTop: '8px' }}>
            {item.qr ? (
            <Button
              variant="outlined"
              size='small'
              style={theme.palette.mode !== 'light' ? { color: '#eee' } : null}
              color="secondary"
              onClick={openQRCode}
              startIcon={<i className='ico ico-qrcode' />}
            >
              {i18n.formatMessage({ id: 'sevent_qr_code' })}
            </Button>
            ) : null}
            
            <Button
              variant="outlined"
              style={theme.palette.mode !== 'light' ? { color: '#eee' } : null}
              size="small"
              color="secondary"
              startIcon={<i className="ico ico-download" />}
              onClick={downloadPdf}
            >
            {i18n.formatMessage({ id: 'sevent_download_ticket' })}
          </Button>
          </Box>
        </ItemTextStyled>
      </Grid>
    </Grid>
    </ItemViewBox>
    <QRCodeModal
        i18n={i18n}
        open={isQRCodeOpen}
        onClose={closeQRCode}
        qrImage={item.qr}
      />
    </ItemView>
  );
}

export default connectItemView(SeventTicketItemView, actionCreators, {});
