/**
 * @type: service
 * name: SiteDockContainer
 */
import { useGlobal } from '@metafox/framework';

export default function SiteDockContainer() {
  const { jsxBackend, layoutBackend } = useGlobal();

  return jsxBackend.render(layoutBackend.getSiteDockComponents());
}
