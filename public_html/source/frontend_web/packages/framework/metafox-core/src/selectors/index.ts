import { GlobalState } from '@metafox/framework';
import { get, isEqual, isString } from 'lodash';
import {
  createSelector,
  createSelectorCreator,
  defaultMemoize
} from 'reselect';
import { MenuShape, PagingState } from '../types';

function getResourceAction(
  state: GlobalState,
  appName: string,
  resource: string,
  action: string
) {
  return get(state, `_actions.${appName}.${resource}.${action}`);
}

function getResourceMenu(
  state: GlobalState,
  appName: string,
  resource: string,
  menuName: string
) {
  return get(state, `_resourceMenus.${appName}.${resource}.${menuName}`);
}

function getResourceConfig(
  state: GlobalState,
  appName: string,
  resource: string,
  action?: string
) {
  return action
    ? get(state, `_actions.${appName}.${resource}.${action}`)
    : get(state, `_actions.${appName}.${resource}`);
}

export function getResourceForm(
  state: GlobalState,
  appName: string,
  resourceName: string,
  formName: string
) {
  return get(state, `_forms.${appName}.${resourceName}.${formName}`);
}

function getPaging(state: GlobalState, pagingId: string): PagingState {
  return state.pagination[pagingId];
}

const getItem = (state: GlobalState, identity: string) => {
  return identity ? get(state, identity) : undefined;
};

const getItems = (state: GlobalState, ids: string[]) => {
  return ids ? ids.map((x: string) => get(state, x)).filter(Boolean) : [];
};

// schedule_post not have id
const getItemRawData = (x, index) =>
  (x ? { id: `t${index}`, ...x } : undefined);

const getItemsData = (state: GlobalState, identities: string[]) => {
  return identities
    ? identities
        ?.map((x, index) =>
          (isString(x) ? get(state, x) : getItemRawData(x, index))
        )
        .filter(Boolean)
    : [];
};

const getQuizQuestion = (state: GlobalState, identities: string[]) => {
  return identities ? identities?.map(x => get(state, x)).filter(Boolean) : [];
};

const getExtraData = (state: GlobalState, identity: string) => {
  return identity ? get(state, identity) : undefined;
};

export const getResourceConfigSelector = getResourceConfig;

export const getResourceActionSelector = getResourceAction;

export const getResourceMenuSelector = getResourceMenu;

const getAppMenu = (
  state: GlobalState,
  appName: string,
  menuName: string
): MenuShape => get(state, `_appMenus.${appName}.${menuName}`);

export const getAppMenuSelector = createSelector(getAppMenu, menu => menu);

const getSession = (state: GlobalState) => {
  const session = state.session;
  const user = getItemSelector(state, `user.entities.user.${session.user?.id}`);

  if (!user) return session;

  return { ...session, user: { ...session.user, ...user } };
};

export const getItemSelector = createSelector(getItem, data => data);

export const getItemsSelector = createSelector(getItems, data => data);

export const getDeepItemSelector = createSelectorCreator(
  defaultMemoize,
  isEqual
);

export const getDeepItem = getDeepItemSelector(getItem, data => data);

export const getPhotoSelector = createSelector(getItem, data => data);

export const getPhotosSelector = createSelector(getItemsData, data => data);

export const getUserSelector = createSelector(getItem, data => data);

export const getSessionSelector = createSelector(getSession, data => data);

export const getPollAnswersSelector = createSelector(
  getItemsData,
  item => item
);

export const getQuizQuestionSelector = createSelector(
  getQuizQuestion,
  item => item
);

export const getAttachmentsSelector = createSelector(getItems, data => data);

export const getCategoriesSelector = createSelector(getItems, data => data);

export const getTagsSelector = createSelector(getItems, data => data);

export const getExtraDataSelector = createSelector(
  getExtraData,
  extraData => extraData
);

const getProfileMenu = (
  state: GlobalState,
  moduleName: string,
  resourceName: string
) => get(state, `_resourceMenus.${moduleName}.${resourceName}.profileMenu`);

const getProfileActionMenu = (
  state: GlobalState,
  moduleName: string,
  resourceName: string
) =>
  get(state, `_resourceMenus.${moduleName}.${resourceName}.profileActionMenu`);

export const getProfileMenuSelector = createSelector(
  getProfileMenu,
  item => item
);

export const getProfileActionMenuSelector = createSelector(
  getProfileActionMenu,
  item => item
);

const getAppSidebarSearch = (state: GlobalState, app: string) =>
  get(state, `${app}.uiConfig.sidebarSearch`);

export const getAppSidebarSearchSelector = createSelector(
  getAppSidebarSearch,
  data => data
);

export const getPagingSelector = createSelector(getPaging, data => data);

const getPageMeta = (state: GlobalState, id: string) => state.pageMeta[id];

export const getPageMetaDataSelector = createSelector(
  getPageMeta,
  data => data
);
