/**
 * @type: formElement
 * name: form.element.MultiAudioField
 * chunkName: formElement
 */

import { FormFieldProps, FormSchemaShape } from '@metafox/form';
import {
  BasicFileItem,
  RemoteDataSource,
  useGlobal,
  useResourceAction,
  useResourceForm
} from '@metafox/framework';
import { DropFileBox, InputNotched, LineIcon } from '@metafox/ui';
import {
  checkFileAccept,
  parseFileSize,
  shortenFileName
} from '@metafox/utils';
import {
  Box,
  Button,
  FormControl,
  Typography,
  InputLabel,
  LinearProgress
} from '@mui/material';
import { styled } from '@mui/material/styles';
import { useField } from 'formik';
import produce from 'immer';
import {
  camelCase,
  uniqueId,
  debounce,
  isString,
  isArray,
  isEmpty
} from 'lodash';
import React, { useRef } from 'react';
import ErrorMessage from '../ErrorMessage';
import { IntlShape } from 'react-intl';
import EditItemForm from './EditItemForm';

export interface DropButtonProps {
  isOver?: boolean;
}
type AudioItemProp = {
  item: Record<string, any>;
  index: number;
  handleChange: (valueField: any, index: number, fieldName: string) => void;
  showEditAudio: boolean;
  handleEditAudio: () => void;
  handleRemoveAudio: () => void;
  maxLengthName: number;
  i18n: IntlShape;
  error: any;
  enabled: boolean;
  progress: number;
  uploading: boolean;
  formSchemaEditItem?: FormSchemaShape;
  dataSourceEditItem?: RemoteDataSource;
};

const name = 'MultiAudioField';
const DropButton = styled(Button, {
  name,
  slot: 'DropButton'
})<DropButtonProps>(({ theme, isOver }) => ({
  ...(isOver && {
    backgroundColor: theme.palette.action.hover
  })
}));

const AudioUploaded = styled('div', {
  name,
  slot: 'AudioUploaded'
})<{}>(({ theme }) => ({
  display: 'flex',
  fontSize: theme.mixins.pxToRem(13),
  justifyContent: 'space-between',
  paddingBottom: theme.spacing(1),
  '& .ico-music-note-o': {
    fontSize: theme.mixins.pxToRem(15)
  }
}));

const IconAction = styled('div', {
  name,
  slot: 'IconAction'
})<{}>(({ theme }) => ({
  display: 'flex',
  '& > span': {
    marginLeft: theme.spacing(1)
  }
}));

const DropzoneBox = styled('div', {
  name,
  slot: 'DropzoneBox'
})<{}>(({ theme }) => ({
  width: 'fit-content'
}));

const Label = styled(InputLabel, {
  name,
  slot: 'Label'
})<{ haveError?: boolean }>(({ theme, haveError }) => ({
  ...(haveError && {
    color: theme.palette.error.main
  })
}));

const Noti = styled(Typography, {
  name,
  slot: 'Noti'
})<{ haveError?: boolean }>(({ theme, haveError }) => ({
  ...(haveError && {
    color: theme.palette.error.main
  })
}));

const TitleSong = styled(Typography, {
  name,
  slot: 'TitleSong'
})(({ theme }) => ({
  marginLeft: theme.spacing(1),
  marginRight: theme.spacing(1),
  overflowWrap: 'anywhere'
}));

const WrapTitleStyled = styled('div', {
  name,
  slot: 'WrapTitleStyled'
})<{}>(({ theme }) => ({
  display: 'flex',
  alignItems: 'center'
}));

