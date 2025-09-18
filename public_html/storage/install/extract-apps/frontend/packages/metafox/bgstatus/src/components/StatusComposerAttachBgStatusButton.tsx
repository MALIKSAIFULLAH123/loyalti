/**
 * @type: ui
 * name: statusComposer.control.AttachBackgroundStatusButton
 * chunkName: statusComposerControl
 */
import { StatusComposerControlProps } from '@metafox/framework';
import { getImageSrc } from '@metafox/utils';
import React from 'react';
import Base from './AttachBackgroundStatus/AttachBackgroundStatus';
import { alpha } from '@mui/material';

export default function AttachBgStatusToStatusComposerButton({
  composerRef,
  editorRef,
  disabled,
  value,
  isEdit,
  focusToEndText,
  ...rest
}: StatusComposerControlProps) {
  const clearBackgroundStatus = () => {
    composerRef.current.removeBackground(isEdit);
    focusToEndText && focusToEndText();
  };

  const selectBackgroundStatus = (item: any) => {
    const className = 'withBackgroundStatus';
    const textAlignment = 'center';
    const editorStyle: React.CSSProperties = {
      fontSize: '28px',
      color: item?.text_color || 'white',
      fontWeight: 'bold',
      textAlign: 'center',
      backgroundSize: 'cover',
      backgroundImage: `url("${getImageSrc(item.image)}")`,
      minHeight: 371,
      marginTop: '16px',
      marginBottom: '16px',
      cursor: 'text',
      '& a': {
        color: item?.text_color || 'white'
      },
      ...(item?.text_color
        ? {
            '& .editor-placeholder': {
              color: alpha(item?.text_color, 0.7)
            }
          }
        : {})
    };

    composerRef.current.setBackground({
      className,
      textAlignment,
      item,
      editorStyle
    });
    setImmediate(() => {
      focusToEndText && focusToEndText();
    });
  };

  if (disabled) return null;

  return (
    <Base
      {...rest}
      selectedId={
        value?.status_background_id ||
        composerRef.current.state.attachments?.statusBackground?.value?.id
      }
      onClear={clearBackgroundStatus}
      onSelectItem={selectBackgroundStatus}
    />
  );
}
