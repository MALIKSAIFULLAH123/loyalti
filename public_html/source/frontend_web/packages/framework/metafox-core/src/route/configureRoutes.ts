import { RouteProps } from 'react-router-dom';

type RouteProps2 = { modal?: boolean } & RouteProps;

export function configureRoutes(
  routes: Record<string, RouteProps2>,
  modal: boolean = false
): RouteProps2[] {
  if (!routes) return [];

  return Object.values(routes)
    .filter(
      route => (route.path && !modal && !route.modal) || (modal && route.modal)
    )
    .map(route => {
      if (false !== route.exact) route.exact = true;

      if (!route['key']) route['key'] = route.path;

      return route;
    });
}