function AudioItem({
  item,
  index,
  handleChange,
  showEditAudio,
  handleEditAudio,
  handleRemoveAudio,
  error,
  enabled,
  progress,
  uploading,
  formSchemaEditItem,
  dataSourceEditItem
}: AudioItemProp) {
  // does not render item is removed
  // keep item to send to server
  if (item.status === 'remove') return null;

  const haveError = isArray(error) && error[index];
  const noEdit = isEmpty(formSchemaEditItem) && isEmpty(dataSourceEditItem);

  return (
    <>
      <AudioUploaded data-testid={`audioUploaded_${index}`}>
        <WrapTitleStyled data-testid={`audioUploadedWrapTitle_${index}`}>
          <LineIcon icon="ico-music-note-o" />
          <TitleSong
            data-testid={`audioUploadedTitle_${index}`}
            color={haveError ? 'error' : null}
          >
            {shortenFileName(item?.file_name, 70)}
          </TitleSong>
        </WrapTitleStyled>
        <IconAction data-testid={`audioUploadedAction_${index}`}>
          {uploading ? (
            <Box sx={{ minWidth: '100px', pl: 1, mt: '6px' }}>
              <LinearProgress
                data-testid={`audioUploadedProgress_${index}`}
                variant="determinate"
                value={progress || 0}
              />
            </Box>
          ) : (
            <>
              {noEdit ? null : (
                <Typography
                  data-testid={`audioUploadedEdit_${index}`}
                  variant="body2"
                  color="primary"
                  component="span"
                  role="button"
                  onClick={() =>
                    enabled && handleEditAudio(item?.id || item?.uid)
                  }
                >
                  <LineIcon icon="ico-compose" />
                </Typography>
              )}
              <Typography
                data-testid={`audioUploadedRemove_${index}`}
                variant="body2"
                color="primary"
                component="span"
                role="button"
                onClick={() => enabled && handleRemoveAudio(index)}
              >
                <LineIcon icon="ico-close" />
              </Typography>
            </>
          )}
        </IconAction>
      </AudioUploaded>
      {!noEdit && showEditAudio === (item?.id || item?.uid) ? (
        <EditItemForm
          initialValues={item}
          formSchema={formSchemaEditItem}
          dataSource={dataSourceEditItem}
          onChange={(value: any, name: string) =>
            handleChange(value, index, name)
          }
        />
      ) : null}
    </>
  );
}

