import React from 'react';
import useTourGuideContext, { useGetTourGuide } from '@metafox/tourguide/hooks';
import { StatusTourGuide } from '@metafox/tourguide/types';
import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Box, Button, styled, Typography } from '@mui/material';
import { isEmpty } from 'lodash';
import {
  findElementAndRemoveClass,
  removeStyleElementSelected
} from '@metafox/tourguide/utils';

const name = 'ContentDock';

const ItemContent = styled(Box, { name, slot: 'ItemContent' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  cursor: 'pointer'
}));

const IconItem = styled(Box, {
  name,
  slot: 'IconItem'
})(({ theme }) => ({
  width: 24,
  height: 24,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  transform: 'translate(-4px, 0)',
  '& .ico': {
    fontSize: theme.mixins.pxToRem(16)
  }
}));

const WrapperButton = styled(Box, { name, slot: 'WrapperButton' })(
  ({ theme }) => ({
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    padding: theme.spacing(1, 2),
    '&>button:not(:first-of-type)': {
      marginLeft: theme.spacing(1)
    }
  })
);

const ButtonStyled = styled(Button, { name, slot: 'ButtonStyle' })(
  ({ theme }) => ({
    textTransform: 'uppercase'
  })
);

function PreventClickAfterDragButton({
  children,
  onClick: onClickProp,
  ...rest
}) {
  const [isDragging, setIsDragging] = React.useState(false);
  const dragStart = React.useRef({ x: 0, y: 0 });

  const onMouseDown = e => {
    dragStart.current = { x: e.clientX, y: e.clientY };
    setIsDragging(false);
  };

  const onMouseMove = e => {
    const dx = Math.abs(e.clientX - dragStart.current.x);
    const dy = Math.abs(e.clientY - dragStart.current.y);

    if (dx > 1 || dy > 1) {
      setIsDragging(true);
    }
  };

  const onClick = e => {
    if (isDragging) {
      e.preventDefault();
      e.stopPropagation();

      return;
    }

    onClickProp(e);
  };

  return (
    <ItemContent
      {...rest}
      onMouseDown={onMouseDown}
      onMouseMove={onMouseMove}
      onClick={onClick}
    >
      {children}
    </ItemContent>
  );
}

function Content({ menu, isCreateStep }) {
  const { i18n, dispatch, useGetItems } = useGlobal();
  const { fire, status, tourId } = useTourGuideContext();
  const item = useGetTourGuide(tourId);
  const steps = useGetItems(item?.steps);

  const handleResetDock = () => {
    if (!isEmpty(item)) {
      steps.map((step: any) => removeStyleElementSelected(step?.element));
    }

    findElementAndRemoveClass('tourguide-selected');

    fire({
      type: 'resetDock'
    });
  };

  const handleSuccess = value => {
    fire({
      type: 'setUpdate',
      payload: {
        tourId: value?.id
      }
    });
  };

  const handleClickAction = React.useCallback(
    (e, item) => {
      e.stopPropagation();
      e.preventDefault();

      dispatch({
        type: item?.value,
        payload: {
          data: { ...item?.params?.payload }
        },
        meta: {
          onSuccess: handleSuccess,
          onCancel: handleResetDock
        }
      });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [fire, handleSuccess, handleResetDock]
  );

  const handleComplete = () => {
    dispatch({
      type: 'tourguide/markAsActive',
      payload: { id: tourId },
      meta: {
        onSuccess: handleResetDock,
        onCancel: handleResetDock
      }
    });
  };

  if (status === StatusTourGuide.No && !isEmpty(menu)) {
    return menu?.map((item, index) => (
      <PreventClickAfterDragButton
        key={index}
        onClick={e => handleClickAction(e, item)}
      >
        <IconItem>
          <LineIcon icon={item?.icon} />
        </IconItem>
        <Typography variant="subtitle2" fontWeight={400}>
          {item?.label ? i18n.formatMessage({ id: item?.label }) : null}
        </Typography>
      </PreventClickAfterDragButton>
    ));
  }

  if (isCreateStep) {
    return (
      <WrapperButton>
        <ButtonStyled size="small" variant="contained" onClick={handleComplete}>
          {i18n.formatMessage({ id: 'tourguide_create_complete' })}
        </ButtonStyled>
        <ButtonStyled size="small" variant="outlined" onClick={handleResetDock}>
          {i18n.formatMessage({ id: 'tourguide_create_cancel' })}
        </ButtonStyled>
      </WrapperButton>
    );
  }

  return null;
}

export default Content;
