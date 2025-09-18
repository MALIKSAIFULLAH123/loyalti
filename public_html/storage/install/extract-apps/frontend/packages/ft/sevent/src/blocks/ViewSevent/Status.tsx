import React from 'react';
import { useGlobal } from '@metafox/framework';

export default function Status({ item, text }) {

  const {
    i18n,
    useTheme
  } = useGlobal();
  let phrase;
  let color;
  let background;
  
  const theme = useTheme();

  if (item.status === 'draft') return null;
  
  switch (item.status) {
    case 'upcoming':
      phrase = 'sevent_upcoming';
      color = '#1a8cff';
      background = 'rgba(26,140,255,0.2)';
      break;
    case 'pending':
        phrase = 'pending_campaign';
        color = '#1a8cff';
        background = 'rgba(26,140,255,0.2)';
      break;
    case 'past':
      phrase = 'sevent_past';
      color = 'indianred';
      background = 'rgba(255, 26, 26, 0.2)';
      break;
    case 'ongoing':
      phrase = 'sevent_ongoing';
      color = '#64cf45';
      background = 'rgba(100,207,69,0.2)';
      break;
  }
  
  return (
    <>
    {text ? (
       <div style={{ background: color,
        color: theme.palette.primary.contrastText,
        padding: '4px' }}>
          {i18n.formatMessage({ id: phrase }) }
      </div>
    ) : (
      <div style={{
        display: 'flex',
        alignItems: 'center',
        verticalAlign: 'top',
        padding: '4px 16px 4px 12px',
        letterSpacing: '.15em',
        marginBottom: '4px',
        textTransform: 'uppercase',
        whiteSpace: 'nowrap',
        color,
        fontSize: '12px',
        background,
        borderLeft: `5px solid ${color}`
      }}>
      {i18n.formatMessage({ id: phrase }) }
      </div>
    )}
    </>
  );
};
