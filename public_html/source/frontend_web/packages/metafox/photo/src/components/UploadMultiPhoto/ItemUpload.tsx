import {
  useGlobal,
  useResourceAction,
  useResourceForm
} from '@metafox/framework';
import { StyledIconButton, LineIcon } from '@metafox/ui';
import { compactUrl, isVideoType, isIOS } from '@metafox/utils';
import { Box, styled } from '@mui/material';
import clsx from 'clsx';
import React from 'react';
import useStyles from './styles';
import PreviewImageComponent from './PreviewImage';
import ItemPhotoMove from './ItemPhotoMove';
import { isEmpty, pick } from 'lodash';
import { APP_PHOTO, RESOURCE_PHOTO } from '@metafox/photo';

const APP_VIDEO = 'video';
const RESOURCE_VIDEO = 'video';

const ActionsWrapper = styled(Box, {
  name: 'MultipleUploadField',
  slot: 'ActionsWrapper'
})(({ theme }) => ({
  position: 'absolute',
  top: theme.spacing(1),
  right: theme.spacing(1)
}));
const OtherActionsWrapper = styled(Box, {
  name: 'MultipleUploadField',
  slot: 'OtherActionsWrapper'
})(({ theme }) => ({
  position: 'absolute',
  top: theme.spacing(1),
  left: theme.spacing(1)
}));

const ActionButton = styled(StyledIconButton, {
  name: 'MultipleUploadField',
  slot: 'ActionButton'
})(({ theme }) => ({
  fontSize: '14px',
  background: 'rgba(0,0,0,0.4) !important',
  color: theme.palette.common.white,
  '&:hover': {
    background: 'rgba(0,0,0,0.4) !important',
    color: theme.palette.common.white
  }
}));

const PreviewVideoWrapper = styled('div', {
  name: 'MultipleUploadField',
  slot: 'PreviewVideoWrapper'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  height: '100%'
}));

const MaskPlay = styled('div', {
  name: 'MultipleUploadField',
  slot: 'MaskPlay'
})(({ theme }) => ({
  position: 'absolute',
  width: theme.spacing(5),
  height: theme.spacing(5),
  color: '#fff',
  backgroundColor: 'rgba(0,0,0,0.4)',
  borderRadius: '50%',
  left: '50%',
  top: '50%',
  marginLeft: theme.spacing(-2.5),
  marginTop: theme.spacing(-2.5),
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  fontSize: theme.mixins.pxToRem(24)
}));

const PreviewVideo = styled('video', {
  name: 'MultipleUploadField',
  slot: 'PreviewVideo'
})({
  position: 'absolute',
  left: 0,
  top: 0,
  right: 0,
  bottom: 0,
  backgroundRepeat: 'no-repeat',
  backgroundPosition: 'center',
  maxWidth: '100%'
});

const ItemMediaContainer = styled(Box, {
  name: 'MultipleUploadField',
  slot: 'ItemMediaContainer'
})({
  position: 'relative',
  textAlign: 'center'
});

export default function ItemUpload(props) {
  const classes = useStyles();
  const { i18n, dialogBackend } = useGlobal();
  const {
    item,
    removeItem,
    aspectRatio,
    editItem,
    id,
    allowRemove = true,
    allowEdit = false,
    mappingEditPhotoFields = [],
    editPhotoAction
  } = props;

  const isVideo =
    isVideoType(item?.file?.type) || item?.module_name === 'video';
  const isVideoTag = !isIOS && isVideo && !item?.image;
  // eslint-disable-next-line react-hooks/rules-of-hooks
  const formSchema = useResourceForm(
    isVideo ? APP_VIDEO : APP_PHOTO,
    isVideo ? RESOURCE_VIDEO : RESOURCE_PHOTO,
    isVideo ? 'edit_selecting_video' : 'edit_selecting_photo'
  );

  const dataSource =
    useResourceAction(
      editPhotoAction?.module_name,
      editPhotoAction?.resource_name,
      editPhotoAction?.action_name
    ) || {};

  if (!item) return;

  const { extra } = item;
  // default rule: new file can edit and delete
  const { can_delete, can_edit } = extra || {
    can_edit: true,
    can_delete: true
  };

  // always can remove on new file
  const canDeleteItem = item?.uid ? true : can_delete && allowRemove;
  const canEditItem = item?.uid ? allowEdit : can_edit && allowEdit;

  const openEditItem = async () => {
    if (!canEditItem || (!dataSource && !formSchema)) return;

    const initValues = pick(item, mappingEditPhotoFields);
    const values = await dialogBackend.present({
      component: 'photo.dialog.quickEditPhotoFieldItem',
      props: {
        formSchema: isEmpty(dataSource) ? formSchema : undefined,
        dataSource: {
          ...dataSource,
          apiUrl: compactUrl(dataSource.apiUrl, item)
        },
        initialValues: {
          ...initValues,
          ...item?.extra_info
        }
      }
    });

    // cancel case
    if (!values) return;

    editItem(id, { extra_info: values });
  };

  return (
    <ItemPhotoMove {...props}>
      <ItemMediaContainer>
        <Box
          sx={{ position: 'relative' }}
          className={clsx(
            classes.itemMediaBackdrop,
            classes[`ratio${aspectRatio}`]
          )}
        >
          {isVideo ? (
            <PreviewVideoWrapper>
              {isVideoTag ? (
                <PreviewVideo
                  src={item?.source || item?.destination}
                  controls={false}
                ></PreviewVideo>
              ) : (
                <PreviewImageComponent item={item} />
              )}
              <MaskPlay>
                <LineIcon icon="ico-play" />
              </MaskPlay>
            </PreviewVideoWrapper>
          ) : (
            <PreviewImageComponent item={item} />
          )}
          <OtherActionsWrapper>
            {canEditItem && (formSchema || dataSource) ? (
              <ActionButton
                size="small"
                onClick={openEditItem}
                color="primary"
                icon="ico-pencil"
                title={i18n.formatMessage({ id: 'edit' })}
              />
            ) : null}
          </OtherActionsWrapper>
          <ActionsWrapper>
            {canDeleteItem ? (
              <ActionButton
                size="small"
                onClick={removeItem}
                color="primary"
                icon="ico-close"
                title={i18n.formatMessage({ id: 'remove' })}
              />
            ) : null}
          </ActionsWrapper>
        </Box>
      </ItemMediaContainer>
    </ItemPhotoMove>
  );
}
