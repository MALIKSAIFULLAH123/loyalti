/**
 * @type: formElement
 * name: form.element.NumberCode
 * chunkName: formBasic
 */

import React from 'react';
import { FormFieldProps } from '@metafox/form';
import { Box, FormControl, styled } from '@mui/material';
import { useField } from 'formik';
import ErrorMessage from './ErrorMessage';
import { camelCase } from 'lodash';

const RootStyled = styled(Box)(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  margin: theme.spacing(1.5, 0, 2.5, 0)
}));

const Wrapper = styled(Box)(({ theme }) => ({
  display: 'flex',
  flexDirection: 'column',
  flex: 1,
  alignItems: 'center'
}));

const WrapperError = styled(Box)(({ theme }) => ({
  minHeight: theme.spacing(3.25)
}));

const ItemInput = styled(Box)(({ theme }) => ({
  border: theme.mixins.border('secondary'),
  ...(theme.palette.mode === 'light' && {
    borderColor: 'rgba(0, 0, 0, 0.2)'
  }),
  width: '48px',
  height: '54px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  fontSize: '32px',
  position: 'relative',
  marginRight: theme.spacing(1),
  borderRadius: theme.spacing(0.5),
  '&.last-item': {
    marginRight: 0
  }
}));

const InputStyled = styled('input', {
  shouldForwardProp: props => props !== 'focused' && props !== 'lastItem'
})<{ focused?: any; lastItem?: boolean }>(({ theme, focused, lastItem }) => ({
  position: 'absolute',
  fontSize: '32px',
  textAlign: 'center',
  outline: 'none',
  backgroundColor: 'transparent',
  width: '48px',
  height: '54px',
  ...(theme.palette.mode === 'dark' && {
    caretColor: 'white'
  }),
  ...(lastItem && {
    caretColor: 'transparent'
  }),
  border: 'none',
  ...(focused && {
    border: theme.mixins.border('primary'),
    borderRadius: theme.spacing(0.5)
  })
}));

const NumberCode = ({ config, name, formik }: FormFieldProps) => {
  const [field, meta, { setValue, setTouched }] = useField(name ?? 'TextField');
  const [focused, setFocused] = React.useState(false);
  const text = field?.value || '';
  const values = text.split('');
  const { number = 6, type = 'number', autoFocus = true } = config;
  const refInput = React.useRef<any>();

  const CODE_LENGTH = new Array(number).fill(0);

  const handleClick = e => {
    e?.preventDefault();

    if (refInput.current) {
      refInput.current.focus();
      setFocused(true);
    }
  };

  const handleBlur = e => {
    setFocused(false);
    field.onBlur(e);
    setTouched(true);
  };

  const updateValue = data => {
    const result: any = data ? data.toString() : undefined;

    setValue(result);
  };

  const handleChange = e => {
    e?.stopPropagation();

    const _value = e.target.value;

    if (text.length >= CODE_LENGTH.length) return null;

    const data = (text + _value).slice(0, CODE_LENGTH.length);
    updateValue(data);
  };

  const handleKeyUp = e => {
    if (e.key === 'Backspace') {
      const data = text.slice(0, text.length - 1);

      updateValue(data);
    }
  };

  const selectedIndex =
    values.length < CODE_LENGTH.length ? values.length : CODE_LENGTH.length - 1;

  const hideInput = !(values.length <= CODE_LENGTH.length);

  const firstAndLast = values.length === 0 ? 0 : 8;

  const haveError = Boolean(
    meta.error && (focused || meta.touched || formik.submitCount)
  );

  return (
    <FormControl data-testid={camelCase(`field ${name}`)}>
      <RootStyled>
        <Wrapper onClick={handleClick}>
          <Box sx={{ display: 'flex', position: 'relative' }}>
            {CODE_LENGTH.map((v, index) => {
              return (
                <ItemInput
                  key={index}
                  className={CODE_LENGTH.length - 1 === index && 'last-item'}
                >
                  {values[index]}
                </ItemInput>
              );
            })}
            <InputStyled
              id={`input-${values.length}`}
              focused={focused}
              lastItem={values.length === CODE_LENGTH.length}
              value=""
              ref={refInput}
              onBlur={handleBlur}
              onChange={handleChange}
              type={type}
              autoFocus={autoFocus}
              style={{
                top: '0px',
                bottom: '0px',
                left: `${selectedIndex * (48 + firstAndLast)}px`,
                opacity: hideInput ? 0 : 1
              }}
              onKeyUp={handleKeyUp}
            />
          </Box>
          <WrapperError>
            {haveError ? <ErrorMessage error={meta.error} /> : null}
          </WrapperError>
        </Wrapper>
      </RootStyled>
    </FormControl>
  );
};

export default NumberCode;
