/**
 * @type: ui
 * name: app.step.ProcessUpgrade
 * bundle: admincp
 */
import { useUpgradeState } from '@metafox/app/hooks';
import { useGlobal } from '@metafox/framework';
import { useStepNavigate } from '@metafox/ui/steps';
import { Box, CircularProgress } from '@mui/material';
import React from 'react';
import ProcessStep from './ProcessStep';

export default function ProcessSteps() {
  const { dispatch } = useGlobal();
  const [currentIndex, setCurrentIndex] = React.useState(0);
  const [next] = useStepNavigate();
  const { upgradeSteps: steps } = useUpgradeState();

  React.useEffect(() => {
    dispatch({ type: '@app/upgrade/process/start' });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  React.useEffect(() => {
    if (steps.length > 0 && currentIndex >= steps.length) {
      next();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [currentIndex]);

  if (!steps.length) {
    return (
      <Box>
        <CircularProgress variant="indeterminate" />
      </Box>
    );
  }

  return (
    <Box sx={{ minHeight: 300, display: 'flex', flexDirection: 'column' }}>
      {steps.map((step, index) => (
        <ProcessStep
          step={step}
          onSuccess={() => setCurrentIndex(index + 1)}
          key={index.toString()}
          active={currentIndex === index}
        />
      ))}
    </Box>
  );
}
