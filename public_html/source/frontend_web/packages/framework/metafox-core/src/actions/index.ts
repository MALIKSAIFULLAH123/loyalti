import {
  CLOSE_MENU,
  ENTITY_DELETE,
  ENTITY_FULFILL,
  ENTITY_PATCH,
  ENTITY_PUT,
  FetchActionShape,
  FETCH_DETAIL,
  FormSubmitConfig,
  FORM_SUBMIT,
  LOGGED_OUT,
  PAGINATION_DELETE,
  PAGINATION,
  PAGINATION_MODIFIED,
  PagingMeta,
  PagingPayload
} from '@metafox/framework';
import { compactData, compactUrl } from '@metafox/utils';

export function formSubmitAction(
  payload: FormSubmitConfig,
  meta?: { onSuccess?: () => void; onFailure?: (error: any) => void }
) {
  return {
    type: payload.submitAction ? payload.submitAction : FORM_SUBMIT,
    payload,
    meta
  };
}

export function deleteEntityAction(identity: string) {
  return { type: ENTITY_DELETE, payload: { identity } };
}

export function deletePaginationAction(
  identity: string,
  prefixPagingId?: string
) {
  return { type: PAGINATION_DELETE, payload: { identity, prefixPagingId } };
}

export function makeDirtyAction(domains: string, excludes?: string) {
  return { type: PAGINATION_MODIFIED, payload: { domains, excludes } };
}

export function patchEntityAction(
  identity: string,
  data: Record<string, any>,
  deepMerge
) {
  return {
    type: ENTITY_PATCH,
    payload: {
      identity,
      data,
      deepMerge
    }
  };
}

export function putEntityAction(identity: string, data: Record<string, any>) {
  return {
    type: ENTITY_PUT,
    payload: {
      identity,
      data
    }
  };
}

export function fulfillEntityAction(data: any) {
  return {
    type: ENTITY_FULFILL,
    payload: { data }
  };
}

export function loggedOutAction() {
  return { type: LOGGED_OUT };
}

export function closeMenuAction(action: any) {
  return { ...action, type: CLOSE_MENU };
}

export function paginationAction(payload: PagingPayload, meta?: PagingMeta) {
  return {
    type: PAGINATION,
    payload,
    meta
  };
}

export function fetchDetail(
  apiUrl: string,
  params?: Record<string, any>,
  onSuccess?: () => void,
  onFailure?: (error: any) => void,
  abortId?: string
): FetchActionShape {
  const { apiParams, pageParams, ...others } = Object.assign({}, params);

  return {
    type: FETCH_DETAIL,
    payload: {
      apiUrl: compactUrl(apiUrl, { ...apiParams, ...pageParams, ...others }),
      apiParams: compactData(apiParams, { ...pageParams, ...others })
    },
    meta: { onFailure, onSuccess, abortId }
  };
}
