/**
 * @type: formElement
 * name: form.element.Progress
 * chunkName: formBasic
 */
import { FormControl, Step, StepLabel, Stepper } from '@mui/material';
import { camelCase, range } from 'lodash';
import React from 'react';
import { FormFieldProps } from '@metafox/form';
import { styled } from '@mui/material/styles';

const name = 'Progress';

const StepLabelStyled = styled(StepLabel, {
  name,
  slot: 'StepLabel',
  shouldForwardProp: props => props !== 'active'
})<{ active?: boolean }>(({ theme, active }) => ({
  ...(active && {
    '& .MuiStepLabel-label.Mui-active.MuiStepLabel-alternativeLabel': {
      color: theme.palette.primary.main,
      fontSize: theme.mixins.pxToRem(14)
    }
  })
}));

function Progress({
  config,
  name,
  disabled: forceDisabled,
  formik
}: FormFieldProps) {
  const {
    margin = 'normal',
    fullWidth,
    sxFieldWrapper,
    sx,
    labelSteps = {}
  } = config;
  const { current_step, total_step } = formik?.values || {};

  return (
    <FormControl
      margin={margin}
      fullWidth={fullWidth}
      data-testid={camelCase(`field ${name}`)}
      sx={sxFieldWrapper}
    >
      <Stepper alternativeLabel sx={sx}>
        {range(total_step).map(index => {
          const stepNumber = index + 1;

          return (
            <Step
              completed={stepNumber < current_step}
              active={stepNumber === current_step}
              key={index.toString()}
            >
              <StepLabelStyled active={stepNumber === current_step}>
                {labelSteps[stepNumber]}
              </StepLabelStyled>
            </Step>
          );
        })}
      </Stepper>
    </FormControl>
  );
}
export default Progress;
