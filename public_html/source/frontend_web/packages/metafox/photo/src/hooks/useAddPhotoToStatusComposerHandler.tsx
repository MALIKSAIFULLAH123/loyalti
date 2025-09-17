import {
  BasicFileItem,
  StatusComposerRef,
  useGlobal
} from '@metafox/framework';
import { get, isEmpty } from 'lodash';
import React from 'react';
import useHandleFeedMediaFile from './useHandleFeedMediaFile';

const IS_ANDROID: boolean = /Android/.test(navigator.userAgent);

export default function useAddPhotoToStatusComposerHandler(
  composerRef: React.MutableRefObject<StatusComposerRef>,
  inputRef?: React.MutableRefObject<HTMLInputElement>,
  extra?: Record<string, any>
): [(value) => void, () => void] {
  const { dispatch } = useGlobal();
  const hasSetPhoto = React.useRef(false);
  const { acceptTypes } = extra || {};
  const [handleFile] = useHandleFeedMediaFile({
    parentUser: extra?.parentUser
  });

  const openFileInput = React.useCallback(() => {
    inputRef?.current?.click();
  }, [inputRef]);

  const handleSupportChooseFrom = React.useCallback(() => {
    dispatch({
      type: 'photo/androidSupportChoosePhoto',
      payload: { ref: inputRef, acceptTypes },
      meta: { callback: openFileInput }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [acceptTypes, inputRef, openFileInput]);

  const onClick = React.useCallback(() => {
    if (IS_ANDROID) {
      handleSupportChooseFrom();
    } else {
      openFileInput();
    }
  }, [openFileInput, handleSupportChooseFrom]);

  const clear = () => {
    if (inputRef?.current) {
      inputRef.current.value = null;
    }
  };

  const handleChange = React.useCallback(
    async files => {
      if (!files.length) return;

      const filesCurrent: BasicFileItem[] = get(
        composerRef.current.state,
        'attachments.photo.value'
      );

      const { setAttachments } = composerRef.current;

      if (!setAttachments) return;

      handleFile(files, filesCurrent, value => {
        if (isEmpty(filesCurrent) && isEmpty(value)) return;

        setAttachments('photo', 'photo', {
          as: 'StatusComposerControlAttachedPhotos',
          value
        });

        hasSetPhoto.current = true;
      });
      clear();

      return null;
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [composerRef]
  );

  const hasAttachmentPhotos = React.useMemo(
    () => !isEmpty(get(composerRef.current?.state, 'attachments.photo.value')),
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [composerRef.current?.state?.attachments?.photo?.value]
  );

  React.useEffect(() => {
    const { requestComposerUpdate } = composerRef.current || {};

    if (hasSetPhoto.current && hasAttachmentPhotos && requestComposerUpdate) {
      hasSetPhoto.current = false;
      requestComposerUpdate();
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [hasAttachmentPhotos]);

  const handleBeforeChange = value => {
    const files = inputRef?.current?.files || value;

    handleChange(files);
  };

  return [handleBeforeChange, onClick];
}
