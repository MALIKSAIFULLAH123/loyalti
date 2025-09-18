import React from 'react';
import useTourGuideContext from '@metafox/tourguide/hooks';
import { StepItemType, TourGuideItemShape } from '@metafox/tourguide';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, styled, Tooltip } from '@mui/material';
import { isEmpty } from 'lodash';
import PlayPauseAction from './PlayPauseAction';

const name = 'ContentDock';

export const IconBtn = styled('div', {
  name,
  slot: 'IconBtn',
  shouldForwardProp: props => props !== 'disabled' && props !== 'isPlaying'
})<{ disabled?: boolean; isPlaying?: boolean }>(
  ({ theme, disabled, isPlaying }) => ({
    width: 40,
    height: 40,
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    cursor: disabled ? 'default' : 'pointer',
    '& .ico': {
      fontSize: theme.mixins.pxToRem(18),
      fontWeight: theme.typography.fontWeightBold,
      color: disabled
        ? `${theme.palette.action.disabled} !important`
        : theme.palette.text.primary
    },
    ...(!disabled && {
      ':hover': {
        backgroundColor: theme.palette.action.hover,
        borderRadius: theme.shape.borderRadius * 1.5
      }
    }),
    ...(isPlaying && {
      backgroundColor: theme.palette.action.hover,
      borderRadius: theme.shape.borderRadius * 1.5
    })
  })
);

const ActionList = styled(Box, {
  name,
  slot: 'ActionList'
})<{ colorItem?: string; disabled?: boolean }>(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  padding: theme.spacing(1)
}));

interface Props {
  tourData?: TourGuideItemShape;
  data: StepItemType;
  handleDraw?: (
    step?: number,
    total?: number,
    direction?: 'next' | 'prev'
  ) => {};
  onClose: (hasConfirm?: boolean) => void;
}

function ActionListBlock({ tourData, data, handleDraw, onClose }: Props) {
  const { i18n } = useGlobal();
  const { step, totalStep, initialStep } = useTourGuideContext();

  const disablePrev = step <= initialStep;
  const isLast = step === totalStep - 1;

  if (isEmpty(data)) return null;

  const handlePrevStep = () => {
    if (disablePrev) return;

    handleDraw(step - 1, totalStep, 'prev');
  };

  const handleNextStep = () => {
    if (isLast) {
      onClose(true);

      return;
    }

    handleDraw(step + 1);
  };

  const handleRestart = () => {
    handleDraw(0);
  };

  const handleSkip = () => {
    onClose();
  };

  return (
    <ActionList>
      <Tooltip
        title={
          disablePrev ? '' : i18n.formatMessage({ id: 'tourguide_prev_step' })
        }
      >
        <IconBtn disabled={disablePrev} onClick={handlePrevStep}>
          <LineIcon aria-disabled={disablePrev} icon="ico-angle-left" />
        </IconBtn>
      </Tooltip>
      <Tooltip title={i18n.formatMessage({ id: 'tourguide_next_step' })}>
        <IconBtn onClick={handleNextStep}>
          <LineIcon icon="ico-angle-right" />
        </IconBtn>
      </Tooltip>
      {tourData?.is_auto ? (
        <PlayPauseAction handleDraw={handleDraw} onClose={onClose} />
      ) : null}
      <Tooltip title={i18n.formatMessage({ id: 'tourguide_restart' })}>
        <IconBtn onClick={handleRestart}>
          <LineIcon icon="ico-refresh-o" />
        </IconBtn>
      </Tooltip>
      <Tooltip title={i18n.formatMessage({ id: 'tourguide_skip' })}>
        <IconBtn onClick={handleSkip}>
          <LineIcon icon="ico-check" />
        </IconBtn>
      </Tooltip>
    </ActionList>
  );
}

export default ActionListBlock;
