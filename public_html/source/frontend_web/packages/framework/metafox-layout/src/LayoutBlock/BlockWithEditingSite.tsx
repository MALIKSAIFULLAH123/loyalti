/**
 * @type: ui
 * name: layout.blockWithEditingSite
 */
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { IconButton, styled, Tooltip } from '@mui/material';
import * as React from 'react';
import { BlockControlProps, EditMode } from '../types';
import { isString } from 'lodash';

const Root = styled('div', {
  name: 'EditBlock',
  slot: 'root'
})({});

const Controller = styled('div', {
  name: 'EditBlock',
  slot: 'controller',
  shouldForwardProp: prop => prop !== 'dragging' && prop !== 'canDrag'
})<{
  dragging?: boolean;
  canDrag?: boolean;
  disabled?: boolean;
}>(({ theme, disabled }) => ({
  minHeight: '40px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  padding: theme.spacing(0.5, 1),
  backgroundColor: theme.palette.background.paper,
  margin: theme.spacing(1, 0),
  borderRadius: theme.shape.borderRadius / 2,
  boxShadow: theme.shadows[1],
  cursor: 'pointer',
  border: '2px dashed transparent',
  boxSizing: 'border-box',
  ...(disabled
    ? {
        background: 'rgba(0,0,0,0.1)',
        cursor: 'default'
      }
    : {
        '&:hover': {
          opacity: 0.8
        }
      })
}));

const Actions = styled('div', {
  name: 'EditBlock',
  slot: 'actions'
})(({ theme }) => ({
  '& button': {
    marginLeft: theme.spacing(0.5)
  }
}));
const Content = styled('div', {
  name: 'EditBlock',
  slot: 'content'
})(({ theme }) => ({
  paddingLeft: theme.spacing(2)
}));

export default function EditLayoutBlock(props: BlockControlProps) {
  const {
    title: _title,
    layoutEditMode,
    blockName,
    blockOrigin,
    elements
  } = props;
  // setup editing editing page
  const { layoutBackend, jsxBackend, dispatch, i18n } = useGlobal();

  const { title, container } = React.useMemo(
    () =>
      layoutBackend.getBlockView(blockName) || {
        title: blockName,
        container: false
      },
    [blockName, layoutBackend]
  );
  const i18nTitle =
    _title && isString(_title) ? i18n.formatMessage({ id: _title }) : '';
  const displayName = i18nTitle || title || blockName;
  const canEdit =
    (blockOrigin === 'layout' && layoutEditMode === EditMode.editLayout) ||
    (blockOrigin === 'page' && layoutEditMode === EditMode.editPageContent) ||
    (blockOrigin === 'site' && layoutEditMode === EditMode.editSiteContent);

  const handleClick = (type: string) => dispatch({ type, payload: props });

  return (
    <Root>
      <Controller>
        {displayName}
        <Actions>
          {container ? (
            <Tooltip title={i18n.formatMessage({ id: 'add_layout_block' })}>
              <IconButton
                onClick={() => handleClick('@layout/createBlock')}
                size="smaller"
              >
                <LineIcon icon={'ico-plus'} />
              </IconButton>
            </Tooltip>
          ) : null}
          {canEdit ? (
            <Tooltip title={i18n.formatMessage({ id: 'edit_layout_block' })}>
              <IconButton
                onClick={() => handleClick('@layout/editBlock')}
                size="smaller"
              >
                <LineIcon icon={'ico-pencil'} />
              </IconButton>
            </Tooltip>
          ) : null}
          {canEdit ? (
            <Tooltip title={i18n.formatMessage({ id: 'delete_layout_block' })}>
              <IconButton
                onClick={() => handleClick('@layout/deleteBlock')}
                size="smaller"
              >
                <LineIcon icon={'ico-trash'} />
              </IconButton>
            </Tooltip>
          ) : null}
        </Actions>
      </Controller>
      {container ? <Content>{jsxBackend.render(elements)}</Content> : null}
    </Root>
  );
}
