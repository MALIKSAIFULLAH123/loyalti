/**
 * @type: formElement
 * name: form.element.ItemPhotoGallery
 * chunkName: formElement
 */

import { BasicFileItem, useGlobal } from '@metafox/framework';
import { FormFieldProps } from '@metafox/form';
import { LineIcon } from '@metafox/ui';
import { Button, Tooltip } from '@mui/material';
import { useField } from 'formik';
import { camelCase, uniqueId } from 'lodash';
import React, { useCallback, useRef, useState } from 'react';
import Label from '../Label';
import useStyles from './ItemPhotoGalleryField.styles';

export default function ItemPhotoGalleryField({
  name,
  config
}: FormFieldProps) {
  const classes = useStyles();
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const [, , { setValue }] = useField(name ?? 'ItemPhotoGalleryField');
  const { i18n } = useGlobal();
  const placeholder = config.placeholder || 'Add Photos';
  const [previewUrls, setPreviewUrls] = useState([]);
  const [hasPreview, setHasPreview] = useState(
    Boolean(previewUrls.length - 1 > 0)
  );
  const inputRef = useRef<HTMLInputElement>();

  const handleControlClick = useCallback(() => {
    inputRef.current.click();
  }, []);
  const handleInputChange = useCallback(() => {
    const files = inputRef.current.files;

    if (!files) return;

    const fileItems: BasicFileItem[] = [];

    for (let i = 0; i < files.length; ++i) {
      fileItems.push({
        uid: uniqueId('file'),
        source: URL.createObjectURL(files.item(i)),
        file: files.item(i)
      });
    }

    if (fileItems) {
      setPreviewUrls(fileItems);
      setValue(previewUrls);
      setHasPreview(true);
    }
  }, [previewUrls, setValue]);

  const handleDeletePhoto = useCallback(
    item => {
      setPreviewUrls(prev => {
        return prev.filter(x => x.uid !== item.uid);
      });
      setValue(previewUrls);
      setHasPreview(Boolean(previewUrls.length - 1 > 0));
    },
    [previewUrls, setValue]
  );

  return (
    <div className={classes.root} data-testid={camelCase(`field ${name}`)}>
      <div className={classes.addPhotoWrapper}>
        <Label>{config.label}</Label>
        <div
          className={classes.controls}
          role="button"
          onClick={handleControlClick}
        >
          <Button
            size="small"
            color="primary"
            variant="outlined"
            startIcon={<LineIcon icon="ico-photos-plus-o" />}
          >
            {placeholder}
          </Button>
        </div>
        <input
          ref={inputRef}
          type="file"
          multiple
          aria-hidden
          className="srOnly"
          accept="image/*"
          onChange={handleInputChange}
        />
      </div>
      {hasPreview && (
        <>
          <div className={classes.previewList}>
            {previewUrls.map((item, index) => (
              <div className={classes.previewItem} key={index.toString()}>
                <img src={item.source} alt="" />
                <Tooltip title={i18n.formatMessage({ id: 'remove' })}>
                  <LineIcon
                    icon="ico-close"
                    onClick={() => handleDeletePhoto(item)}
                    className={classes.deletePhoto}
                  />
                </Tooltip>
              </div>
            ))}
          </div>
          <div className={classes.totalPhoto}>
            {previewUrls.length}/10{' '}
            {i18n.formatMessage({ id: 'photos_uploaded' })}
          </div>
        </>
      )}
    </div>
  );
}
