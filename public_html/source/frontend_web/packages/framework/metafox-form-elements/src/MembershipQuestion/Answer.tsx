/**
 * @type: formElement
 * name: form.element.Answer
 */

import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import {
  Box,
  Checkbox,
  Radio,
  styled,
  TextField,
  Tooltip
} from '@mui/material';
import { isEmpty } from 'lodash';
import React, { useMemo, useState } from 'react';
import ErrorMessage from '../ErrorMessage';

const StyledAnswer = styled('div', { name: 'StyledAnswer' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  paddingTop: theme.spacing(2)
}));

const StyledIconClose = styled('div', {
  name: 'AnswerItem',
  slot: 'IconClose'
})(({ theme }) => ({
  cursor: 'pointer',
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  width: theme.spacing(5),
  height: theme.spacing(5),
  margin: theme.spacing(0, 0.5),
  '& .ico': {
    fontSize: theme.mixins.pxToRem(16),
    color: theme.palette.text.hint
  }
}));

enum TypeQuestion {
  FreeAnswer,
  Select,
  CheckBox
}

interface AnswerProps {
  type: number;
  value?: string;
  onRemove: () => void;
  onChange: React.ChangeEventHandler<HTMLTextAreaElement | HTMLInputElement>;
  onBlur?: React.FocusEventHandler<HTMLTextAreaElement | HTMLInputElement>;
  error?: any;
  formik?: any;
  minAnswer?: any;
}

const Answer = ({
  formik,
  type,
  value: valueProp,
  onRemove,
  onChange,
  onBlur,
  error: errorFormik,
  minAnswer
}: AnswerProps) => {
  const { i18n, dialogBackend } = useGlobal();

  const [hide, setHide] = useState(type === TypeQuestion.FreeAnswer);
  const [value, setValue] = useState(valueProp || '');
  const [error, setError] = useState(null);

  React.useEffect(() => {
    if (!isEmpty(errorFormik)) {
      setError(errorFormik);
    }
  }, [errorFormik]);

  const handleError = value => {
    if (isEmpty(value)) {
      setError(
        i18n.formatMessage(
          { id: 'error_the_field_is_required' },
          { field: 'answer' }
        )
      );
    } else {
      setError(null);
    }
  };

  const handleBlur: React.FocusEventHandler<
    HTMLTextAreaElement | HTMLInputElement
  > = e => {
    const { value } = e.target;

    onBlur && onBlur(e);

    handleError(value);
  };

  const handleChange: React.ChangeEventHandler<
    HTMLInputElement | HTMLTextAreaElement
  > = e => {
    const { value } = e.target;

    handleError(value);

    setValue(value);
    onChange && onChange(e);
  };

  const iconRender = useMemo(() => {
    if (type === TypeQuestion.CheckBox) return <Checkbox disabled />;

    if (type === TypeQuestion.Select) return <Radio disabled />;
  }, [type]);

  const handleRemove = () => {
    if (formik.values.options?.length <= minAnswer) {
      dialogBackend.alert({
        message: i18n.formatMessage(
          { id: 'you_must_have_min_answer' },
          { value: minAnswer }
        )
      });

      return;
    }

    onRemove && onRemove();
    setHide(true);
  };

  if (hide) return null;

  return (
    <StyledAnswer>
      <Box sx={{ flex: 1 }}>
        <Box sx={{ display: 'flex' }}>
          {iconRender}
          <TextField
            size="small"
            fullWidth
            id="outlined-basic"
            value={value}
            variant="outlined"
            onChange={handleChange}
            onBlur={handleBlur}
            inputProps={{
              maxLength: 255
            }}
            error={!!(formik.submitCount && !!error)}
          />
          <StyledIconClose onClick={handleRemove}>
            <Tooltip
              title={i18n.formatMessage({ id: 'remove_answer' })}
              placement="top"
            >
              <LineIcon icon="ico-close" />
            </Tooltip>
          </StyledIconClose>
        </Box>
        {!!error ? (
          <Box ml={5}>
            <ErrorMessage error={error} />
          </Box>
        ) : null}
      </Box>
    </StyledAnswer>
  );
};

export default Answer;
