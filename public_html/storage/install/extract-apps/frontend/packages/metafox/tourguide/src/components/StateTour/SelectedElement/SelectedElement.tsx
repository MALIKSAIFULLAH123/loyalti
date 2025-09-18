/**
 * @type: ui
 * name: tourguide.ui.selectedElement
 */

import { Box, Paper, Popper, styled } from '@mui/material';
import React, { useState } from 'react';
import { FormBuilder, FormSchemaShape } from '@metafox/form';
import { useGetItems, useGlobal, useResourceAction } from '@metafox/framework';
import { getClosestElement, getClosestPath } from '@metafox/tourguide/utils';
import {
  APP_NAME,
  ID_TOURGUIDE_DOCK,
  RESOURCE_TOURGUIDE_STEP,
  STEP_VALUE_KEYS
} from '@metafox/tourguide/constants';
import { ScrollContainer } from '@metafox/layout';
import useTourGuideContext, {
  useGetTourGuide
} from '@metafox/tourguide//hooks';
import { TourGuideStep } from '@metafox/tourguide';
import LoadingComponent from './LoadingComponent';
import { HeaderDock, TourOverlay } from '@metafox/tourguide/components';
import { isEmpty, pick } from 'lodash';
import ErrorPage from '@metafox/core/pages/ErrorPage/Page';

const name = 'TourGuideSelectedElement';

const WIDTH_POPUP = 500;

const PopperStyled = styled(Popper, { name, slot: 'PopperStyled' })(
  ({ theme }) => ({
    width: WIDTH_POPUP,
    zIndex: 1300
  })
);

const checkContainIgnore = target => {
  const elementIgnore = [
    '[data-testid="popupConfirm"]',
    '[data-testid="popupAlert"]'
  ];

  if (target?.closest(elementIgnore?.join(', '))) return true;

  return false;
};

function getRelativeClickPosition(event, element) {
  if (!element) return null;

  const rect = element.getBoundingClientRect();
  const x = event.clientX - rect.left;
  const y = event.clientY - rect.top;

  return { throttleX: x, throttleY: y };
}

interface Props {
  tourGuideId?: string;
}

interface PositionRefType {
  x?: number;
  y?: number;
  element?: HTMLElement;
  throttleX?: number;
  throttleY?: number;
}

