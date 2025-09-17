/**
 * @type: ui
 * name: ui.step.processes
 */
import { useGlobal } from '@metafox/framework';
import { useStepNavigate } from '@metafox/ui/steps';
import { Box } from '@mui/material';
import React from 'react';
import ProcessStep from './ProcessStep';

export default function ProcessSteps({ steps, disableNavigateConfirm = true }) {
  const { setNavigationConfirm } = useGlobal();
  const [currentIndex, setCurrentIndex] = React.useState(0);
  const [next] = useStepNavigate();

  React.useEffect(() => {
    if (steps?.length > 0 && currentIndex >= steps?.length) {
      next();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [currentIndex]);

  React.useEffect(() => {
    if (!disableNavigateConfirm)
      window.onbeforeunload = () => {
        return 'Do you want to skip the process';
      };

    if (!disableNavigateConfirm) setNavigationConfirm(true);

    return () => {
      setNavigationConfirm(false);
      window.onbeforeunload = undefined;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  if (!steps?.length) {
    return null;
  }

  return (
    <Box sx={{ minHeight: 100, display: 'flex', flexDirection: 'column' }}>
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
