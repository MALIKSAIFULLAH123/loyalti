/**
 * @type: ui
 * name: core.block.no_content
 * title: No Content
 * keywords: no content
 */
import { NoContentProps, useGlobal } from '@metafox/framework';
import { Box, Theme } from '@mui/material';
import { alpha } from '@mui/system/colorManipulator';
import { createStyles, makeStyles } from '@mui/styles';
import * as React from 'react';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      default: {
        fontSize: theme.mixins.pxToRem(15),
        color: theme.palette.text.secondary,
        textAlign: 'center'
      },
      side: {
        fontSize: theme.mixins.pxToRem(18),
        color: alpha(theme.palette.text.secondary, 0.7),
        textAlign: 'left',
        fontWeight: theme.typography.fontWeightBold,
        paddingLeft: theme.spacing(2)
      },
      popover: {
        position: 'relative',
        color: theme.palette.text.secondary,
        fontWeight: theme.typography.fontWeightBold,
        fontSize: theme.mixins.pxToRem(16),
        marginTop: theme.spacing(1),
        marginLeft: theme.spacing(2),
        marginBottom: theme.spacing(2)
      },
      center: {
        fontSize: theme.mixins.pxToRem(15),
        color: theme.palette.text.secondary,
        height: '100%',
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center'
      }
    }),
  { name: 'NoContentBlock' }
);

const NoContentBlock = ({
  title = 'no_content',
  variant = 'default',
  contentStyle
}: NoContentProps) => {
  const classes = useStyles();
  const { i18n } = useGlobal();

  if (title === null) return null;

  return (
    <Box
      data-testid="noResultFound"
      className={classes[variant]}
      sx={contentStyle?.sx}
    >
      {i18n.formatMessage({ id: title })}
    </Box>
  );
};
export default NoContentBlock;