const SelectedElement = (props: Props) => {
  const { dispatch, useFetchDetail } = useGlobal();
  const [formSchema, setFormSchema] = useState<FormSchemaShape>();

  const { tourGuideId } = props || {};

  const {
    fire,
    createStep,
    pageParams: pageParamsContext,
    tourId,
    valueStepSubmit,
    isMoveDock
  } = useTourGuideContext();

  const dataSource = useResourceAction(
    APP_NAME,
    RESOURCE_TOURGUIDE_STEP,
    'addItem'
  );

  const pageParams = React.useMemo(
    () => ({
      tour_guide_id: tourGuideId,
      page_name: pageParamsContext?.pageMetaName
    }),
    [tourGuideId, pageParamsContext?.pageMetaName]
  );

  const [data, loading, error] = useFetchDetail({
    dataSource,
    pageParams,
    forceReload: true,
    cacheKey: tourId
  });

  React.useEffect(() => {
    if (data) {
      setFormSchema(data);
    } else {
      setFormSchema(undefined);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [data, pageParams, loading]);

  const tourItem = useGetTourGuide(tourId);
  const tourSteps = useGetItems(tourItem?.steps);

  const [selectedElement, setSelectedElement] = useState<HTMLElement | null>(
    null
  );
  const [selectedElements, setSelectedElements] = useState<
    HTMLElement[] | null
  >([]);
  const [selectorPath, setSelectorPath] = useState(null);
  const positionRef = React.useRef<PositionRefType>({
    x: 0,
    y: 0,
    element: undefined,
    throttleX: 0,
    throttleY: 0
  });

  const hoverElementRef = React.useRef<HTMLElement | null>();

  const popperRef = React.useRef<any>(null);
  const throttleX = positionRef.current?.throttleX;
  const throttleY = positionRef.current?.throttleY;
  const refDevOverlay = React.useRef(null);
  const timeoutRef = React.useRef<any>(null);

  const createOverlay = closestElement => {
    clearTimeout(timeoutRef.current);
    const rect = closestElement.getBoundingClientRect();
    const activeOverlay = !!refDevOverlay.current;
    const overlayDiv = refDevOverlay.current || document.createElement('div');
    overlayDiv.className = 'tourguide-overlay';
    overlayDiv.style.position = 'fixed';
    overlayDiv.style.top = `${rect.top}px`;
    overlayDiv.style.left = `${rect.left}px`;
    overlayDiv.style.width = `${rect.width}px`;
    overlayDiv.style.height = `${rect.height}px`;
    overlayDiv.style.zIndex = 1300;
    overlayDiv.style.boxShadow = '0 0 0 9999px rgba(0,0,0,0.6)';
    overlayDiv.style.borderRadius = '8px';
    overlayDiv.style.pointerEvents = 'none';
    overlayDiv.style.transition = 'all 0.3s ease';

    if (!activeOverlay) {
      document.body.appendChild(overlayDiv);
    }

    refDevOverlay.current = overlayDiv;
  };

  const removeOverlay = (timeout = 500) => {
    timeoutRef.current = setTimeout(() => {
      if (refDevOverlay.current) {
        refDevOverlay.current.remove();
        refDevOverlay.current = null;
      }
    }, timeout);
  };

  const handleElementHover = React.useCallback(
    (e: MouseEvent) => {
      const target = e.target as HTMLElement;

      if (
        isMoveDock ||
        selectedElement ||
        target === document.documentElement ||
        target === document.body ||
        checkContainIgnore(target)
      ) {
        document.body.style.cursor = 'inherit';

        return;
      }

      const { element: closestElement } = getClosestElement(target);

      if (closestElement) {
        closestElement.classList.add('tourguide-hover');
        createOverlay(closestElement);
        hoverElementRef.current = closestElement;
      }
    },
    [selectedElement, isMoveDock]
  );

  const handleElementOut = React.useCallback(
    (e: MouseEvent) => {
      const target = e.target as HTMLElement;

      if (target === document.documentElement || target === document.body)
        return;

      if (selectedElement) return;

      const { element: closestElement } = getClosestElement(target);

      if (closestElement) {
        closestElement.classList.remove('tourguide-hover');
        removeOverlay();
        hoverElementRef.current = null;
      }
    },
    [selectedElement]
  );

  const handleElementSelect = React.useCallback(
    (e: MouseEvent) => {
      const target = e.target as HTMLElement;
      const idTourDock = document.getElementById(ID_TOURGUIDE_DOCK);

      if (
        !target ||
        selectedElement ||
        idTourDock.contains(target) ||
        checkContainIgnore(target)
      )
        return;

      e.stopImmediatePropagation();
      e.stopPropagation();
      e.preventDefault();

      const { element: closestElement } = getClosestElement(target);

      if (closestElement) {
        const positionElement = getRelativeClickPosition(e, closestElement);
        positionRef.current = { ...positionElement, element: closestElement };

        if (popperRef.current != null) {
          popperRef.current.update();
        }

        const _selectorPath = getClosestPath(closestElement);

        if (selectedElements?.some(item => item === closestElement)) return;

        closestElement.classList.add('tourguide-selected');
        setSelectorPath(_selectorPath);
        setSelectedElement(closestElement);
        setSelectedElements([...selectedElements, closestElement]);
        fire({
          type: 'setStatusCreate',
          payload: TourGuideStep.InputInfoStep
        });
      }
    },
    [fire, selectedElement, selectedElements]
  );

  const handleMousedown = React.useCallback(event => {
    const muiDropdown = event.target.closest('.MuiSelect-select');
    const muiAutocomplete = event.target.closest('.MuiAutocomplete-root');

    if (muiDropdown || muiAutocomplete) {
      event.stopPropagation();
      event.preventDefault();
    }
  }, []);

  React.useEffect(() => {
    return () => {
      removeOverlay(0);
    };
  }, []);

  React.useEffect(() => {
    if (selectedElement) return;

    document.addEventListener('mouseover', handleElementHover);
    document.addEventListener('mouseout', handleElementOut);
    document.addEventListener('click', handleElementSelect, true);
    document.addEventListener('mousedown', handleMousedown, true);

    return () => {
      document.removeEventListener('mouseover', handleElementHover);
      document.removeEventListener('mouseout', handleElementOut);
      document.removeEventListener('click', handleElementSelect, true);
      document.removeEventListener('mousedown', handleMousedown, true);
      document.body.style.cursor = 'inherit';
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedElement, selectedElements, isMoveDock]);

  React.useEffect(() => {
    if (!selectedElement) return;

    const handleKeyDown = (e: any) => {
      if (e.keyCode === 27) {
        handleCancel();
      }
    };

    window.addEventListener('keydown', handleKeyDown);

    return () => {
      window.removeEventListener('keydown', handleKeyDown);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedElement]);

  React.useEffect(() => {
    if (createStep !== TourGuideStep.SelectElement || isEmpty(tourSteps))
      return;

    const data = tourSteps
      ?.map(item => document.querySelector(item?.element))
      .filter(item => item);

    setSelectedElements(data);
  }, [tourSteps, createStep]);

  if (createStep !== TourGuideStep.InputInfoStep) return null;

  const handleResetSelector = (isLast = false) => {
    if (selectedElement) {
      setSelectedElement(null);
    }

    hoverElementRef.current?.classList.remove('tourguide-hover');

    if (!isLast) {
      fire({
        type: 'setStatusCreate',
        payload: TourGuideStep.SelectElement
      });
    } else if (isLast) {
      selectedElement?.classList?.remove('tourguide-selected');

      if (selectedElements) {
        setSelectedElements(null);
        selectedElements?.forEach(item => {
          item?.classList?.remove('tourguide-selected');
        });
      }
    }
  };

  const handleCancel = () => {
    if (selectedElement) {
      setSelectedElement(null);
      selectedElement?.classList?.remove('tourguide-selected');
    }

    setSelectedElements(
      selectedElements.filter(item => item !== selectedElement)
    );

    hoverElementRef.current?.classList.remove('tourguide-hover');

    fire({
      type: 'setStatusCreate',
      payload: TourGuideStep.SelectElement
    });
  };

  const handleSuccess = values => {
    handleResetSelector(values?.is_completed);

    fire({
      type: 'setValueStepSubmit',
      payload: pick(values, STEP_VALUE_KEYS)
    });

    if (values?.is_completed) {
      dispatch({ type: 'navigate/reload' });

      return;
    }
  };

  return (
    <>
      <TourOverlay />
      <PopperStyled
        open={!!selectedElement}
        data-testid="selectElement"
        popperOptions={{
          strategy: 'fixed'
        }}
        placement={'auto'}
        anchorEl={{
          getBoundingClientRect: () => {
            return new DOMRect(
              selectedElement?.getBoundingClientRect()?.x + throttleX,
              selectedElement?.getBoundingClientRect()?.y + throttleY,
              0,
              0
            );
          }
        }}
        popperRef={popperRef}
      >
        <Paper
          sx={{
            backgroundImage: 'unset'
          }}
        >
          <HeaderDock title="Step" onClose={handleCancel} isNewDock />
          <ScrollContainer
            autoHide
            autoHeight
            autoHeightMax={'60vh'}
            autoHeightMin={'60vh'}
          >
            <ErrorPage
              loading={loading}
              error={error}
              loadingComponent={LoadingComponent}
            >
              <Box p={2}>
                <FormBuilder
                  noHeader
                  initialValues={{
                    element: selectorPath,
                    tour_guide_id: tourGuideId,
                    ...valueStepSubmit
                  }}
                  fixedFooter
                  pageParams={pageParams}
                  formSchema={formSchema}
                  keepPaginationData
                  onSuccess={handleSuccess}
                  onCancel={handleCancel}
                />
              </Box>
            </ErrorPage>
          </ScrollContainer>
        </Paper>
      </PopperStyled>
    </>
  );
};

export default SelectedElement;
