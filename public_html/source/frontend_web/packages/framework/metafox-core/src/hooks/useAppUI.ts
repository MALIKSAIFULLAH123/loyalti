import { GlobalState } from '@metafox/framework/Manager';
import { FormSchemaShape } from '@metafox/form';
import { get } from 'lodash';
import { useSelector } from 'react-redux';
import { AppUIConfig } from '../types';

export default function useAppUI(
  appName: string,
  sidebarHeaderName: string
): FormSchemaShape | undefined {
  return useSelector<GlobalState, AppUIConfig>(state =>
    get(state, `${appName}.uiConfig.sidebarHeader.${sidebarHeaderName}`)
  ) as any;
}
