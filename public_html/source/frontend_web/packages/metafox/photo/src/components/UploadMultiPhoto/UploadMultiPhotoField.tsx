/**
 * @type: formElement
 * name: form.element.UploadMultiPhoto
 */

import { FormFieldProps } from '@metafox/form';
import ErrorMessage from '@metafox/form-elements/ErrorMessage';
import { useGlobal } from '@metafox/framework';
import { DropFileBox, LineIcon } from '@metafox/ui';
import {
  Button,
  FormControl,
  Grid,
  Typography,
  InputLabel,
  Box
} from '@mui/material';
import { styled } from '@mui/material/styles';
import { useField } from 'formik';
import { camelCase, isEmpty, isEqual } from 'lodash';
import React, { useRef } from 'react';
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

const AddMoreBtnWrapper = styled(Button, {
  name: 'MultipleUploadField',
  slot: 'AddMoreBtnWrapper'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: theme.palette.primary.main,
  borderStyle: 'solid',
  borderWidth: 1,
  borderRadius: theme.shape.borderRadius,
  height: '100%',
  width: '100%'
}));

const Label = styled(InputLabel, {
  name: 'Label'
})<{ haveError?: boolean }>(({ theme, haveError }) => ({
  padding: theme.spacing(0, 1),
  background: theme.palette.background.paper,
  ...(haveError && {
    color: theme.palette.error.main
  })
}));

