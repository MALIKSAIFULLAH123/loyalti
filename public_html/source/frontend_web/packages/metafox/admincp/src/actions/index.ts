import {
  ADMINCP_SITE_STATUS_PUT,
  ADMINCP_SITE_STATUS_START,
  ADMINCP_SITE_STATUS_GET,
  ADMINCP_SITE_LOADING
} from '../constants';

export function putAdminSiteStatus(data: any) {
  return {
    type: ADMINCP_SITE_STATUS_PUT,
    payload: { data }
  };
}

export function getAdminSiteStatus(reload = false) {
  return {
    type: ADMINCP_SITE_STATUS_GET,
    payload: { reload }
  };
}

export function startGetAdminSiteStatus() {
  return {
    type: ADMINCP_SITE_STATUS_START
  };
}

export function setAdminSiteLoading(loading = false) {
  return {
    type: ADMINCP_SITE_LOADING,
    payload: { loading }
  };
}
