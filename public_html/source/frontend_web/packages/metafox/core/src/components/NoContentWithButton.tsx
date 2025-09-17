/**
 * @type: ui
 * name: core.block.no_content_with_button
 * title: No content with button
 * keywords: no content
 */

import { useGlobal } from '@metafox/framework';
import { LineIcon } from '@metafox/ui';
import { Button, styled, Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import * as React from 'react';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        display: 'flex',
        flexDirection: 'column',
        justifyContent: 'center',
        padding: theme.spacing(0, 2),
        alignItems: 'center',
        marginTop: theme.spacing(11.25)
      },
      icon: {
        fontSize: theme.mixins.pxToRem(64),
        color: theme.palette.text.secondary,
        marginBottom: theme.spacing(4)
      },
      title: {
        fontSize: theme.mixins.pxToRem(24),
        fontWeight: theme.typography.fontWeightBold,
        marginBottom: theme.spacing(1.5),
        [theme.breakpoints.down('xs')]: {
          fontSize: theme.mixins.pxToRem(18)
        }
      },
      content: {
        fontSize: theme.mixins.pxToRem(18),
        color: theme.palette.text.secondary,
        textAlign: 'center',
        [theme.breakpoints.down('xs')]: {
          fontSize: theme.mixins.pxToRem(15)
        }
      }
    }),
  {
    name: 'NoContentWithIcon'
  }
);

const WrapButton = styled(Button, { name: 'WrapButton' })(({ theme }) => ({
  marginTop: theme.spacing(1),
  '&:hover': {
    backgroundColor: 'unset'
  }
}));

interface NoContentWithIconProps {
  icon: string;
  title?: string;
  description?: string;
  buttonCustom?: any;
}

export default function NoContentWithButton({
  icon = 'ico-user3-three',
  title,
  description,
  buttonCustom
}: NoContentWithIconProps) {
  const classes = useStyles();
  const { i18n, jsxBackend } = useGlobal();

  return (
    <div className={classes.root} data-testid="noResultFound">
      <LineIcon className={classes.icon} icon={icon} />
      {title ? (
        <div className={classes.title}>
          <span>{i18n.formatMessage({ id: title })}</span>
        </div>
      ) : null}
      {description ? (
        <div className={classes.content}>
          {i18n.formatMessage({ id: description })}
        </div>
      ) : null}
      {buttonCustom ? (
        <WrapButton>
          {jsxBackend.render({
            component: buttonCustom.component,
            props: { ...buttonCustom.props }
          })}
        </WrapButton>
      ) : null}
    </div>
  );
}
