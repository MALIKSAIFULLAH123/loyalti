import { useGlobal } from '@metafox/framework';
import React from 'react';
import {
  Route,
  Routes,
  useLocation,
  useNavigationType,
  createPath
} from 'react-router-dom';

const INIT: string = '@initial';

export default function MainRoutes({ location: locationProp }) {
  const mn = useGlobal();
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const { routeBackend, isInMasterPage, setRouteLoading, RouteLoadingView } =
    mn;
  const master = isInMasterPage();
  const locationGlobal = useLocation() as any;
  const location = locationProp ?? locationGlobal;
  const action = useNavigationType();
  const url = createPath(location);
  const lastMatch = React.useRef<any>(undefined);
  let freeze = false;

  const handleTriggerScrollTop = React.useCallback(() => {
    if (mn.triggerScrollTop && !location.hash) {
      mn.triggerScrollTop();
    }
    // eslint-disable-next-line
  }, []);

  const [page, setPage] = React.useState<{
    name: string;
    url: string;
    params: object;
    component: React.FC;
  }>({
    name: INIT,
    url,
    component: RouteLoadingView,
    params: {}
  });

  const isFirst = page.name === INIT;

  if (location.state?.asModal) {
    freeze = true;
  }

  if (location.state?.asChildPage && master) {
    freeze = true;
  }

  if (freeze && isFirst) {
    freeze = false;
  }

  if (freeze && isFirst) {
    handleTriggerScrollTop();
  }

  if (action === 'POP') {
    lastMatch.current = null;
  }

  // eslint-disable-next-line react-hooks/exhaustive-deps
  // test if can map
  React.useEffect(() => {
    if (freeze) return;

    routeBackend
      .getPage(url, setRouteLoading, handleTriggerScrollTop)
      .then(setPage);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [url, freeze, location?.key]);

  const { component: Page, params } = page;

  return (
    <Routes>
      <Route path="*" element={<Page {...params} />} />
    </Routes>
  );
}
