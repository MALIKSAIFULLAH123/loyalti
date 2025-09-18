import {
  HistoryState,
  IS_ADMINCP,
  LAYOUT_EDITOR_TOGGLE,
  useGlobal,
  useLocation
} from '@metafox/framework';
import { Box, styled } from '@mui/material';
import { isEmpty, isNil } from 'lodash';
import React, {
  useCallback,
  useMemo,
  useReducer,
  useRef,
  useState
} from 'react';
import {
  ActionTourType,
  PositionType,
  StatusTourGuide,
  TourGuideSettingType,
  TourGuideStep
} from '@metafox/tourguide/types';
import {
  ID_TOURGUIDE_DOCK,
  TOURGUIDE_NEW_STEP
} from '@metafox/tourguide/constants';
import {
  initStateTourGuide,
  reducerTourGuide,
  TourGuideContext
} from '@metafox/tourguide/context';
import { HeaderDock, StyleTourGuide } from '@metafox/tourguide/components';
import Content from './Content';
import { useGetTourGuide, useStatusCreate } from '@metafox/tourguide/hooks';
import {
  fadeInLeftAnimation,
  fadeInRightAnimation,
  removeStyleElementSelected
} from '@metafox/tourguide/utils';
import { transformPositionStyle } from '@metafox/tourguide/utils/drag';
import loadable from '@loadable/component';
import { DraggableData } from 'react-draggable';

// cut off 60kb from bundle.
const Draggable = loadable(
  () => import(/* webpackChunkName: "reactDraggable" */ 'react-draggable')
);

const name = 'TourGuideDock';
const widthContent = 160;
const heightContent = 48;

const DockContainer = styled(Box, {
  name,
  slot: 'DockContainer',
  shouldForwardProp: props =>
    props !== 'fadeInRight' &&
    props !== 'fadeInLeft' &&
    props !== 'isCreateStep'
})<{ fadeInLeft?: boolean; fadeInRight?: boolean; isCreateStep?: boolean }>(
  ({ theme, fadeInLeft, fadeInRight, isCreateStep }) => ({
    position: 'fixed',
    zIndex: 1400,
    ...(isCreateStep
      ? {
          minWidth: 175,
          maxWidth: 400,
          height: 'auto'
        }
      : {
          width: widthContent,
          height: heightContent
        }),
    ...(fadeInLeft && {
      WebkitAnimationName: fadeInLeftAnimation,
      animationName: fadeInLeftAnimation,
      animationDuration: '1.35s',
      WebkitAnimationDuration: '1.35s',
      animationFillMode: 'both',
      WebkitAnimationFillMode: 'both'
    }),
    ...(fadeInRight && {
      WebkitAnimationName: fadeInRightAnimation,
      animationName: fadeInRightAnimation,
      animationDuration: '1.35s',
      WebkitAnimationDuration: '1.35s',
      animationFillMode: 'both',
      WebkitAnimationFillMode: 'both'
    })
  })
);

const WrapperTransform = styled(Box, {
  name,
  slot: 'WrapperTransform',
  shouldForwardProp: props => props !== 'transformStyle'
})<{ transformStyle?: string }>(({ theme, transformStyle }) => ({
  ...(transformStyle && { transform: transformStyle })
}));

const WrapperRotation = styled(Box, {
  name,
  slot: 'container',
  shouldForwardProp: props => props !== 'rotation'
})<{ rotation?: number }>(({ theme, rotation = 0 }) => ({
  transform: `rotate(${rotation}deg)`,
  transformOrigin: 'center',
  width: '100%',
  height: '100%',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center'
}));

const WraperContainer = styled(Box, {
  name,
  slot: 'WraperContainer',
  shouldForwardProp: props => props !== 'isCreateStep'
})<{ isCreateStep?: boolean }>(({ theme, isCreateStep }) => ({
  boxShadow: theme.shadows[1],
  background: theme.palette.primary.main,
  color: theme.palette.primary.contrastText,
  borderRadius: theme.shape.borderRadius * 1.5,
  width: '100%',
  cursor: 'move',
  ...(isCreateStep && {
    background: theme.palette.background.paper,
    borderRadius: theme.shape.borderRadius * 1.25,
    color: theme.palette.text.primary
  })
}));

