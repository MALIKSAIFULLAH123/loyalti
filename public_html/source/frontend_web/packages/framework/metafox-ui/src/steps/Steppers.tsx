/**
 * @type: ui
 * name: ui.step.steppers
 */
import React from 'react';
import { StepContent, StepLabel, Step, Stepper } from '@mui/material';
import { useGlobal } from '@metafox/framework';
import { StepsProps } from './types';
import reducerFunction from './reducerFunction';
import StepContext from './Context';

export default function Steppers(_data: StepsProps) {
  const { jsxBackend } = useGlobal();

  const reducer = React.useReducer(reducerFunction, {
    activeStep: 0,
    activeId: _data.steps[0]?.id,
    data: _data
  });

  const [{ data, activeStep }] = reducer;

  if (!data?.steps) return null;

  return (
    <StepContext.Provider value={reducer}>
      <Stepper activeStep={activeStep} orientation="vertical">
        {data.steps.map(step => {
          return (
            <Step
              key={step.id}
              expanded={step.expanded}
              disabled={step.disabled}
            >
              <StepLabel>{step.title}</StepLabel>
              <StepContent>
                {jsxBackend.render({
                  component: step.component,
                  props: step.props
                })}
              </StepContent>
            </Step>
          );
        })}
      </Stepper>
    </StepContext.Provider>
  );
}
