/**
 * @type: block
 * name: sevent.block.BrowseSevents
 * title: Browse Sevents
 * keywords: sevent
 * description: Display sevents
 */
import { createBlock, ListViewBlockProps, useGlobal } from '@metafox/framework';
import React from 'react';
import { styled, Box } from '@mui/material';
import Title from './Title';

export default function BrowseSevents() {
  const { ListView, usePageParams, compactUrl, useIsMobile } = useGlobal();
  const params = usePageParams();
  const isMobile = useIsMobile();
  const sort = params['sort'] ? params['sort'] : 'latest';
  const distance = params['distance'] ? params['distance'] : '';
  const countryIso = params['country_iso'] ? params['country_iso'] : '';
  const view = params['view'] ? params['view'] : 'all';
  const sview = params['sview'] ? params['sview'] : '';
  const categoryId = params['category_id'] ? params['category_id'] : '';
  const when = params['when'] ? params['when'] : 'all';
  const isXlarge = window.innerWidth > 1419 ? true : false;
  const apiParams = 'distance=' + distance + '&country_iso=' + countryIso +  
    '&sview=' + sview + '&view=' + view + '&sort=' + sort + '&when=' + when + '&category_id=' + categoryId;
  const dataSource = {
    apiUrl: compactUrl('/sevent', params),
    apiParams
  };
  const SearchBlock = createBlock<ListViewBlockProps>({
    name: 'Search Block',
    extendBlock: 'core.block.sidebarQuickFilter',
    overrides: {
      title: '',
      blockLayout: 'sidebar app filter'
    }
  });
  const FormBlock = styled('div', { name: 'FormBlock' })(({ theme }) => ({
    display: 'flex',
    flexDirection: 'column',
    flex: 1,
    marginRight: '-24px',
    width:'100%',
    [theme.breakpoints.down('sm')]: {
      flexDirection: 'column',
      width: '100%'
    }
  }));  

  const HeaderTitle = styled(Box, { name: 'HeaderTitle' })(({ theme }) => ({
    width: '100%',
    marginTop: '16px',
    [theme.breakpoints.down('sm')]: {
      alignItems: 'flex-start',
      height: 'initial'
    }
  }));

  const SearchBlockBox = styled(Box, { name: 'SearchBlockBox' })(({ theme }) => ({
    flexBasis: '100%', 
    marginLeft: '-16px',
    [theme.breakpoints.down('sm')]: {
      marginLeft: '-16px',
      marginRight: '-24px'
    }
  }));

  return (
    <div style={{ marginBottom: '32px' }}>
      <Box>
        <HeaderTitle display='flex' flexDirection='column'
        style={isMobile ? { padding: '16px 16px 0' } : null} 
          justifyContent='space-between' position='relative' zIndex={1}>
          <div style={{ height: '26px' }}>
            <Title/>
          </div>
          {view == 'all' && (
            <FormBlock>
              <SearchBlockBox> 
                <SearchBlock />
              </SearchBlockBox>
            </FormBlock>
            )}
        </HeaderTitle>
      </Box>
      <Box style={isMobile ? { padding: '0 16px' } : null} >
        <ListView
          acceptQuerySearch
          dataSource={dataSource}
          canLoadMore={true}
          contentType='sevent'
          resourceName='sevent'
          clearDataOnUnMount
          maxPageNumber={120}
          numberOfItemsPerPage={isXlarge ? 16 : 9}
          gridLayout='Sevent Lists'
          blockLayout='Main Listings'
          itemView='sevent.itemView.mainCard'
          emptyPage='core.block.no_item_with_icon'
          emptyPageProps={{
            description: 'no_sevent_found',
            image: 'ico-calendar-o'
          }}
        />
      </Box>
    </div>
  );
}