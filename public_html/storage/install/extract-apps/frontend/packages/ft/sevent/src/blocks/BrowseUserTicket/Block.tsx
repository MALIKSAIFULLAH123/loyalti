/**
 * @type: block
 * name: sevent.block.BrowseUserTicket
 * title: Browse Sevents
 * keywords: sevent
 * description: Display sevents
 */
import { useGlobal } from '@metafox/framework';
import React from 'react';
import { styled, Box } from '@mui/material';

export default function BrowseUserTicket() {
  const { ListView, useIsMobile, i18n } = useGlobal();
  const isMobile = useIsMobile();
  const isXlarge = window.innerWidth > 1419 ? true : false;

  const dataSource = {
    apiUrl: '/sevent/myTickets'
  };

  const HeaderTitle = styled(Box, { name: 'HeaderTitle' })(({ theme }) => ({
    width: '100%',
    marginTop: '16px',
    [theme.breakpoints.down('sm')]: {
      alignItems: 'flex-start',
      height: 'initial'
    }
  }));

  return (
    <div style={{ marginBottom: '32px' }}>
      <Box>
        <HeaderTitle display='flex' flexDirection='column'
          style={isMobile ? { padding: '16px 16px 0' } : null} 
          justifyContent='space-between' position='relative' zIndex={1}>
          <div style={{ height: '26px' }}>
          <h2 style={{ margin: 0, padding: 0 }}>
            {i18n.formatMessage({ id: 'sevent_my_tickets' })}
          </h2>
          </div>
        </HeaderTitle>
      </Box>
      <Box style={isMobile ? { padding: '0 16px' } : null} >
        <ListView
          acceptQuerySearch
          dataSource={dataSource}
          canLoadMore={true}
          clearDataOnUnMount
          maxPageNumber={120}
          numberOfItemsPerPage={isXlarge ? 12 : 8}
          gridLayout='Ticket Lists'
          blockLayout='Main Listings'
          itemView='sevent.itemView.userTicketCard'
          emptyPage='core.block.no_item_with_icon'
          emptyPageProps={{
            description: 'no_tickets_found',
            image: 'ico-ticket-o'
          }}
        />
      </Box>
    </div>
  );
}