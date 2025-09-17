/**
 * @type: ui
 * name: layout.containerWithEditingTemplate
 */
import { useGlobal } from '@metafox/framework';
import AddSlotIcon from '@mui/icons-material/AddLinkOutlined';
import DeleteContainerIcon from '@mui/icons-material/DeleteSweepOutlined';
import EditIcon from '@mui/icons-material/ModeEditOutlineOutlined';
import AddContainerIcon from '@mui/icons-material/PlaylistAddOutlined';
import { Grid, IconButton, Tooltip } from '@mui/material';
import { styled } from '@mui/material/styles';
import * as React from 'react';
import { LayoutContainerProps } from '../types';
import Container from './Container';

type ToolbarProps = {
  containerName: string;
  templateName: string;
  elementPath: string;
};

const StyledToolbar = styled('div')(({ theme }) => ({
  textAlign: 'right',
  padding: theme.spacing(1),
  '&:hover': {
    opacity: 1
  }
}));

const StyledIconButton = styled(IconButton)(({ theme }) => ({
  border: '1px solid transparent',
  borderColor: theme.palette.border.secondary,
  with: 32,
  height: 32,
  // backgroundColor: theme.palette.background.paper,
  marginLeft: 4,
  color: theme.palette.primary.light
}));

const Toolbar = (props: ToolbarProps) => {
  const { i18n, dispatch, useLayout } = useGlobal();
  const { pageName, pageSize } = useLayout();
  const { containerName, templateName } = props;

  const handle = (type: string) =>
    dispatch({
      type,
      payload: props
    });

  const canDelete = !['main', 'header', 'footer'].includes(containerName);

  return (
    <StyledToolbar>
      layout: <i>{templateName}</i>, container: <i>{containerName}</i>, page:
      <i>{pageName}</i>, screenSize: <i>{pageSize}</i>
      <StyledIconButton onClick={() => handle('@layout/editContainer')}>
        <Tooltip title={i18n.formatMessage({ id: 'edit_layout_container' })}>
          <EditIcon />
        </Tooltip>
      </StyledIconButton>
      <StyledIconButton onClick={() => handle('@layout/createSlot')}>
        <Tooltip title={i18n.formatMessage({ id: 'create_layout_slot' })}>
          <AddSlotIcon />
        </Tooltip>
      </StyledIconButton>
      <StyledIconButton onClick={() => handle('@layout/createContainer')}>
        <Tooltip title={i18n.formatMessage({ id: 'create_layout_container' })}>
          <AddContainerIcon />
        </Tooltip>
      </StyledIconButton>
      {canDelete ? (
        <StyledIconButton onClick={() => handle('@layout/deleteContainer')}>
          <Tooltip
            title={i18n.formatMessage({ id: 'delete_layout_container' })}
          >
            <DeleteContainerIcon />
          </Tooltip>
        </StyledIconButton>
      ) : null}
    </StyledToolbar>
  );
};

export default function ContainerWithEditingTemplate({
  containerName,
  elementPath,
  layoutEditMode,
  templateName,
  elements,
  master,
  rootStyle,
  wrap = 'nowrap'
}: LayoutContainerProps & { children: React.ReactNode }) {
  // how to edit section with item
  const { jsxBackend } = useGlobal();

  return (
    <>
      <Toolbar
        containerName={containerName}
        templateName={templateName}
        elementPath={elementPath}
      />
      <Container master={master} editMode={layoutEditMode} {...rootStyle}>
        <Grid container columnSpacing={0} rowSpacing={0} wrap={wrap}>
          {jsxBackend.render(elements)}
        </Grid>
      </Container>
    </>
  );
}
