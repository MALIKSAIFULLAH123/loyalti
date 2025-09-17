/**
 * @type: modalRoute
 * name: media.viewModal
 * path: /photo/:photo_id(\d+)/:slug?, /photo/:photo_set(\d+)/:photo_id(\d+)/:slug?, /media/:photo_set(\d+)/:media_type/:photo_id(\d+)/:slug?, /media/album/:photo_album(\d+)/:media_type/:photo_id(\d+)/:slug?
 * bundle: web
 */
import {
  fetchDetail,
  useGlobal,
  useAbortControl,
  useLocation
} from '@metafox/framework';
import * as React from 'react';
import { APP_PHOTO } from '@metafox/photo/constant';
import qs from 'query-string';

const appName = 'photo';
const resourceName = 'video';
const dialogId = 'viewMedia';

export default function PhotoViewModal(props) {
  const { dispatch, createPageParams, dialogBackend, use } = useGlobal();
  const pageParams = createPageParams<{
    photo_set: string;
    photo_album: string;
    photo_id: string;
    media_type?: string;
  }>(props, prev => ({
    media_type: prev['media_type'] || APP_PHOTO,
    _pageType: 'viewItemInModal'
  }));
  const [loading, setLoading] = React.useState(true);
  const [error, setErr] = React.useState<number>(0);
  const { photo_id, media_type, photo_set, photo_album } = pageParams;
  const identity = `${media_type}.entities.${media_type}.${photo_id}`;
  const abortId = useAbortControl(photo_id);

  const location = useLocation();
  const searchParams = location?.search
    ? qs.parse(location.search.replace(/^\?/, ''))
    : {};

  const onSuccess = () => {
    setLoading(false);
  };

  const onError = error => {
    setLoading(false);
    setErr(error);
  };

  React.useEffect(() => {
    if (photo_set) {
      dispatch({
        type: 'photo/photo_set/LOAD',
        payload: {
          photo_set,
          media_id: photo_id
        },
        meta: {
          onSuccess,
          onError
        }
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [photo_set]);

  React.useEffect(() => {
    if (photo_album) {
      dispatch({
        type: 'photo/photo_album/LOAD',
        payload: {
          photo_album,
          media_id: photo_id
        },
        meta: {
          onSuccess,
          onError
        }
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [photo_album]);

  React.useEffect(() => {
    use({ getPageParams: () => pageParams });

    if (photo_album || photo_set) return;

    if (photo_id) {
      dispatch(
        fetchDetail(
          `/${media_type}/:id`,
          { id: photo_id, apiParams: { ...searchParams } },
          onSuccess,
          onError,
          abortId
        )
      );
    }

    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [identity, pageParams]);

  React.useEffect(() => {
    if (loading)
      dialogBackend.present({
        component: 'Loading',
        dialogId: dialogId ?? `${appName}${resourceName}`
      });
    else {
      dialogBackend.present({
        component: 'media.dialog.mediaView',
        props: {
          identity,
          photo_set,
          photo_album,
          photo_id,
          media_type,
          loading,
          error
        },
        dialogId: dialogId ?? `${appName}${resourceName}`
      });
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [identity, pageParams, loading, error]);

  return null;
}
