/**
 * @type: ui
 * name: statusComposer.control.StatusUploadPhotoButton
 * chunkName: statusComposerControl
 */
import {
  StatusComposerControlProps,
  useGlobal,
  useGetItem
} from '@metafox/framework';
import React from 'react';
import useAddPhotoToStatusComposerHandler from '../hooks/useAddPhotoToStatusComposerHandler';
import useFeedMediaConfig from '../hooks/useFeedMediaConfig';

export default function StatusComposerControlUploadPhotoButton(
  props: StatusComposerControlProps & {
    label: string;
  }
) {
  const { i18n } = useGlobal();
  const { control: Control, disabled, label, parentIdentity } = props;
  const parentItem = useGetItem(parentIdentity);

  const inputRef = React.useRef<HTMLInputElement>();

  const handleResetValue = (
    event: React.MouseEvent<HTMLInputElement, MouseEvent>
  ) => {
    event.currentTarget.value = null;
  };

  const { acceptTypes, canUploadVideo, canUploadPhoto } = useFeedMediaConfig({
    parentUser: parentItem
  });

  const [handleChange, onClick] = useAddPhotoToStatusComposerHandler(
    props.composerRef,
    inputRef,
    { parentUser: parentItem }
  );

  if (!acceptTypes.length) return;

  return (
    <>
      <Control
        disabled={disabled}
        testid="attachPhoto"
        onClick={onClick}
        icon="ico-photos-alt-o"
        label={label}
        title={i18n.formatMessage({
          id: disabled ? 'this_cant_be_combined' : 'upload_media'
        })}
        canUploadTypes={{ video: canUploadVideo, photo: canUploadPhoto }}
      />
      <input
        onChange={handleChange}
        multiple
        onClick={handleResetValue}
        data-testid="inputAttachPhoto"
        ref={inputRef}
        className="srOnly"
        type="file"
        accept={acceptTypes}
      />
    </>
  );
}
