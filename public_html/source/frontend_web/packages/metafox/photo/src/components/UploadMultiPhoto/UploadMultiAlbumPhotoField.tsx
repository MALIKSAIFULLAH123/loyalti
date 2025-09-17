/**
 * @type: formElement
 * name: form.element.UploadMultiAlbumItem
 */

import { FormFieldProps } from '@metafox/form';
import ErrorMessage from '@metafox/form-elements/ErrorMessage';
import { useGlobal, useIsMobile } from '@metafox/framework';
import { DropFileBox, LineIcon } from '@metafox/ui';
import { Button, FormControl, Grid, Typography, Box } from '@mui/material';
import { styled } from '@mui/material/styles';
import { useField } from 'formik';
import { camelCase, isEmpty, isEqual } from 'lodash';
import React, { useCallback } from 'react';
import useCheckMediaFileSize from '@metafox/photo/hooks/useCheckMediaFileSize';
import ItemUpload from './ItemUpload';
import { produce } from 'immer';

const Control = styled('div', {
  name: 'MultipleUploadField',
  slot: 'Control',
  shouldForwardProp: prop => prop !== 'haveError'
})<{ haveError: boolean }>(({ theme, haveError }) => ({
  height: 120,
  borderRadius: 4,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  border: theme.mixins.border('secondary'),
  borderColor:
    theme.palette.mode === 'light' ? '#0000003b' : 'rgba(255, 255, 255, 0.23)',
  '& .ico-photos-plus-o': {
    fontSize: `${theme.mixins.pxToRem(15)} !important`
  },
  '&:hover': {
    borderColor: theme.palette.mode === 'light' ? '#000' : '#fff'
  },
  '& button': {
    [theme.breakpoints.down('sm')]: {
      flexDirection: 'column',
      height: 'auto',
      maxWidth: 'calc(100% - 40px)',
      padding: '10px'
    }
  },
  ...(haveError && {
    borderColor: `${theme.palette.error.main} !important`
  })
}));

export interface DropButtonProps {
  isOver?: boolean;
}

const DropButton = styled(Button, {
  name: 'DropButton',
  slot: 'DropButton'
})<DropButtonProps>(({ theme, isOver }) => ({
  ...(isOver && {
    backgroundColor: theme.palette.action.hover
  })
}));

const AddMoreBtnWrapper = styled('div', {
  name: 'MultipleUploadField',
  slot: 'AddMoreBtnWrapper'
})<{ disabled: boolean }>(({ theme, disabled }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  borderColor: disabled
    ? theme.palette.text.disabled
    : theme.palette.primary.main,
  borderStyle: 'solid',
  borderWidth: 1,
  borderRadius: theme.shape.borderRadius,
  height: '100%'
}));

const LabelButton = styled('span', {
  name: 'LabelButton',
  slot: 'LabelButton'
})(({ theme }) => ({
  fontSize: theme.mixins.pxToRem(15)
}));

