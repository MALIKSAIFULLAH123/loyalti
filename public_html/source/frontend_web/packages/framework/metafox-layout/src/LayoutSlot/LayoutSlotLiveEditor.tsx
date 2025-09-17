/**
 * @type: ui
 * name: layout.slotLiveEditor
 */
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, Button, ButtonGroup, IconButton } from '@mui/material';
import { camelCase } from 'lodash';
import React from 'react';
import { EditSlotParams, PageSize } from '../types';

export default function LayoutSlotLiveEditor(
  props: {
    pageName?: string;
    pageSize?: PageSize;
  } & EditSlotParams
) {
  const { slotName } = props;
  const { i18n, dispatch } = useGlobal();

  const handleClick = (type: string) => dispatch({ type, payload: props });

  return (
    <Box
      title="configure this area"
      data-testid={camelCase(`LayoutSlot_${slotName}`)}
      sx={{
        position: 'absolute',
        right: 0,
        display: 'flex',
        flexDirection: 'row',
        justifyContent: 'flex-end',
        fontSize: '0.75rem',
        zIndex: 100
      }}
    >
      <ButtonGroup size="small">
        <Button disabled size="smallest">
          {slotName}
        </Button>
        <IconButton
          size="smallest"
          onClick={() => handleClick('@layout/editSlot')}
          title={i18n.formatMessage({ id: 'edit_layout_slot' })}
        >
          <LineIcon icon="ico-pencilline-o" />
        </IconButton>
        <IconButton
          size="smallest"
          onClick={() => handleClick('@layout/createBlock')}
          title={i18n.formatMessage({ id: 'create_layout_block' })}
        >
          <LineIcon icon="ico-list-plus" />
        </IconButton>
      </ButtonGroup>
    </Box>
  );
}
