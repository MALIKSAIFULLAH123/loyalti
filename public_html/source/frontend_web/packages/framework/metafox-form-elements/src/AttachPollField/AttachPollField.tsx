/**
 * @type: formElement
 * name: form.element.AttachPoll
 */

import { FormFieldProps } from '@metafox/form';
import { useGlobal } from '@metafox/framework';
import { useField } from 'formik';
import React from 'react';
import ErrorMessage from '../ErrorMessage';
import { Button, styled, Box, FormControl } from '@mui/material';
import { camelCase } from 'lodash';
import { LineIcon } from '@metafox/ui';

const AttachPollButton = styled(Button, { name: 'AttachPollButton' })(() => ({
  fontWeight: 'bold'
}));

const AttachPoll = ({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) => {
  const { jsxBackend, dialogBackend, i18n } = useGlobal();
  const {
    disabled,
    formUrl,
    fullWidth = true,
    margin = 'normal',
    size
  } = config;
  const [field, meta, { setValue }] = useField(name ?? 'AttachPollField');
  const placeholder =
    config.placeholder || i18n.formatMessage({ id: 'attach_poll' });

  const handleControlClick = React.useCallback(() => {
    dialogBackend
      .present({
        component: 'poll.dialog.AttachPollDialog',
        props: {
          formUrl
        }
      })
      .then(value => {
        if (!value) return;

        setValue(value);
      });
  }, [dialogBackend, formUrl, setValue]);
  const pollData = field?.value;
  const isEachFieldError = meta.error;

  return (
    <FormControl
      fullWidth={fullWidth}
      margin={margin}
      size={size}
      data-testid={camelCase(`field ${name}`)}
    >
      <div>
        <AttachPollButton
          variant="outlined"
          size="small"
          color="primary"
          data-testid={camelCase(`button ${name}`)}
          onClick={handleControlClick}
          disabled={
            disabled || forceDisabled || formik.isSubmitting || !!pollData
          }
          startIcon={<LineIcon icon="ico-barchart-o" />}
        >
          {placeholder}
        </AttachPollButton>
      </div>
      {pollData ? (
        <Box mt={2}>
          {jsxBackend.render({
            component: 'AttachPollPreview',
            props: {
              value: pollData,
              formUrl,
              handleRemove: () => setValue(null),
              handleEdit: setValue
            }
          })}
        </Box>
      ) : null}
      {meta.error && !isEachFieldError ? (
        <ErrorMessage error={meta.error?.toString()} />
      ) : null}
    </FormControl>
  );
};

export default AttachPoll;
