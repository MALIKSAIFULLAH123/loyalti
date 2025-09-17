/**
 * @type: formElement
 * name: form.element.Attachment
 * chunkName: formExtras
 */

import { FormFieldProps } from '@metafox/form';
import { BasicFileItem, useGlobal } from '@metafox/framework';
import { LineIcon, RoleLabel } from '@metafox/ui';
import {
  getFileExtension,
  shortenFileName,
  parseFileSize
} from '@metafox/utils';
import {
  Box,
  Button,
  FormControl,
  LinearProgress,
  styled
} from '@mui/material';
import { useField } from 'formik';
import produce from 'immer';
import { camelCase, get, uniqueId, isString } from 'lodash';
import React, { useCallback, useRef } from 'react';
import ErrorMessage from '../ErrorMessage';

// apply this style help automation ci works property
const fixInputStyle: React.CSSProperties = {
  width: 2,
  position: 'absolute',
  right: 0,
  opacity: 0,
  display: 'none'
};

export interface AttachmentItemProps {
  item: BasicFileItem;
  error?: string;
  handleDelete?: (id: number) => void;
  index?: number;
  isSubmitting?: boolean;
  name?: string;
  progress?: number;
}

const AttachmentIcon = styled(LineIcon, { name: 'AttachmentIcon' })(
  ({ theme }) => ({
    paddingRight: theme.spacing(0.5),
    fontSize: theme.mixins.pxToRem(15)
  })
);

const AttachmentButton = styled(Button, { name: 'AttachmentButton' })(
  ({ theme }) => ({
    fontWeight: 'bold'
  })
);

const AttachmentAction = styled('div', {
  name: 'AttachmentAction',
  shouldForwardProp: prop => prop !== 'disabled'
})<{ disabled: boolean }>(({ theme, disabled }) => ({
  color: disabled ? theme.palette.text.disabled : theme.palette.primary.main,
  marginLeft: theme.spacing(1.5),
  cursor: 'pointer',
  alignSelf: 'center',
  '&:hover': {
    textDecoration: 'underline'
  }
}));

const StyledAttachmentItem = styled(Box, {
  name: 'AttachmentItem',
  slot: 'FormItem'
})(({ theme }) => ({
  display: 'flex',
  marginTop: theme.spacing(1),
  fontSize: theme.mixins.pxToRem(13),
  lineHeight: 1.5,
  '& .info': {
    display: 'flex',
    alignItems: 'center'
  }
}));

const StyledNameFile = styled('div')(({ theme }) => ({
  overflowWrap: 'anywhere'
}));

function AttachmentItem({
  item,
  index,
  handleDelete,
  error,
  isSubmitting,
  name,
  progress
}: AttachmentItemProps) {
  const { i18n } = useGlobal();

  // does not render item is removed
  // keep item to send to server
  if (item.status === 'remove') return null;

  return (
    <div>
      <StyledAttachmentItem>
        <div
          className="info"
          data-testid={camelCase(`attachmentFile ${index}`)}
        >
          <AttachmentIcon icon="ico-paperclip-alt" />
          {item?.status === 'new' && (
            <RoleLabel
              text={i18n.formatMessage({ id: 'new' })}
              sx={{ mr: 0.5 }}
            />
          )}
          <StyledNameFile>{shortenFileName(item.file_name, 30)}</StyledNameFile>
        </div>
        {isSubmitting ? (
          <Box sx={{ marginLeft: 'auto', minWidth: '100px', pl: 1, mt: 1 }}>
            <LinearProgress variant="determinate" value={progress || 0} />
          </Box>
        ) : (
          <AttachmentAction
            onClick={() => handleDelete(index)}
            disabled={isSubmitting}
            data-testid={camelCase(`button_remove ${name}`)}
          >
            {i18n.formatMessage({ id: 'remove' })}
          </AttachmentAction>
        )}
      </StyledAttachmentItem>
      {error ? <ErrorMessage error={error} /> : null}
    </div>
  );
}

