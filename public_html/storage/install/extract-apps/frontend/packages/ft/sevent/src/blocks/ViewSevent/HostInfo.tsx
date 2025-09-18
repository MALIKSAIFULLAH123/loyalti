import { useGlobal, Link } from '@metafox/framework';
import React from 'react';
import { Box, Button } from '@mui/material';
import FacebookLinkIcon from '@mui/icons-material/Facebook';
import LanguageLinkIcon from '@mui/icons-material/Language';

export default function HostInfo({
  item
}) {
  const { i18n, useTheme, useIsMobile } = useGlobal();

  const isMobile = useIsMobile();
  const theme = useTheme();

  if (!item) return null;

  return (
   <div>
      <h3>
        {i18n.formatMessage({ id: 'sevent_organized_by' })}
      </h3>
      <div style={{ background: theme.palette.action.hover, borderRadius: '8px', padding: '16px' }}>
          <Box display='flex' alignItems='flex-start' width='100%'
          flexDirection={!isMobile ? 'row' : 'column'} justifyContent='space-between' 
          gap={!isMobile ? '12px' : '24px'}>
              <Box display='flex' gap='12px'>
                {item.host ? (
                  <div style={{ width: '56px', height: '56px', borderRadius: '50%', overflow: 'hidden' }}>
                      <img src={item.host} style={{ width: '100%', height: '100%', objectFit: 'cover' }} />
                  </div>
                ) : null}
                 <div>
                  <div style={{ fontWeight: 'bold', fontSize: '16px', marginBottom: '8px' }}>
                      {item.host_title}
                  </div>
                  <div style={{ marginTop: '12px' }}> 
                    <Button as={Link} href={`mailto:${item.host_contact}`}
                            variant='contained' color='primary' style={{ height: '100%' }}>
                      {i18n.formatMessage({ id: 'sevent_contact' })}
                    </Button>
                  </div>
                </div>
              </Box>
              {item.host_contact ? (
                  <Box display='flex' alignItems='center' gap='8px'>
                    {item.host_website ? (
                      <a style={{ color: theme.palette.text.secondary }} href={item.host_website} target='_blank'>
                        <LanguageLinkIcon/>
                    </a>
                    ) : null}
                    {item.host_facebook ? (
                      <a style={{ color: theme.palette.text.secondary }} href={item.host_facebook} target='_blank'>
                        <FacebookLinkIcon/>
                    </a>
                    ) : null}
                  </Box>
              ) : null}
          </Box>
          {item.host_description ? (
            <div style={{ color: theme.palette.text.secondary, marginTop: '16px', fontSize: '13px' }}>
              {item.host_description}
            </div>
          ) : null}
      </div>
   </div>
  );
}
