import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { CheckCircle, RadioButtonUnchecked } from '@mui/icons-material';
import { Radio, styled, TextField, Tooltip, Typography } from '@mui/material';
import React, { memo } from 'react';
import ErrorMessage from '../ErrorMessage';
import { camelCase } from 'lodash';

const name = 'AnswerItem';
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
    fontSize: theme.mixins.pxToRem(18),
    color: theme.palette.text.hint
  }
}));

const StyledRadioGroup = styled('div', { name, slot: 'Radiogroup' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center'
  })
);

const AnswerItemStyled = styled('div', { name, slot: 'AnswerItemStyled' })(
  ({ theme }) => ({
    display: 'flex',
    alignItems: 'center'
  })
);

const AnswerInput = styled(TextField, { name, slot: 'AnswerInput' })(
  ({ theme }) => ({
    width: 'calc(100% / 3 * 2)',
    '& .MuiOutlinedInput-root': {
      borderRadius: theme.spacing(0.5)
    }
  })
);

const EndAdornmentButton = styled('div', { name, slot: 'EndAdornmentButton' })(
  ({ theme }) => ({
    width: 'calc(100% / 3 * 1)',
    display: 'flex',
    alignItems: 'center'
  })
);

const ButtonWrapper = styled('div', { name, slot: 'ButtonWrapper' })(
  ({ theme }) => ({
    display: 'flex',
    width: '100%',
    justifyContent: 'flex-start',
    alignItem: 'center'
  })
);

const RadioButton = styled(Radio, { name, slot: 'RadioButton' })(
  ({ theme }) => ({
    width: 40,
    height: 40
  })
);

const AnswerItem = ({
  lastElement,
  index,
  item,
  disabled,
  questionIndex,
  addMoreAnswer,
  handleDeleteAnswer,
  handleChangeAnswer,
  handleChangeCorrectAnswer,
  submitCount,
  error,
  handleTouched,
  maxLength
}: any) => {
  const { i18n } = useGlobal();
  const [touched, setTouched] = React.useState<boolean>(false);

  // don't show error until user leave text field
  const haveError = Boolean(error && (touched || submitCount));

  const handleBlur = () => {
    handleTouched();
    setTouched(true);
  };

  return (
    <>
      <AnswerItemStyled key={index} data-testid={`answer_${index + 1}`}>
        <AnswerInput
          onBlur={handleBlur}
          variant="outlined"
          margin="dense"
          disabled={disabled}
          required
          placeholder={i18n.formatMessage(
            { id: 'answer_index' },
            { index: index + 1 }
          )}
          defaultValue={item.answer}
          error={haveError}
          onChange={e => handleChangeAnswer(e, questionIndex, index)}
          inputProps={{
            maxLength
          }}
        />
        <EndAdornmentButton>
          <ButtonWrapper>
            <StyledIconClose
              data-testid={camelCase(`button_remove_answer ${name}`)}
              onClick={handleDeleteAnswer}
            >
              <Tooltip
                title={i18n.formatMessage({ id: 'remove_answer' })}
                placement="top"
              >
                <LineIcon icon="ico-close" />
              </Tooltip>
            </StyledIconClose>
            <StyledRadioGroup>
              <RadioButton
                checked={Boolean(item.is_correct)}
                icon={<RadioButtonUnchecked />}
                checkedIcon={<CheckCircle />}
                disabled={disabled}
                name="checkedH"
                color="primary"
                data-testid={camelCase(`radio_correct_answer ${name}`)}
                onChange={e =>
                  handleChangeCorrectAnswer(e, questionIndex, index)
                }
              />
              <Typography
                variant="body1"
                color="text.hint"
                sx={{ display: { sm: 'block', xs: 'none' } }}
              >
                {i18n.formatMessage({ id: 'correct_answer' })}
              </Typography>
            </StyledRadioGroup>
          </ButtonWrapper>
        </EndAdornmentButton>
      </AnswerItemStyled>
      {haveError ? <ErrorMessage error={error} /> : null}
    </>
  );
};

export default memo(AnswerItem);
