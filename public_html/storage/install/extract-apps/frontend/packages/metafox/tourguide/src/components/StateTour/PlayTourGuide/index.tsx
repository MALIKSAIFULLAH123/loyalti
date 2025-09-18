/**
 * @type: ui
 * name: tourguide.ui.playTourGuide
 */

import React from 'react';
import useTourGuideContext, { useGetTourGuide } from '@metafox/tourguide/hooks';
import { useGlobal } from '@metafox/framework';
import { isEmpty } from 'lodash';
import { TourOverlay, HeaderDock } from '@metafox/tourguide/components';
import { Box, Grow, Paper, Popper, styled } from '@mui/material';
import { ScrollContainer } from '@metafox/layout';
import ActionList from './ActionList';
import {
  isBoundSmallerView,
  isElementFixed,
  isElementInViewport,
  scrollToElementIfNeeded
} from '@metafox/tourguide/utils';
import {
  MIN_HEIGHT_PLAYTOUR,
  MIN_WIDTH_PLAYTOUR
} from '@metafox/tourguide/constants';
import { HtmlViewerWrapper } from '@metafox/ui';
import HtmlViewer from '@metafox/html-viewer';
import { StepItemType, TourGuideItemShape } from '@metafox/tourguide/types';

const name = 'playTourGuide';

const PopperStyled = styled(Popper, { name, slot: 'PopperStyled' })(
  ({ theme }) => ({
    width: MIN_WIDTH_PLAYTOUR,
    zIndex: 1300
  })
);

const WrapperContainer = styled(Box, { name, slot: 'WrapperContainer' })(
  ({ theme }) => ({})
);

const WrapperContent = styled(Box, {
  name,
  slot: 'WrapperContent',
  shouldForwardProp: props =>
    props !== 'colorItem' && props !== 'backgroundItem'
})<{ colorItem?: string; backgroundItem?: string }>(
  ({ theme, colorItem, backgroundItem }) => ({
    padding: theme.spacing(2),
    color: colorItem ? colorItem : theme.palette.text.primary,
    backgroundColor: backgroundItem
      ? backgroundItem
      : theme.palette.background.paper
  })
);

interface Props {
  tourGuideId?: string;
  title?: string;
  onClose?: (hasConfirm?: boolean) => void;
  onStop?: () => void;
  hasConfirmClose?: boolean;
}

const transformOriginMap = {
  'bottom-start': 'top left',
  bottom: 'top center',
  'bottom-end': 'top right',
  'top-start': 'bottom left',
  top: 'bottom center',
  'top-end': 'bottom right',
  'right-start': 'top left',
  right: 'center left',
  'right-end': 'bottom left',
  'left-start': 'top right',
  left: 'center right',
  'left-end': 'bottom right'
};

