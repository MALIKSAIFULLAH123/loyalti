/**
 * @type: ui
 * name: StatusComposerControlAttatchedVideos
 */

import {
  BasicFileItem,
  StatusComposerControlProps,
  useGlobal
} from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Button, IconButton, Tooltip } from '@mui/material';
import clsx from 'clsx';
import { get } from 'lodash';
import React from 'react';
import useStyles from './styles';

type Props = StatusComposerControlProps & {};

function CloseButton({ className, title, onClick }) {
  return (
    <Tooltip title={title}>
      <IconButton size="smaller" onClick={onClick} className={className}>
        <LineIcon icon="ico-close" />
      </IconButton>
    </Tooltip>
  );
}

export default function PreviewVideos({ composerRef }: Props) {
  const classes = useStyles();
  const { i18n } = useGlobal();
  const fileItems: BasicFileItem[] = get(
    composerRef.current.state,
    'attachments.video.value'
  );
  const [items, setItems] = React.useState<BasicFileItem[]>(fileItems ?? []);

  const onRemoveAll = React.useCallback(() => {
    composerRef.current.removeAttachments();
  }, [composerRef]);

  React.useEffect(() => {
    if (!fileItems || !fileItems?.length) return;

    setItems(fileItems);
  }, [fileItems]);

  React.useEffect(() => {
    if (items.length) {
      composerRef.current.setAttachments('video', 'video', {
        value: items,
        as: 'StatusComposerControlAttatchedVideos'
      });
    }
  }, [composerRef, items]);

  if (!items?.length) return null;

  const gridType = Math.min(items.length, 4) % 5;
  const remain = items.length - gridType;

  return (
    <div className={clsx(classes.root)}>
      <div
        className={clsx(classes.listContainer, classes[`preset${gridType}`])}
      >
        {items.slice(0, gridType).map((item, index) => (
          <div
            className={clsx(classes.itemRoot, classes[`item${index}`])}
            key={index.toString()}
          >
            <video className={classes.itemInner} src={item.source} />
            {0 < remain && gridType === index + 1 ? (
              <div className={classes.remainBackdrop}>
                <div className={classes.remainText}>{`+ ${remain}`}</div>
              </div>
            ) : null}
          </div>
        ))}
      </div>
      <div className={classes.actionBar}>
        <CloseButton
          onClick={onRemoveAll}
          title={i18n.formatMessage({ id: 'remove_all' })}
          className={classes.closeButton}
        />
        <Button variant="contained" size="small">
          <LineIcon icon="ico-pencil" />
          <span>{i18n.formatMessage({ id: 'edit_all' })}</span>
        </Button>
      </div>
    </div>
  );
}
