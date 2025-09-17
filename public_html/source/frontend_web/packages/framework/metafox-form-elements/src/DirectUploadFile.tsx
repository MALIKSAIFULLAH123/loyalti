/**
 * @type: formElement
 * name: form.element.DirectUploadFile
 * chunkName: formBasic
 */
import { FormFieldProps } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import { LineIcon, Image } from '@metafox/ui';
import {
  Box,
  Button,
  FormControl,
  Tooltip,
  styled,
  Skeleton,
  FormLabel
} from '@mui/material';
import { useField } from 'formik';
import { camelCase, get } from 'lodash';
import React from 'react';

const fixInputStyle: React.CSSProperties = {
  width: 2,
  right: 0,
  position: 'absolute',
  opacity: 0
};

const Preview = styled('div', { name: 'Preview' })(({ theme }) => ({
  marginTop: theme.spacing(1),
  borderRadius: theme.shape.borderRadius / 2,
  width: 200,
  maxWidth: 200,
  overflow: 'hidden',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  position: 'relative',
  '& button': {
    '& + button': {
      marginLeft: theme.spacing(1)
    }
  }
}));

const RemoveBtn = styled('div', {
  name: 'RemoveBtn',
  slot: 'removeBtn'
})(({ theme }) => ({
  width: theme.spacing(3),
  height: theme.spacing(3),
  borderRadius: theme.shape.borderRadius * 1.5,
  backgroundColor: 'rgba(0,0,0,0.89)',
  color: '#fff',
  position: 'absolute',
  top: theme.spacing(2),
  right: theme.spacing(2),
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  cursor: 'pointer'
}));

function DirectUploadFile({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) {
  const { fullWidth, label, margin } = config;
  const { i18n, apiClient, dialogBackend } = useGlobal();
  const [field, , { setValue }] = useField(name);
  const [loading, setLoading] = React.useState(false);
  const inputRef = React.useRef<HTMLInputElement>();

  const handleFileChange = () => {
    setLoading(true);
    const file = inputRef.current.files.item(0);

    if (!file.type.match('image/*')) {
      dialogBackend.alert({
        message: i18n.formatMessage({ id: 'file_accept_type_fail' })
      });
      setLoading(false);

      return;
    }

    const formData = new FormData();

    formData.append('file', file);

    const type = 'photo';
    formik.setSubmitting(true);

    formData.append('type', type);
    formData.append('name', 'file');
    formData.append('item_type', type);
    formData.append('file_type', type);
    formData.append('file_name', file.name);
    formData.append('file_size', file.size.toString());

    apiClient
      .post('/file', formData)
      .then(response => get(response, 'data.data'))
      .then(data => {
        setValue(data.url);
        formik.setSubmitting(false);
        setLoading(false);
      })

      .catch(err => {
        setLoading(false);
      });
  };

  const handleControlClick = () => {
    inputRef.current.click();
  };

  const handleDeletePhoto = () => {
    setValue('');
  };

  return (
    <FormControl
      margin={margin}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
    >
      {label ? <FormLabel sx={{ mb: 1 }}>{label}</FormLabel> : null}
      <div>
        <Button
          size="small"
          color="primary"
          variant="outlined"
          onClick={handleControlClick}
          startIcon={<LineIcon icon="ico-photo-plus-o" />}
        >
          {i18n.formatMessage({ id: 'add_photo' })}
        </Button>
      </div>
      {loading && (
        <Box mt={1}>
          <Skeleton variant="rounded" width={200} height={100} />
          <Skeleton variant="text" sx={{ fontSize: '15px' }} width="80%" />
        </Box>
      )}
      {field?.value && !loading && (
        <>
          <Preview>
            <Image src={field.value} />
            <Tooltip title={i18n.formatMessage({ id: 'remove' })}>
              <RemoveBtn onClick={handleDeletePhoto}>
                <LineIcon icon="ico-close" />
              </RemoveBtn>
            </Tooltip>
          </Preview>
          <Box mt={1} sx={{ wordBreak: 'break-all' }}>
            {field.value}
          </Box>
        </>
      )}
      <input
        ref={inputRef}
        type="file"
        accept="image/*"
        data-testid={camelCase(`input ${name}`)}
        onChange={handleFileChange}
        style={fixInputStyle}
      />
    </FormControl>
  );
}
export default DirectUploadFile;
