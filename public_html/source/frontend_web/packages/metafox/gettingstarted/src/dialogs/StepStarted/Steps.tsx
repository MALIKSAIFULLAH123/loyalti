/**
 * @type: dialog
 * name: gettingStarted.dialog.steps
 */

import { Link, useGetItem, useGetItems, useGlobal } from '@metafox/framework';
import { Box, Button, styled } from '@mui/material';
import React from 'react';
import { DialogContent, DialogTitle, Dialog } from '@metafox/dialog';
import StepGettingStarted from './Step';
import {
  usePagingDataTodoList,
  useStepListing
} from '@metafox/gettingstarted/hooks';

const name = 'stepGettingstarted';

const ActionStep = styled(Box, {
  name,
  slot: 'actionStep'
})(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  backgroundColor: theme.palette.background.paper,
  [theme.breakpoints.down('sm')]: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    width: '100%',
    padding: theme.spacing(1)
  }
}));

export default function StepsGettingStarted({ currentStep }) {
  const { useDialog, i18n, dispatch, jsxBackend } = useGlobal();
  const { dialogProps, closeDialog } = useDialog();
  const { data } = useStepListing();
  const listStep = useGetItems(data);
  const [step, setStep] = React.useState(currentStep);
  const dataTodoList = usePagingDataTodoList();
  const { ids, pagesOffset } = dataTodoList;
  const { total } = pagesOffset;
  const itemDataTodoList = useGetItem(ids[step - 1]);

  const itemStep = React.useMemo(
    () => itemDataTodoList || listStep.find(item => item.ordering === step),
    [itemDataTodoList, listStep, step]
  );

  const previousStep = () => {
    setStep(prev => prev - 1);
  };

  const nextStep = () => {
    setStep(prev => prev + 1);
  };

  const openListTodo = () => {
    closeDialog();
    dispatch({ type: 'getting-started/gettingStarted' });
  };

  return (
    <Dialog {...dialogProps} maxWidth="md" fullWidth>
      <DialogTitle>
        <Link onClick={openListTodo} color="inherit" sx={{ cursor: 'pointer' }}>
          {i18n.formatMessage({ id: 'getting_started' })}
        </Link>
      </DialogTitle>
      <DialogContent>
        {!itemStep ? (
          jsxBackend.render({ component: 'form.DefaultLoading' })
        ) : (
          <>
            <StepGettingStarted item={itemStep} />
            <ActionStep>
              <Button
                variant="text"
                color="inherit"
                onClick={previousStep}
                disabled={step === 1}
              >
                {i18n.formatMessage({ id: 'previous' })}
              </Button>
              <Box>
                {i18n.formatMessage(
                  { id: 'pagination_current_of_total_page' },
                  {
                    current: step,
                    total
                  }
                )}
              </Box>
              {total === step ? (
                <Button variant="text" color="inherit" onClick={closeDialog}>
                  {i18n.formatMessage({ id: 'explore' })}
                </Button>
              ) : (
                <Button
                  variant="text"
                  color="inherit"
                  onClick={nextStep}
                  disabled={total === step}
                >
                  {i18n.formatMessage({ id: 'next' })}
                </Button>
              )}
            </ActionStep>
          </>
        )}
      </DialogContent>
    </Dialog>
  );
}
