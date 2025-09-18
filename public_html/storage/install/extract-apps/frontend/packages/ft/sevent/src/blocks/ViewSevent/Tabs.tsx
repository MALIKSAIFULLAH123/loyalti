// SeventTabs.js

import React from 'react';
import { Tabs, Tab, styled } from '@mui/material';
import { useGlobal } from '@metafox/framework';

const Badge = styled('span')(({ theme }) => ({
    color: theme.palette.error.main
  }));

const useCustomTabs = item => {
  if (!item || !item.statistic) return [] ;

  const tabs = {
    campaign: {
      id: 'sevent_event_information',
      value: 'campaign',
      invisible: true
    },
    ticket: {
      id: 'tickets',
      value: 'ticket',
      total: item.statistic.total_ticket,
      invisible: item.statistic.total_ticket > 0 ? true : false
    },
    attending: {
      id: 'sevent_attending',
      value: 'attending',
      invisible: item.statistic.total_attending > 0 ? true : false,
      total: item.statistic.total_attending
    },
    interested: {
      id: 'sevent_interested_tab',
      value: 'interested',
      invisible: item.statistic.total_interested > 0 ? true : false,
      total: item.statistic.total_interested
    },
    comment: {
        id: 'sevent_comments',
        value: 'comment',
        invisible: true,
        total: item.statistic.total_comment
    },
    terms: {
        id: 'sevent_terms_and_services',
        value: 'terms',
        invisible: item.terms ? true : false
    }
    };

    return tabs;
  };

const SeventTabs = ({ tabRefs, value, setValue, item, terms, campaign, comments, ticket, attending, interested }) => {
  const {
    i18n,
    useIsMobile
  } = useGlobal();

  const isMobile = useIsMobile();
  const tabs = useCustomTabs(item);

  const handleChange = (event, newValue) => {
    setValue(newValue);
  };

  if (!item || !item.statistic) return null;

  return (
    <div ref={tabRefs}>
      {tabs[value] ? (
        <Tabs
          value={value}
          onChange={handleChange}
          variant={isMobile ? 'scrollable' : 'standard'} 
          scrollButtons={isMobile ? 'auto' : 'off'}
        >
          {Object.keys(tabs)
            .filter(x => tabs[x].invisible)
            .map(tab => (
              <Tab
                key={tabs[tab].id}
                disableRipple
                label={
                    <span>
                      {i18n.formatMessage({ id: tabs[tab].id })}{' '}
                      {tabs[tab].total ? (
                        <Badge>({tabs[tab].total})</Badge>
                      ) : null}
                    </span>
                  }
                value={tabs[tab].value}
                aria-label={tabs[tab].value}
              />
            ))}
        </Tabs>
      ) : null}
      {tabs[value]?.value === 'ticket' ? (
        <div style={{ minHeight: '100px' }}>
            {ticket}
        </div>
      ) : null}
      {tabs[value]?.value === 'attending' ? (
        <div style={{ minHeight: '100px' }} >
            {attending}
        </div>
      ) : null}
       {tabs[value]?.value === 'interested' ? (
        <div style={{ minHeight: '100px' }} >
            {interested}
        </div>
      ) : null}
      {tabs[value]?.value === 'terms' ? (
        <div style={{ minHeight: '100px' }}>
            {terms}
        </div>
      ) : null}
      {tabs[value]?.value === 'comment' ? (
        <div style={{ minHeight: '100px' }}>
            {comments}
        </div>
      ) : null}
      {tabs[value]?.value === 'campaign' ? (
        <div style={{ minHeight: '100px' }}>
             {campaign}
        </div>
      ) : null}
    </div>
  );
};

export default SeventTabs;
;