export default function UploadMultiPhotoField({
  name,
  formik,
  acceptTypeFile,
  config,
  disabled: forceDisabled
}: FormFieldProps) {
  const {
    accept,
    upload_url,
    item_type,
    max_upload_filesize: maxSizeLimit,
    description,
    label,
    isVideoUploadAllowed,
    acceptFail,
    required,
    allowDrop,
    allowEditPhoto: allowEdit = false,
    thumbnail_sizes,
    aspectRatio = 'auto',
    columnSpacing = 1,
    rowSpacing = 1,
    grid = { md: 4, sm: 6, xs: 6 },
    mappingEditPhotoFields = [],
    editPhotoAction
  } = config;
  const { i18n, jsxBackend, ParserPreviewPhoto } = useGlobal();
  const [, , { setValue: setFileItemTypeValue }] = useField('fileItemType');
  const [fieldItemPhoto, meta, { setValue, setTouched }] = useField(
    name ?? 'ItemPhotoField'
  );

  const inputRef = useRef<HTMLInputElement>();

  const [progress, setProgress] = React.useState({});

  const onUploadProgress = (event, idFile) => {
    const progress = Math.round((event.loaded * 100) / event.total);

    setProgress(prev => ({ ...prev, [idFile]: progress }));
    // eslint-disable-next-line no-console
  };

  const [validFileItems, setValidFileItems, handleProcessFiles] =
    useCheckMediaFileSize({
      initialValues: fieldItemPhoto.value || [],
      upload_url,
      maxSizeLimit,
      isAcceptVideo: isVideoUploadAllowed,
      messageAcceptFail: acceptFail,
      accept: acceptTypeFile || accept,
      inputRef
    });
  const placeholder = config.placeholder || 'add_photo';

  const removeFile = (uid: string) =>
    setValidFileItems(prev => prev.filter(item => item.uid !== uid));

  const removeOnStatusFile = (id: string | number) => {
    setValidFileItems(prev =>
      prev.map(item => {
        if (item.id === id) {
          return { ...item, status: 'remove' };
        }

        return item;
      })
    );
  };

  React.useEffect(() => {
    if (isEqual(validFileItems, fieldItemPhoto.value)) {
      return;
    }

    if (isEmpty(validFileItems)) setFileItemTypeValue(undefined);

    if (!isEmpty(validFileItems) && !meta.touched) {
      setTouched(true);
    }

    const data = validFileItems.map((x, index) => ({
      ...x,
      ordering: index + 1,
      file_type: x?.type,
      fileItemType: item_type,
      status: x?.status,
      thumbnail_sizes,
      onUploadProgress: event => onUploadProgress(event, x?.id || x?.uid)
    }));

    setValue(data);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [validFileItems]);

  const handleControlClick = () => {
    inputRef.current.click();
  };

  const onDnDFile = files => {
    if (!files.length) return;

    handleProcessFiles(files, fieldItemPhoto.value);
  };

  const handleInputChange = () => {
    const files = inputRef.current.files;

    if (!files.length) return;

    setFileItemTypeValue(item_type);

    handleProcessFiles(Object.values(files), fieldItemPhoto.value);

    // clear value of inputRef when selected done
    if (inputRef?.current) {
      inputRef.current.value = null;
    }
  };

  const haveError = Boolean(meta.error && (meta.touched || formik.submitCount));
  const filesFilter = fieldItemPhoto?.value?.filter(
    x => x?.status !== 'remove'
  );
  const hasFiles = filesFilter?.length;

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

  const onParseFile = (parseFile, item) => {
    setValidFileItems(prevItems =>
      prevItems.map(file =>
        (file?.uid === item?.uid && parseFile?.preUploadFile?.url
          ? {
              ...file,
              ...parseFile?.preUploadFile,
              source: parseFile.source,
              id: parseFile?.preUploadFile?.temp_file
            }
          : file)
      )
    );
  };

  return (
    <>
      <FormControl
        required={required}
        error={haveError}
        fullWidth
        margin="normal"
        data-testid={camelCase(`field ${name}`)}
      >
        <Label
          haveError={haveError}
          required={required}
          variant="outlined"
          shrink
          color="primary"
          disabled={forceDisabled || formik.isSubmitting}
        >
          {config.label}
        </Label>
        {!hasFiles ? (
          <DropFileBox
            onDrop={files => onDnDFile(files)}
            render={({ canDrop, isOver }) => (
              <Control
                haveError={haveError}
                role="button"
                onClick={handleControlClick}
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
        ) : (
          <Grid container columnSpacing={columnSpacing} rowSpacing={rowSpacing}>
            <Grid item {...grid}>
              <DropFileBox
                style={{ height: '100%' }}
                onDrop={files => onDnDFile(files)}
                render={({ canDrop, isOver }) => (
                  <AddMoreBtnWrapper
                    disabled={forceDisabled || formik.isSubmitting}
                    onClick={handleControlClick}
                    startIcon={<LineIcon icon="ico-photos-plus-o" />}
                    sx={{ fontWeight: 'bold' }}
                    size="large"
                  >
                    {label}
                  </AddMoreBtnWrapper>
                )}
              />
            </Grid>
            {filesFilter?.map((item, index) => {
              const idFile = item?.uid || item?.id;

              return (
                <Grid item key={idFile} {...grid}>
                  <ParserPreviewPhoto
                    item={item}
                    onParse={parseFile => onParseFile(parseFile, item)}
                  >
                    <Box sx={{ position: 'relative' }}>
                      <ItemUpload
                        aspectRatio={aspectRatio}
                        index={index}
                        id={idFile}
                        movePhoto={movePhoto}
                        item={item}
                        allowDnD={allowDrop}
                        allowEdit={allowEdit}
                        editPhotoAction={editPhotoAction}
                        mappingEditPhotoFields={mappingEditPhotoFields}
                        removeItem={() =>
                          !formik.isSubmitting &&
                          (item?.uid
                            ? removeFile(item?.uid)
                            : removeOnStatusFile(item.id))
                        }
                        editItem={handleEditItem}
                      />
                      {jsxBackend.render({
                        component: 'photo.progress.loading',
                        props: {
                          value: progress[idFile] || 0,
                          uploading: formik.isSubmitting
                        }
                      })}
                    </Box>
                  </ParserPreviewPhoto>
                </Grid>
              );
            })}
          </Grid>
        )}
        <input
          ref={inputRef}
          type="file"
          aria-hidden
          className="srOnly"
          multiple
          accept={acceptTypeFile || accept}
          onChange={handleInputChange}
        />
      </FormControl>
      {description ? (
        <Typography sx={{ fontSize: '13px' }} color="text.hint" mt={1}>
          {description}
        </Typography>
      ) : null}
      {haveError ? <ErrorMessage error={meta.error} /> : null}
    </>
  );
}