function PlayTourGuide({
  tourGuideId,
  onStop,
  onClose,
  hasConfirmClose
}: Props) {
  const { useGetItems } = useGlobal();
  const { fire, step, totalStep: totalStepContext } = useTourGuideContext();
  const initialStepRef = React.useRef(undefined);
  const directionRef = React.useRef('next');

  const item = useGetTourGuide(tourGuideId);

  const steps = useGetItems(item?.steps);
  const stepItem: TourGuideItemShape = steps[step];

  const eleSelectorRef = React.useRef<HTMLElement | null>();
  const [elementSelector, setElementSelector] = React.useState(null);
  const refDevOverlay = React.useRef(null);

  const timeoutRef = React.useRef<any>(null);

  const createOverlay = (closestElement, isScroll = false) => {
    clearTimeout(timeoutRef.current);
    const rect = closestElement.getBoundingClientRect();
    const isFixed = isElementFixed(closestElement);
    const scrollY = isFixed ? 0 : window.pageYOffset || window.scrollY || 0;

    const activeOverlay = !!refDevOverlay.current;
    const overlayDiv = refDevOverlay.current || document.createElement('div');
    overlayDiv.className = 'tourguide-overlay';
    overlayDiv.style.position = isFixed ? 'fixed' : 'absolute';
    overlayDiv.style.top = `${rect.top + scrollY}px`;
    overlayDiv.style.left = `${rect.left}px`;
    overlayDiv.style.width = `${rect.width}px`;
    overlayDiv.style.height = `${rect.height}px`;
    overlayDiv.style.zIndex = 1300;
    overlayDiv.style.boxShadow = '0 0 0 9999px rgba(0,0,0,0.8)';
    overlayDiv.style.borderRadius = '8px';
    overlayDiv.style.pointerEvents = 'none';
    overlayDiv.style.transition = isScroll ? 'none' : 'all 0.3s ease';

    if (!activeOverlay) {
      document.body.appendChild(overlayDiv);
    }

    refDevOverlay.current = overlayDiv;
  };

  const removeOverlay = () => {
    timeoutRef.current = setTimeout(() => {
      if (refDevOverlay.current) {
        refDevOverlay.current.remove();
        refDevOverlay.current = null;
      }
    }, 0);
  };

  const drawStepElement = (index, totalStep = totalStepContext, direction) => {
    // remove style prev step
    if (eleSelectorRef.current) {
      eleSelectorRef.current.classList.remove('tourguide-selected');
      removeOverlay();
    }

    const item: StepItemType = steps[index];

    const eleSelector: HTMLElement = document.querySelector(item?.element);

    eleSelectorRef.current = eleSelector;

    if (!eleSelector) {
      if (totalStep === 1 || index === totalStep) {
        onClose();
      } else {
        directionRef.current = direction || 'next';
        const num = directionRef.current === 'next' ? 1 : -1;

        if (initialStepRef.current === undefined) {
          fire({
            type: 'setUpdate',
            payload: {
              initialStep: index + num
            }
          });
        }

        fire({
          type: 'setStep',
          payload: {
            step: index + num
          }
        });
      }

      return;
    }

    fire({
      type: 'setStep',
      payload: {
        step: index
      }
    });
    initialStepRef.current = index;
    directionRef.current = 'next';

    eleSelector?.classList.add('tourguide-selected');
    setElementSelector(eleSelector);
    scrollToElementIfNeeded(eleSelector);
    createOverlay(eleSelector);
  };

  React.useEffect(() => {
    if (isEmpty(steps)) return;

    fire({
      type: 'setUpdate',
      payload: {
        totalStep: steps?.length,
        steps,
        step: 0
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [steps]);

  React.useEffect(() => {
    if (isEmpty(steps)) return;

    drawStepElement(step, steps?.length, directionRef.current);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [stepItem, step, steps, totalStepContext]);

  React.useEffect(() => {
    if (!elementSelector) return;

    const onScroll = () => createOverlay(elementSelector, true);

    window.addEventListener('scroll', onScroll);

    return () => {
      window.removeEventListener('scroll', onScroll);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [elementSelector]);

  React.useEffect(() => {
    return () => {
      initialStepRef.current = true;
      fire({
        type: 'resetStart'
      });

      if (eleSelectorRef.current) {
        eleSelectorRef.current.classList.remove('tourguide-selected');
        removeOverlay();
      }
    };
  }, []);

  const popperProps = React.useMemo(() => {
    const isInView = isElementInViewport(elementSelector);

    const isSmallerView = isBoundSmallerView(
      elementSelector,
      MIN_HEIGHT_PLAYTOUR,
      MIN_WIDTH_PLAYTOUR
    );
    const anchorEl = {
      anchorEl: elementSelector,
      placement: 'bottom',
      anchorOrigin: {
        vertical: 'center',
        horizontal: 'center'
      },
      transformOrigin: {
        vertical: 'center',
        horizontal: 'center'
      }
    };

    const anchorNoEl = {
      placement: 'auto',
      anchorEl: elementSelector
    };

    if (isInView) {
      if (isSmallerView) {
        return anchorEl;
      }

      return anchorNoEl;
    }

    // outView
    if (isSmallerView) {
      return anchorEl;
    }

    return anchorNoEl;
  }, [elementSelector]);

  if (isEmpty(steps) || !elementSelector) return null;

  return (
    <>
      <TourOverlay />
      <PopperStyled
        key={stepItem?.id}
        open={!!eleSelectorRef.current}
        data-testid="selectElement"
        popperOptions={{
          strategy: 'fixed',
          modifiers: [
            {
              name: 'offset',
              options: {
                offset: () => {
                  return [5, 5];
                }
              }
            },
            {
              name: 'preventOverflow',
              enabled: true,
              options: {
                altAxis: true,
                padding: 8
              }
            },
            {
              name: 'flip',
              enabled: true,
              options: {
                padding: 8
              }
            }
          ]
        }}
        {...popperProps}
        transition
      >
        {({ TransitionProps, placement }) => (
          <Grow
            {...TransitionProps}
            in={!!eleSelectorRef.current}
            timeout={600}
            style={{ transformOrigin: transformOriginMap[placement] }}
          >
            <Paper sx={{ overflow: 'hidden' }}>
              <Grow
                in
                timeout={600}
                style={{ transformOrigin: transformOriginMap[placement] }}
              >
                <Box>
                  <ScrollContainer autoHide autoHeight autoHeightMax={'50vh'}>
                    <WrapperContainer>
                      <WrapperContent
                        colorItem={stepItem?.font_color}
                        backgroundItem={stepItem?.background_color}
                      >
                        <HeaderDock
                          title={stepItem?.title}
                          onClose={onStop}
                          hasConfirmClose={hasConfirmClose}
                          color={stepItem?.font_color ?? 'text.primary'}
                          showFull
                          sx={{ p: 0 }}
                        />
                        {stepItem?.desc ? (
                          <HtmlViewerWrapper mt={0}>
                            <HtmlViewer html={stepItem?.desc} />
                          </HtmlViewerWrapper>
                        ) : null}
                      </WrapperContent>
                    </WrapperContainer>
                  </ScrollContainer>
                  <ActionList
                    tourData={item}
                    data={stepItem}
                    handleDraw={drawStepElement}
                    onClose={onClose}
                  />
                </Box>
              </Grow>
            </Paper>
          </Grow>
        )}
      </PopperStyled>
    </>
  );
}

export default PlayTourGuide;
