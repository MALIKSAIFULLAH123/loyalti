/**
 * @type: ui
 * name: layout.blockWithEditingPage
 */
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, IconButton, styled, Tooltip } from '@mui/material';
import * as React from 'react';
import { useDrag } from 'react-dnd';
import { getEmptyImage } from 'react-dnd-html5-backend';
import { TYPE_BLOCK } from '../constants';
import { BlockControlProps, EditMode } from '../types';
import { isString } from 'lodash';

const DragLayer = styled(Box, {
  name: 'BlockEditingDragLayer',
  slot: 'Block'
})<{ currentOffset: any; width?: number; mouseOffset: any }>(
  ({ theme, currentOffset, mouseOffset, width }) => ({
    backgroundColor: theme.palette.background.paper,
    display: 'flex',
    alignItems: 'center',
    padding: '4px 8px',
    margin: '8px 0',
    borderRadius: theme.shape.borderRadius,
    minWidth: '300px',
    minHeight: '40px',
    cursor: 'move',
    border: '2px dashed transparent',
    boxSizing: 'border-box',
    opacity: 0.8,
    marginLeft:
      mouseOffset && currentOffset
        ? `${mouseOffset.x - currentOffset.x - (width ? width / 2 : 0)}px`
        : 0
  })
);

const DraggingComponent = ({ item }) => {
  return <DragLayer>{item.title}</DragLayer>;
};

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
}>(({ theme, canDrag, dragging, disabled }) => ({
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
  '&[draggable="true"]': {
    cursor: canDrag ? 'move' : 'default'
  },
  ...(disabled
    ? {
        background: 'rgba(0,0,0,0.1)',
        cursor: 'default'
      }
    : {
        '&:hover': {
          opacity: 0.8
        }
      }),
  ...(dragging && {
    opacity: 0,
    height: 0,
    margin: 0,
    padding: 0,
    '&:hover': {
      opacity: 0
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
    blockId,
    title: _title,
    layoutEditMode,
    blockDisabled,
    blockName,
    blockOrigin,
    slotName,
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

  const canToggle =
    blockOrigin !== 'page' && layoutEditMode === EditMode.editPageContent;

  React.useEffect(() => {
    preview(getEmptyImage(), { captureDraggingState: false });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const [collectedProps, drag, preview] = useDrag({
    type: TYPE_BLOCK,
    item: {
      type: TYPE_BLOCK,
      blockId,
      blockName,
      slotName,
      draggingComponent: DraggingComponent,
      title: displayName
    },
    canDrag: canEdit,
    collect: monitor => ({
      isDragging: !!monitor.isDragging()
    })
  });

  const handleClick = (type: string) => dispatch({ type, payload: props });

  return (
    <Root>
      <Controller
        canDrag={canEdit}
        ref={drag}
        dragging={collectedProps.isDragging}
        disabled={blockDisabled && layoutEditMode === EditMode.editPageContent}
      >
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
          {canToggle ? (
            <Tooltip
              title={i18n.formatMessage({
                id: blockDisabled
                  ? 'layout_enable_layout_block'
                  : 'layout_disable_layout_block'
              })}
            >
              <IconButton
                onClick={() => handleClick('@layout/toggleBlock')}
                size="smaller"
              >
                <LineIcon icon={'ico-eye-off'} />
              </IconButton>
            </Tooltip>
          ) : null}
        </Actions>
      </Controller>
      {container ? <Content>{jsxBackend.render(elements)}</Content> : null}
    </Root>
  );
}