const WrapperContent = styled(Box, {
  name,
  slot: 'WrapperContent',
  shouldForwardProp: props => props !== 'isCreateStep'
})<{ isCreateStep?: boolean }>(({ theme, isCreateStep }) => ({
  padding: theme.spacing(1.5, 2),
  ...(isCreateStep && {
    padding: theme.spacing(1, 0)
  })
}));

const initialActionTour: ActionTourType = {
  tourguide_id: null,
  menu: []
};

export default function TourGuideDock() {
  const {
    getSetting,
    dispatch,
    jsxBackend,
    cookieBackend,
    useIsMobile,
    useGetItems,
    localStore,
    useLoggedIn
  } = useGlobal();
  const loggedIn = useLoggedIn();
  const [data, setTourData] = React.useState<ActionTourType>(initialActionTour);
  const location = useLocation<HistoryState>();
  const { key } = location;
  const [loading, setLoading] = React.useState(false);
  const isTablet = useIsMobile(true);
  const isEditMode = localStore.get(LAYOUT_EDITOR_TOGGLE);
  const contentRef = React.useRef<HTMLDivElement>();
  const [positionDrag, setPositionDrag] = useState<DraggableData>();
  const [transformStyle, setTransformStyle] = useState<string | null>(null);
  const [draggable, setDraggable] = React.useState<boolean>(false);
  const refResize = useRef<ResizeObserver>();

  const tourguideApp: TourGuideSettingType = getSetting('tourguide');

  const SelectedEleComponent = jsxBackend.get('tourguide.ui.selectedElement');
  const PlayTourComponent = jsxBackend.get('tourguide.ui.playTourGuide');

  const [state, fire] = useReducer(reducerTourGuide, initStateTourGuide);

  const { createStep, status, tourId, isMoveDock, hasDragDock } = state || {};

  const position: PositionType = React.useMemo(
    () =>
      tourguideApp?.tour_guide_button?.position ?? {
        top: '60px',
        right: '0px'
      },
    [tourguideApp]
  );
  const rotation = useMemo(
    () => tourguideApp?.tour_guide_button?.rotation || 0,
    [tourguideApp]
  );

  const onResize = useCallback(() => {
    const contentRect = contentRef.current?.getBoundingClientRect();

    setTransformStyle(
      transformPositionStyle(
        position,
        {
          width: widthContent,
          height: contentRect?.height || heightContent
        },
        rotation
      )
    );
  }, [position, rotation]);

  const enableDrag = React.useCallback(() => {
    setDraggable(true);
  }, []);

  const disableDrag = React.useCallback(() => {
    setDraggable(false);
  }, []);

  const onDraggable = React.useCallback(
    (e: Event, data: DraggableData) => {
      if (isMoveDock) return;

      fire({
        type: 'setMoveDock',
        payload: true
      });

      setPositionDrag(data);
    },
    [isMoveDock]
  );

  const onStopDraggable = useCallback((e: Event, data: DraggableData) => {
    fire({
      type: 'setMoveDock',
      payload: false
    });

    setPositionDrag(data);
  }, []);

  React.useEffect(() => {
    if (!contentRef.current) return;

    refResize.current = new ResizeObserver(() => {
      onResize();
    });

    refResize.current?.observe(contentRef.current);

    return () => refResize?.current.disconnect(); // clean up
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [onResize, contentRef.current]);

  const fadeInLeft = useMemo(() => !isNil(position?.left), [position?.left]);
  const fadeInRight = useMemo(() => !isNil(position?.right), [position?.right]);

  const isCreateStep = React.useMemo(
    () =>
      createStep === TourGuideStep.SelectElement ||
      createStep === TourGuideStep.InputInfoStep,
    [createStep]
  );

  const item = useGetTourGuide(tourId);
  const steps = useGetItems(item?.steps);

  const {
    tourguide_id,
    status: statusTourguide,
    createStep: createStepStatus
  } = useStatusCreate();

  const onDone = ({ data = initialActionTour }) => {
    setTourData(data);
    setLoading(false);
  };

  React.useEffect(() => {
    if (!key) return;

    setLoading(true);
    dispatch({ type: 'tourguide/getActions', meta: { onDone, fire } });
    fire({
      type: 'setUpdate',
      payload: {
        status: StatusTourGuide.No
      }
    });

    return () => {
      fire({
        type: 'resetDock'
      });
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [key]);

  React.useEffect(
    () => {
      const localTourData: any = cookieBackend.getJSON(TOURGUIDE_NEW_STEP);
      const tour_id =
        statusTourguide === StatusTourGuide.Create ? tourguide_id : undefined;

      if (!tour_id && !localTourData?.tour_guide_id) return;

      fire({
        type: 'setUpdate',
        payload: {
          createStep: TourGuideStep.SelectElement,
          status: StatusTourGuide.Create,
          tourId: tour_id || localTourData?.tour_guide_id
        }
      });

      if (localTourData?.tour_guide_id) {
        dispatch({
          type: 'tourguide/newStepLocalStore',
          payload: { data: { id: localTourData?.tour_guide_id } }
        });
        cookieBackend.remove(TOURGUIDE_NEW_STEP);
      }
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [fire, tourguide_id, statusTourguide]
  );

  React.useEffect(
    () => {
      if (!statusTourguide && !createStepStatus && !tourguide_id) return;

      fire({
        type: 'setUpdate',
        payload: {
          tourId: tourguide_id,
          createStep: createStepStatus,
          status: statusTourguide
        }
      });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [fire, createStepStatus, statusTourguide, tourguide_id]
  );

  if (isEditMode || loading || isTablet || IS_ADMINCP || isEmpty(tourguideApp))
    return;

  if (
    status === StatusTourGuide.Hidden ||
    createStep === TourGuideStep.Tour ||
    (status !== StatusTourGuide.Create && isEmpty(data?.menu))
  )
    return null;

  const handleSuccessClose = () => {
    if (!isEmpty(item)) {
      steps.map((step: any) => removeStyleElementSelected(step?.element));
    }

    fire({
      type: 'setUpdate',
      payload: {
        status: StatusTourGuide.Hidden
      }
    });
  };

  const handleHideTour = (id = tourId, hasConfirm = true) => {
    dispatch({
      type: 'tourguide/hideItem',
      payload: { hasConfirm, data: { id } },
      meta: {
        onSuccess: handleSuccessClose
      }
    });
  };

  const handleCloseDock = (hasConfirmCloseProps = true) => {
    const hasConfirmClose = loggedIn ? hasConfirmCloseProps : false;
    const tourguide_id = data?.tourguide_id;

    if (!tourguide_id) {
      handleSuccessClose();

      return;
    }

    handleHideTour(tourguide_id, hasConfirmClose);
  };

  return (
    <TourGuideContext.Provider value={{ ...state, fire }}>
      <StyleTourGuide />
      {status === StatusTourGuide.Start ? (
        <PlayTourComponent
          tourGuideId={tourId}
          onStop={handleHideTour}
          onClose={handleCloseDock}
          hasConfirmClose={false}
        />
      ) : (
        <Draggable
          onDrag={onDraggable}
          onMouseLeave={onStopDraggable}
          onStop={onStopDraggable}
          disabled={!draggable}
          position={positionDrag}
        >
          <DockContainer
            {...(!hasDragDock && { fadeInRight, fadeInLeft })}
            {...position}
            isCreateStep={isCreateStep}
            id={ID_TOURGUIDE_DOCK}
          >
            <WrapperTransform
              ref={contentRef}
              transformStyle={isCreateStep ? null : transformStyle}
              {...(!isCreateStep && !transformStyle && { display: 'none' })}
            >
              <WrapperRotation rotation={isCreateStep ? 0 : rotation}>
                <WraperContainer
                  isCreateStep={isCreateStep}
                  onMouseDownCapture={enableDrag}
                  onMouseUp={disableDrag}
                >
                  {isCreateStep ? (
                    <HeaderDock
                      isNewDock
                      color="primary.main"
                      title="tourguide_new_here"
                      onClose={handleCloseDock}
                    />
                  ) : null}
                  <WrapperContent isCreateStep={isCreateStep}>
                    <Content menu={data.menu} isCreateStep={isCreateStep} />
                  </WrapperContent>
                </WraperContainer>
              </WrapperRotation>
              {isCreateStep ? (
                <SelectedEleComponent tourGuideId={tourId} />
              ) : null}
            </WrapperTransform>
          </DockContainer>
        </Draggable>
      )}
    </TourGuideContext.Provider>
  );
}
