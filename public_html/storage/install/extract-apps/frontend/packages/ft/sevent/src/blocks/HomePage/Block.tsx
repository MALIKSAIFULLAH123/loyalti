/**
 * @type: block
 * name: sevent.block.HomePage
 * title: Browse Sevents Page
 * keywords: sevent
 * description: Display sevents
 */
import React from 'react';
import { useGlobal } from '@metafox/framework';
import SearchForm from './Form';
import Button from '@mui/material/Button';
import { Link } from 'react-router-dom';
import ArrowForwardIcon from '@mui/icons-material/ArrowForward';

export default function HomeSevents() {
  const { ListView, i18n, useTheme, useIsMobile } = useGlobal();
  const theme = useTheme();
  const isXlarge = window.innerWidth > 1919 ? true : false;
  const isMobile = useIsMobile();

  return (
    <div style={{ padding: isMobile ? '0 16px' : '0 0 32px 0' }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between',
        position: 'relative', zIndex: 1, flexDirection: isMobile ? 'column' : 'row', gap: isMobile ? '8px' : '' }}>
        <div>
          <h1 style={{ marginBottom: '8px' }}>
            {i18n.formatMessage({ id: 'sevents_home' })}
          </h1>
          <div style={{ fontSize: '14px', marginBottom: '8px', color: theme.palette.text.secondary }}>
            {i18n.formatMessage({ id: 'sevent_home_desc' })}
          </div>
        </div>
        <SearchForm/>
      </div>
      <ListView
        dataSource={{
          'apiParams': 'view=feature',
          'apiUrl': '/sevent',
          apiRules: {
            v: ['truthy', 'v'],
            view: ['feature'],
            sort1: [
              'includes',
              'sort',
              ['latest', 'most_viewed', 'most_liked', 'most_discussed']
            ],
            when1: ['truthy', 'v']
          }
        }}
        canLoadMore={false}
        maxPageNumber={1}
        clearDataOnUnMount
        numberOfItemsPerPage={isXlarge ? 4 : 6}
        gridLayout='Sevent Lists'
        blockLayout='Main Listings'
        itemView='sevent.itemView.mainCard'
        emptyPage='core.block.no_item_with_icon'
        emptyPageProps={{
          description: 'no_featured_sevent_found',
          image: 'ico-calendar-o'
        }}
      />
      <div style={{ margin: '32px 0 0', textAlign: 'center' }}>
          <Button component={Link} endIcon={<ArrowForwardIcon/>}
              to="/sevent/all" variant="contained" size="large">
                {i18n.formatMessage({ id: 'sevent_explore_all_events' })}
            </Button>
      </div>
       <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <div>
          <h1 style={{ marginBottom: '8px', marginTop: '32px' }}>
            {i18n.formatMessage({ id: 'sevent_browse_by_category' })}
          </h1>
          <div style={{ fontSize: '14px', marginBottom: '32px', color: theme.palette.text.secondary }}>
            {i18n.formatMessage({ id: 'sevent_home_category_desc' })}
          </div>
        </div>
      </div>
       <ListView
          dataSource={{
            'apiUrl': '/sevent/getCategories',
            apiRules: {
              v1: ['truthy', 'v'],
              view: ['sponsor'],
              sort1: [
                'includes',
                'sort',
                ['latest', 'most_viewed', 'most_liked', 'most_discussed']
              ],
              when1: ['truthy', 'v']
            }
          }}
          canLoadMore={false}
          maxPageNumber={1}
          numberOfItemsPerPage={30}
          gridLayout='Sevent Lists'
          itemView='sevent.itemView.topic'
        />
    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
      <div>
        <h1 style={{ marginBottom: '8px', marginTop: isMobile ? '32px' : '64px' }}>
          {i18n.formatMessage({ id: 'sponsored_sevent' })}
        </h1>
        <div style={{ fontSize: '14px', marginBottom: '16px', color: theme.palette.text.secondary }}>
          {i18n.formatMessage({ id: 'sponsored_sevent_desc' })}
        </div>
      </div>
    </div>
    <ListView
        dataSource={{
          'apiParams': 'view=sponsor',
          'apiUrl': '/sevent',
          apiRules: {
            v: ['truthy', 'v'],
            view: ['sponsor'],
            sort1: [
              'includes',
              'sort',
              ['latest', 'most_viewed', 'most_liked', 'most_discussed']
            ],
            when1: ['truthy', 'v']
          }
        }}
        canLoadMore={false}
        maxPageNumber={1}
        clearDataOnUnMount
        numberOfItemsPerPage={isXlarge ? 4 : 3}
        gridLayout='Sevent Lists'
        blockLayout='Main Listings'
        itemView='sevent.itemView.mainCard'
        emptyPage='core.block.no_item_with_icon'
        emptyPageProps={{
          description: 'no_sevent_found',
          image: 'ico-calendar-o'
        }}
      />
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
        <div>
          <h1 style={{ marginBottom: '8px', marginTop: isMobile ? '32px' : '64px' }}>
            {i18n.formatMessage({ id: 'sevent_near_you' })}
            <Button component={Link} style={{ marginLeft: '16px' }} 
              to="/sevent/all?distance=300" variant="contained" size="small">
                {i18n.formatMessage({ id: 'sevent_home_view_all' })}
            </Button>
          </h1>
          <div style={{ fontSize: '14px', marginBottom: '16px', color: theme.palette.text.secondary }}>
            {i18n.formatMessage({ id: 'sevent_almost_there_desc' })}
          </div>
        </div>
      </div>
      <ListView
          dataSource={{
            'apiParams': 'distance=300',
            'apiUrl': '/sevent',
            apiRules: {
              v: ['truthy', 'v'],
              distance: ['truthy', 'distance'],
              view1: ['almost_there'],
              sort: [
                'includes',
                'sort',
                ['almost_there']
              ],
              when1: ['truthy', 'v']
            }
          }}
          canLoadMore={false}
          maxPageNumber={1}
          clearDataOnUnMount
          numberOfItemsPerPage={isXlarge ? 8 : 6}
          gridLayout='Sevent Lists'
          blockLayout='Main Listings'
          itemView='sevent.itemView.mainCard'
          emptyPage='core.block.no_item_with_icon'
          emptyPageProps={{
            description: 'no_sevent_found',
            image: 'ico-calendar-o'
          }}
        />
      </div>
   );
}
