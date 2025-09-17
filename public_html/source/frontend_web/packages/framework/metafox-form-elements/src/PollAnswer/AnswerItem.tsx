import { useGlobal } from '@metafox/framework';
import { Box, Button, TextField } from '@mui/material';
import React from 'react';
import ErrorMessage from '../ErrorMessage';
import useStyles from './styles';
import { AnswerPollProps } from './types';
import { LineIcon } from '@metafox/ui';
import { camelCase } from 'lodash';

const AnswerItem = ({
  lastElement,
  index,
  item,
  handleDelete,
  handleAdd,
  handleChange,
  disabled,
  submitCount,
  error,
  maxLength,
  disabledAddMore,
  disableRemove,
  meta,
  setTouched,
  name,
  fieldName
}: AnswerPollProps) => {
  const classes = useStyles();
  const { i18n } = useGlobal();
  const haveError = Boolean(error && (meta.touched || submitCount));

  return (
    <>
      <div className={classes.answerItem}>
        <TextField
          className={classes.answerInput}
          required
          variant="outlined"
          onBlur={() => setTouched(true)}
          fullWidth
          margin="normal"
          disabled={disabled}
          inputProps={{
            maxLength,
            'data-testid': camelCase(`input ${name}`)
          }}
          label={i18n.formatMessage(
            { id: 'option_answer' },
            { value: index + 1 }
          )}
          defaultValue={item.answer}
          onChange={e => handleChange(e, item.id)}
          error={haveError}
          data-testid={camelCase(`field ${name}`)}
        />
        <div className={classes.endAdornmentButton}>
          <div className={classes.buttonWrapper}>
            <Button
              className={classes.button}
              onClick={() => handleDelete(item.order)}
              disabled={disableRemove || disabled}
              variant="outlined"
              color="primary"
              data-testid={camelCase(`button_remove ${name}`)}
            >
              <Box sx={{ display: { sm: 'block', xs: 'none' } }}>
                {i18n.formatMessage({ id: 'remove' })}
              </Box>
              <Box sx={{ display: { sm: 'none' } }}>
                <LineIcon icon="ico-trash-alt" />
              </Box>
            </Button>
            <Button
              className={lastElement ? classes.button : classes.visible}
              onClick={() => handleAdd()}
              variant="outlined"
              color="primary"
              disabled={disabledAddMore || disabled}
              data-testid={
                lastElement
                  ? camelCase(`button_add ${fieldName}`)
                  : camelCase(`button_add ${name}`)
              }
            >
              <Box sx={{ display: { sm: 'block', xs: 'none' } }}>
                {i18n.formatMessage({ id: 'add' })}
              </Box>
              <Box sx={{ display: { sm: 'none' } }}>
                <LineIcon icon="ico-plus" />
              </Box>
            </Button>
          </div>
        </div>
      </div>
      {haveError ? <ErrorMessage error={error} /> : null}
    </>
  );
};

export default AnswerItem;