export default function UploadMultiPhotoField({
  name,
  formik,
  config,
  disabled: forceDisabled
}: FormFieldProps) {
  const {
    upload_url,
    max_upload_filesize: maxSizeLimit,
    description,
    allowRemoveItems = true,
    allowAddItems = true,
    aspectRatio = '169',
    allowDrop = false,
    allowEditPhoto: allowEdit = false,
    mappingEditPhotoFields = []
  } = config;
  const { i18n, dialogBackend, jsxBackend, usePageParams } = useGlobal();
  const [, , { setValue: setFileItemTypeValue }] = useField('fileItemType');
  const [fieldItemPhoto, meta, { setValue, setTouched }] = useField(
    name ?? 'ItemPhotoField'
  );
  const isMobile = useIsMobile();
  const [progress, setProgress] = React.useState({});
  const { add_photo } = usePageParams();

  const onUploadProgress = (event, idFile) => {
    const progress = Math.round((event.loaded * 100) / event.total);

    setProgress(prev => ({ ...prev, [idFile]: progress }));
  };

  const [validFileItems, setValidFileItems, handleProcessFiles] =
    useCheckMediaFileSize({
      initialValues: fieldItemPhoto.value || [],
      upload_url,
      maxSizeLimit
    });

  const placeholder = config.placeholder || 'add_photo';

  const removeFile = (uid: string) =>
    setValidFileItems(prev => prev.filter(item => item.uid !== uid));

  const removeOnStatusFile = (id: string | number) => {
    setValidFileItems(prev =>
      prev.map(item => {
        if (item?.id === id) {
          return { ...item, status: 'remove' };
        }

        return item;
      })
    );
  };

  const handleRemoveFile = item => {
    if (formik.isSubmitting) return;

    return item?.uid ? removeFile(item?.uid) : removeOnStatusFile(item?.id);
  };

  React.useEffect(() => {
    if (add_photo) {
      handleChoosePhoto();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    if (isEqual(validFileItems, fieldItemPhoto.value)) {
      return;
    }

    if (isEmpty(validFileItems)) setFileItemTypeValue(undefined);

    if (!meta.touched) {
      setTouched(true);
    }

    const data = validFileItems.map((x, index) => ({
      ...x,
      onUploadProgress: event => onUploadProgress(event, x?.id || x?.uid)
    }));
    setValue(data);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [validFileItems]);

  const onDnDFile = files => {
    if (!files.length) return;

    handleProcessFiles(files, fieldItemPhoto.value);
  };

  const handleChoosePhoto = useCallback(() => {
    dialogBackend.present({
      component: 'photo.dialog.ChooseAlbumItemDialog',
      props: {
        config,
        fileItems: validFileItems,
        setFileItems: setValidFileItems,
        formik
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [validFileItems]);

  const movePhoto = React.useCallback(
    (dragIndex: number, hoverIndex: number) => {
      setValidFileItems(prev => {
        let newArr = [...prev];
        newArr.splice(hoverIndex, 0, newArr.splice(dragIndex, 1)[0]);

        newArr = newArr?.map((item, idx) => ({
          status: 'update',
          ...item,
          ordering: idx + 1
        }));

        return [...newArr];
      });
    },
    [setValidFileItems]
  );

  const handleEditItem = React.useCallback(
    (id: string | number, values) => {
      setValidFileItems(prev => {
        const newValue = produce(prev, draft => {
          const itemEdit = draft.find(x => {
            return x?.uid === id || x?.id === id;
          });

          if (!itemEdit) return;

          if (!itemEdit?.status) {
            itemEdit.status = 'update';
          }

          Object.assign(itemEdit, values);
        });

        return newValue;
      });
    },
    [setValidFileItems]
  );

  const haveError = Boolean(meta.error && (meta.touched || formik.submitCount));
  const filesFilter = fieldItemPhoto?.value?.filter(
    x => x?.status !== 'remove'
  );

  if (!allowAddItems && !fieldItemPhoto.value?.length) return null;

  return (
    <>
      <FormControl error={haveError} fullWidth margin="normal">
        <Typography sx={{ fontSize: '13px' }} color="text.hint" mb={1}>
          {config.label}
        </Typography>
        {filesFilter?.length ? null : (
          <DropFileBox
            onDrop={files => onDnDFile(files)}
            render={({ canDrop, isOver }) => (
              <Control
                haveError={haveError}
                role="button"
                onClick={handleChoosePhoto}
              >
                <DropButton
                  size="small"
                  color="primary"
                  isOver={isOver}
                  variant="outlined"
                  data-testid={camelCase(`field ${name}`)}
                  startIcon={<LineIcon icon="ico-photos-plus-o" />}
                >
                  {i18n.formatMessage({ id: placeholder })}
                </DropButton>
              </Control>
            )}
          />
        )}
      </FormControl>
      <Grid container columnSpacing={1} rowSpacing={1}>
        {filesFilter.length && allowAddItems ? (
          <Grid item sm={6} md={4} xs={6}>
            <DropFileBox
              style={{ height: '100%' }}
              onDrop={files => onDnDFile(files)}
              render={({ canDrop, isOver }) => (
                <AddMoreBtnWrapper disabled={formik.isSubmitting}>
                  <DropButton
                    size="large"
                    color="primary"
                    isOver={isOver}
                    startIcon={<LineIcon icon="ico-photos-plus-o" />}
                    sx={{ fontWeight: 'bold', pl: { xs: 2.5, md: 0 }, pr: 0 }}
                    onClick={handleChoosePhoto}
                    disabled={formik.isSubmitting}
                  >
                    {!isMobile ? (
                      <>
                        {' '}
                        <LabelButton>{config?.label}</LabelButton>{' '}
                      </>
                    ) : null}
                  </DropButton>
                </AddMoreBtnWrapper>
              )}
            />
          </Grid>
        ) : null}
        {filesFilter?.map((item, index) => {
          const idFile = item?.uid || item?.id;

          return (
            <Grid item key={index} sm={6} md={4} xs={6}>
              <Box sx={{ position: 'relative' }}>
                <ItemUpload
                  aspectRatio={aspectRatio}
                  index={index}
                  id={idFile}
                  movePhoto={movePhoto}
                  item={item}
                  allowDnD={allowDrop}
                  allowRemove={allowRemoveItems}
                  allowEdit={allowEdit}
                  removeItem={() => handleRemoveFile(item)}
                  editItem={handleEditItem}
                  mappingEditPhotoFields={mappingEditPhotoFields}
                />
                {jsxBackend.render({
                  component: 'photo.progress.loading',
                  props: {
                    value: progress[idFile] || 0,
                    uploading: formik.isSubmitting
                  }
                })}
              </Box>
            </Grid>
          );
        })}
      </Grid>
      {description ? (
        <Typography sx={{ fontSize: '13px' }} color="text.hint" mt={1}>
          {description}
        </Typography>
      ) : null}
      {haveError ? <ErrorMessage error={meta.error} /> : null}
    </>
  );
}
