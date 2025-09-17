import { useGlobal } from '@metafox/framework';
import { LineIcon, MenuItemProps } from '@metafox/ui';
import { Accordion, AccordionDetails, AccordionSummary } from '@mui/material';
import { GridExpandMoreIcon } from '@mui/x-data-grid';
import React from 'react';

export default function MenuItemAccordion(props: MenuItemProps) {
  const { jsxBackend } = useGlobal();
  const { label, icon, content } = props;

  return (
    <Accordion disableGutters sx={{ boxShadow: 'none' }}>
      <AccordionSummary expandIcon={<GridExpandMoreIcon />}>
        <LineIcon icon={icon} sx={{ fontSize: '13px', minWidth: '24px' }} />
        {label}
      </AccordionSummary>
      <AccordionDetails sx={{ p: 0, m: 0 }}>
        {jsxBackend.render(content)}
      </AccordionDetails>
    </Accordion>
  );
}
