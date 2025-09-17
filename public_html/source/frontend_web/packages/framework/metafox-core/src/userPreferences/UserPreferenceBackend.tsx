/**
 * @type: service
 * name: preferenceBackend
 * priority: -9
 */
import {
  DEFAULT_THEME,
  ACTIVE_THEMES,
  Manager,
  THEME_KEY,
  UserPreferenceConfig,
  IS_ADMINCP,
  DEFAULT_THEME_TYPE
} from '@metafox/framework';
import { ThemeType } from '@metafox/layout';
import { get, set, isEmpty } from 'lodash';

export class UserPreferenceBackend {
  private data: UserPreferenceConfig;

  private manager: Manager;

  public static readonly configKey: string = 'settings';

  constructor(data: UserPreferenceConfig) {
    this.data = data ?? ({} as any);
  }
  [key: string]: any;

  bootstrap(manager: Manager) {
    this.manager = manager;

    const isLayoutPreviewWindow = manager.constants?.isLayoutPreviewWindow;
    const topWindow = window?.top as any;
    const selfWindow = window as any;
    // mix data to this services.

    if (isLayoutPreviewWindow && topWindow.preferenceBackend) {
      return (selfWindow.preferenceBackend = topWindow.preferenceBackend);
    }

    const cookieBackend = manager.cookieBackend;

    const themeType = cookieBackend.get('themeType') as unknown as ThemeType;

    this.data.previewDevice = cookieBackend.get('previewDevice') || '';
    this.data.userLanguage = cookieBackend.get('userLanguage');
    this.data.themeType = themeType || DEFAULT_THEME_TYPE || 'auto';

    const themeId = cookieBackend.get(THEME_KEY) || '';

    this.data[THEME_KEY] = this.compileThemeId(themeId);

    this.notifyChanged = this.notifyChanged.bind(this);

    return this;
  }

  public compileThemeId(name) {
    // check case inactive variant theme in ACP. should back to default theme
    if (!name || IS_ADMINCP) return DEFAULT_THEME;

    const isExistActiveThemes = ACTIVE_THEMES
      ? ACTIVE_THEMES.split(',').includes(name)
      : false;

    return /(.+):(.+)/i.test(name) && isExistActiveThemes
      ? name
      : DEFAULT_THEME;
  }

  public get(name: string, value: any = undefined) {
    return get(this.data, name, value);
  }

  public set(key: string, value: any): void {
    if ('function' === typeof value) {
      const prev = this.get(key);
      value = value(prev);
    }

    set(this.data, key, value);

    this.notifyChanged(Object.assign({}, this.data));
  }

  public getAll(): UserPreferenceConfig {
    return this.data;
  }

  private notifyChanged(obj: UserPreferenceConfig) {
    if (this.manager?.eventCenter)
      this.manager.eventCenter.dispatch('onUserPreferenceChanged', obj);
  }

  public toggleDarkMode = () => {
    this.setAndRemember('themeType', prev => {
      return prev === 'dark' ? 'light' : 'dark';
    });
  };

  public setThemeType = (themeType: 'light' | 'dark' | 'auto') => {
    this.setAndRemember('themeType', themeType);

    const { dispatch } = this.manager;

    if (dispatch) {
      dispatch({
        type: 'userPreference/themeChange',
        payload: { profile_theme_type: themeType }
      });
    }
  };

  private remember(key: string, value: any) {
    if (this.manager?.cookieBackend)
      this.manager.cookieBackend.set(key.toString(), value.toString());
  }

  public setAndRemember(key: string, value: any) {
    if ('function' === typeof value) {
      const prev = this.get(key);
      value = value(prev);
    }

    this.remember(key, value);
    this.set(key, value);
  }

  public getTheme(): string {
    return get(this.data, THEME_KEY);
  }

  public setTheme(value: string, cb): void {
    const { dispatch } = this.manager;
    this.setAndRemember(THEME_KEY, value);

    if (dispatch) {
      dispatch({
        type: 'userPreference/themeChange',
        payload: { profile_theme_id: value },
        meta: { onSuccess: cb }
      });
    }
  }

  public setThemePreferenceInit(values): void {
    if (isEmpty(values)) return;

    Object.keys(values).forEach(x => {
      if (!values[x]) return;

      this.setAndRemember(x, values[x]);
    });
  }

  public leavePreviewMode() {}
}

export default UserPreferenceBackend;
