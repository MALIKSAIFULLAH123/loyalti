import { IS_ADMINCP } from '@metafox/framework';
import uniqMetaName from './uniqMetaName';

type Typed = string | null | false | undefined;

export default function normalizePageName(
  appName: Typed,
  resourceName: Typed,
  action: Typed,
  category?: Typed,
  suffix?: Typed
): string {
  const prefix = IS_ADMINCP ? 'admin.' : '';

  const name = [action, category, resourceName ? resourceName : appName, suffix]
    .filter(Boolean)
    .join('_');

  return uniqMetaName(`${prefix}${appName}.${name}`);
}
