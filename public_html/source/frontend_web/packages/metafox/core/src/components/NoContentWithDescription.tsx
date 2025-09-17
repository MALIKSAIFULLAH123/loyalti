/**
 * @type: ui
 * name: core.block.no_content_with_description
 * title: No Content With Description
 * keywords: no content
 */

import { useGlobal } from '@metafox/framework';
import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import React from 'react';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        flexDirection: 'column',
        marginTop: theme.spacing(4),
        marginBottom: theme.spacing(4)
      },
      title: {
        color: theme.palette.text.primary,
        fontSize: theme.mixins.pxToRem(24),
        fontWeight: theme.typography.fontWeightBold
      },
      content: {
        fontSize: theme.mixins.pxToRem(18),
        color: theme.palette.text.secondary,
        marginTop: theme.spacing(2),
        textAlign: 'center'
      }
    }),
  {
    name: 'NoContentWithDescription'
  }
);

interface NoContentWithDescriptionProps {
  title?: string;
  description?: string;
}

const NoContentWithDescription = ({
  title = 'no_post_found_title',
  description = 'empty_feed'
}: NoContentWithDescriptionProps) => {
  const classes = useStyles();
  const { i18n } = useGlobal();

  return (
    <div className={classes.root} data-testid="noResultFound">
      <div className={classes.title}>
        <span>{i18n.formatMessage({ id: title })}</span>
      </div>
      <div className={classes.content}>
        <span>{i18n.formatMessage({ id: description })}</span>
      </div>
    </div>
  );
};

export default NoContentWithDescription;
