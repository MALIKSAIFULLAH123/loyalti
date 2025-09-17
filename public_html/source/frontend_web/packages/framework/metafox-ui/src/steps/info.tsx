/**
 * @type: ui
 * name: ui.step.info
 * bundle: admincp
 */

import React from 'react';
import { Box, Button } from '@mui/material';
import useStepNavigate from './useStepNavigate';

export default function UiStepInfo({ html, submitLabel, hasSubmit }) {
  const [next] = useStepNavigate();
  const [clicked, setClicked] = React.useState(false);

  const handleClick = () => {
    next();
    setClicked(true);
  };

  React.useEffect(() => {
    if (!hasSubmit) {
      next();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <Box>
      <Box>
        <div dangerouslySetInnerHTML={{ __html: html }}></div>
      </Box>
      {hasSubmit ? (
        <Box sx={{ pt: 2 }}>
          <Button
            disabled={clicked}
            onClick={handleClick}
            variant="contained"
            color="primary"
            size="small"
          >
            {submitLabel ?? 'Continue'}
          </Button>
        </Box>
      ) : null}
    </Box>
  );
}