export default function AttachmentField({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) {
  const { dialogBackend, i18n } = useGlobal();
  const [field, meta, { setValue, setTouched }] = useField(
    name ?? 'AttachmentField'
  );
  const [progress, setProgress] = React.useState({});

  const {
    upload_url,
    item_type,
    fullWidth = true,
    margin = 'normal',
    size,
    max_upload_filesize,
    disabled,
    accept,
    storage_id
  } = config;

  const inputRef = useRef<HTMLInputElement>();
  const placeholder = config.placeholder || 'attach_files';
  const fieldValue = React.useMemo(() => field.value || [], [field.value]);
  const hasAttachment = Array.isArray(fieldValue) && fieldValue.length > 0;

  const handleResetValue = (
    event: React.MouseEvent<HTMLInputElement, MouseEvent>
  ) => {
    event.currentTarget.value = null;
  };

  const handleDelete = useCallback(
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

          const result = produce(fieldValue, draft => {
            if (draft[index].id) {
              draft[index].status = 'remove';
            } else {
              draft.splice(index, 1);
            }
          });
          setValue(result);
        });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [fieldValue]
  );

  const onUploadProgress = (event, index) => {
    const progress = Math.round((event.loaded * 100) / event.total);
    setProgress(prev => ({ ...prev, [index]: progress }));
  };

  const handleInputChange = useCallback(() => {
    if (!meta?.touched) {
      setTouched(true);
    }

    const file = inputRef.current.files;

    if (!file?.length) return;

    const fileItems: AttachmentItemProps[] = [];

    for (const index of Object.keys(file)) {
      const itemResult: AttachmentItemProps = {
        status: 'new',
        upload_url,
        download_url: URL.createObjectURL(file[index]),
        source: URL.createObjectURL(file[index]),
        file: file[index],
        file_name: file[index].name,
        file_size: file[index].size,
        file_type: file[index].type,
        uid: uniqueId('file'),
        fileItemType: item_type,
        extension: getFileExtension(file[index].name),
        storage_id: storage_id ?? null
      };

      const fileItemSize = itemResult.file.size;
      const fileItemName = itemResult.file_name;

      if (fileItemSize > max_upload_filesize && max_upload_filesize !== 0) {
        dialogBackend.alert({
          message: i18n.formatMessage(
            { id: 'warning_upload_limit_one_file' },
            {
              fileName: shortenFileName(fileItemName, 30),
              fileSize: parseFileSize(itemResult.file.size),
              maxSize: parseFileSize(max_upload_filesize)
            }
          )
        });

        break;
      }

      fileItems.push(itemResult);
    }

    if (fileItems?.length > 0) {
      setValue(
        [...fieldValue, ...fileItems].map((x, idx) => ({
          ...x,
          onUploadProgress: event => onUploadProgress(event, idx)
        }))
      );
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [fieldValue]);

  const handleControlClick = useCallback(() => {
    inputRef.current.click();
  }, []);

  return (
    <FormControl
      fullWidth={fullWidth}
      margin={margin}
      size={size}
      data-testid={camelCase(`field ${name}`)}
    >
      <div>
        <AttachmentButton
          variant="outlined"
          size="small"
          color="primary"
          data-testid={camelCase(`button_add ${name}`)}
          onClick={handleControlClick}
          disabled={disabled || forceDisabled || formik.isSubmitting}
          startIcon={<LineIcon icon="ico-paperclip-alt" />}
        >
          {i18n.formatMessage({ id: placeholder })}
        </AttachmentButton>
      </div>
      {hasAttachment &&
        fieldValue.map((item, index) => {
          return (
            <AttachmentItem
              key={index.toString()}
              handleDelete={handleDelete}
              index={index}
              item={item}
              name={name}
              error={isString(meta.error) ? '' : get(meta.error, index)}
              isSubmitting={formik.isSubmitting}
              progress={progress[index]}
            />
          );
        })}
      {isString(meta.error) ? <ErrorMessage error={meta.error} /> : null}
      <input
        onClick={handleResetValue}
        data-testid={camelCase(`input ${name}`)}
        ref={inputRef}
        style={fixInputStyle}
        accept={accept}
        aria-hidden
        type="file"
        multiple
        onChange={handleInputChange}
        hidden
      />
    </FormControl>
  );
}