export default function MultiAudioField({
  name,
  config,
  disabled: forceDisabled,
  formik
}: FormFieldProps) {
  const {
    label = 'Select songs',
    description,
    item_type,
    accept = 'audio/mp3',
    max_upload_filesize,
    max_length_name = 100,
    maxFilesPerUpload,
    maxFiles,
    upload_url,
    disabled,
    storage_id,
    placeholder,
    appName,
    resourceName,
    formEditItem,
    dataSourceName,
    messageAcceptFail
  } = config;
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const { dialogBackend, i18n } = useGlobal();

  const formSchemaEditItem = useResourceForm(
    appName,
    resourceName,
    formEditItem
  );
  const dataSourceEditItem = useResourceAction(
    appName,
    resourceName,
    dataSourceName
  );

  const [showEditAudio, setShowEditAudio] = React.useState<string>();
  const inputRef = useRef<HTMLInputElement>();
  const [field, meta, { setValue, setTouched }] = useField(
    name ?? 'ItemPhotoField'
  );
  const [listAudio, setListAudio] = React.useState<BasicFileItem[]>(
    field.value || []
  );
  const [progress, setProgress] = React.useState({});

  const onUploadProgress = (event, index) => {
    const progress = Math.round((event.loaded * 100) / event.total);

    setProgress(prev => ({ ...prev, [index]: progress }));
    // eslint-disable-next-line no-console
  };

  const handleControlClick = () => {
    inputRef.current.click();
  };

  const titleFile = (name: string) => {
    const lastIndex = name.lastIndexOf('.');

    return name.slice(0, lastIndex);
  };

  const handleRemoveAudio = React.useCallback(
    (index: number) => {
      dialogBackend
        .confirm({
          message: i18n.formatMessage({
            id: 'are_you_sure_you_want_to_delete_attachment_file'
          }),
          title: i18n.formatMessage({ id: 'are_you_sure' })
        })
        .then(oke => {
          if (!oke) return;

          setListAudio(prev =>
            produce(prev, draft => {
              if (draft[index].id) {
                draft[index].status = 'remove';
              } else {
                draft.splice(index, 1);
              }
            })
          );
        });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    []
  );

  const handleEditAudio = React.useCallback(
    (uid: any) => {
      if (showEditAudio !== uid) {
        setShowEditAudio(uid);
      } else {
        setShowEditAudio(null);
      }
    },
    [showEditAudio]
  );

  React.useEffect(() => {
    setValue(
      listAudio.map((x, index) => ({
        ...x,
        onUploadProgress: event => onUploadProgress(event, index)
      }))
    );
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [listAudio]);

  const handleFiles = files => {
    const fileItems: BasicFileItem[] = [];

    for (const index of Object.keys(files)) {
      const itemResult: BasicFileItem = {
        id: 0,
        status: 'new',
        upload_url,
        name: titleFile(files[index].name).slice(0, max_length_name),
        description: '',
        source: URL.createObjectURL(files[index]),
        file: files[index],
        file_name: files[index].name,
        file_size: files[index].size,
        file_type: files[index].type,
        uid: uniqueId('file'),
        fileItemType: item_type,
        storage_id: storage_id ?? null
      };

      const fileItemSize = itemResult?.file_size;

      if (!checkFileAccept(files[index]?.type, accept)) {
        dialogBackend.alert({
          message: i18n.formatMessage({
            id: messageAcceptFail || 'file_accept_type_fail'
          })
        });

        if (inputRef?.current) {
          inputRef.current.value = null;
        }

        break;
      }

      if (
        fileItemSize > max_upload_filesize?.music &&
        max_upload_filesize?.music
      ) {
        dialogBackend.alert({
          message: i18n.formatMessage(
            { id: 'warning_upload_limit_one_file' },
            {
              fileName: shortenFileName(itemResult.file_name, 30),
              fileSize: parseFileSize(itemResult.file_size),
              maxSize: parseFileSize(max_upload_filesize?.music)
            }
          )
        });

        return;
      }

      fileItems.push(itemResult);
    }

    if (fileItems.length) {
      setListAudio(
        produce(draft => {
          draft.push(...fileItems);
        })
      );
    }
  };

  const handleInputChange = React.useCallback(() => {
    const files = inputRef.current.files;

    if (!files) return;

    setTouched(true);
    handleFiles(files);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [listAudio]);

  const onDnDFile = files => {
    if (!files) return;

    handleFiles(files);
  };

  const debounce_fun = debounce((index, val, name) => {
    setListAudio(
      produce(draft => {
        if (draft[index].id) {
          draft[index].status = 'update';
        }

        draft[index][name] = val;
      })
    );
  }, 1000);

  const handleChange = (val: string, index: number, name: string) => {
    debounce_fun(index, val, name);
  };

  const handleResetValue = (
    event: React.MouseEvent<HTMLInputElement, MouseEvent>
  ) => {
    event.currentTarget.value = null;
  };

  const uploadFiles = listAudio.filter(x => x.uid);

  const haveErr = !!(meta.error && (meta.touched || formik.submitCount));

  return (
    <FormControl
      fullWidth
      margin="normal"
      data-testid={camelCase(`field ${name}`)}
    >
      <Label haveError={haveErr} required variant="outlined" shrink="true">
        {label}
      </Label>
      <Box sx={{ p: 2, pt: 2.5 }}>
        <Noti variant="body2" color="text.hint" mb={1.5} component="p">
          {description}
        </Noti>
        <DropzoneBox>
          <DropFileBox
            onDrop={files => onDnDFile(files)}
            render={({ isOver }) => (
              <DropButton
                onClick={handleControlClick}
                size="small"
                color="primary"
                sx={{ fontWeight: 'bold' }}
                isOver={isOver}
                variant="outlined"
                disabled={
                  disabled ||
                  forceDisabled ||
                  formik.isSubmitting ||
                  listAudio.length >= maxFiles ||
                  uploadFiles.length >= maxFilesPerUpload
                }
                data-testid={camelCase(`button ${name}`)}
                startIcon={<LineIcon icon="ico-upload" />}
              >
                {placeholder}
              </DropButton>
            )}
          />
        </DropzoneBox>
        {listAudio.length > 0 && (
          <Box data-testid={camelCase('audio uploaded list')} sx={{ mt: 2 }}>
            {listAudio.map((item, index) => (
              <AudioItem
                i18n={i18n}
                key={index.toString()}
                handleRemoveAudio={handleRemoveAudio}
                handleChange={handleChange}
                handleEditAudio={handleEditAudio}
                showEditAudio={showEditAudio}
                index={index}
                item={item}
                error={meta.error}
                enabled={!formik.isSubmitting}
                progress={progress[index]}
                uploading={formik.isSubmitting}
                formSchemaEditItem={formSchemaEditItem}
                dataSourceEditItem={dataSourceEditItem}
              />
            ))}
          </Box>
        )}
        {haveErr && isString(meta.error) ? (
          <ErrorMessage error={meta.error} />
        ) : null}
      </Box>
      <InputNotched
        haveError={haveErr}
        children={config.label}
        variant="outlined"
      />
      <input
        ref={inputRef}
        onClick={handleResetValue}
        className="srOnly"
        type="file"
        data-testid={camelCase(`input ${name}`)}
        multiple
        accept={accept}
        onChange={handleInputChange}
      />
    </FormControl>
  );
}
