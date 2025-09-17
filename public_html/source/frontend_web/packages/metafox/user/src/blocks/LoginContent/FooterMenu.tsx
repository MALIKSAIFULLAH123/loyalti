import { Link, useGlobal } from '@metafox/framework';
import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import React from 'react';

type FooterMenuProps = {
  color?:
    | 'inherit'
    | 'initial'
    | 'primary'
    | 'secondary'
    | 'textPrimary'
    | 'textSecondary'
    | 'error';
  [key: string]: any;
};

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        position: 'relative'
      },
      footer: {
        paddingTop: theme.spacing(2.5),
        paddingBottom: theme.spacing(2.5)
      },
      footerContent: {
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center'
      },
      footerMenu: {
        listStyle: 'none',
        padding: 0,
        '& > li': {
          color: '#fff',
          display: 'inline-block',
          paddingRight: theme.spacing(1.5),
          paddingLeft: theme.spacing(1.5)
        },
        '& > li:first-of-type': {
          paddingLeft: 0
        }
      },
      footerCopyright: {
        color: '#fff'
      }
    }),
  { name: 'LoginFooterMenu' }
);

export default function FooterMenu({ color = 'inherit' }: FooterMenuProps) {
  const { useAppMenu, getSetting } = useGlobal();

  const primaryFooterMenu = useAppMenu('core', 'primaryFooterMenu');
  const secondaryFooterMenu = useAppMenu('core', 'secondaryFooterMenu');
  const setting = getSetting();
  const copyright = setting?.core?.general?.site_copyright;

  const classes = useStyles();

  if (!primaryFooterMenu && !secondaryFooterMenu) return null;

  return (
    <div className={classes.footer}>
      <div className={classes.footerContent}>
        <ul className={classes.footerMenu}>
          {primaryFooterMenu
            ? primaryFooterMenu.items.map((item, index) => (
                <li key={index.toString()}>
                  <Link color={color} to={item.to} key={item.to}>
                    {item.label}
                  </Link>
                </li>
              ))
            : null}
        </ul>
        <div className={classes.footerCopyright}>
          {secondaryFooterMenu
            ? // eslint-disable-next-line no-confusing-arrow
            secondaryFooterMenu.items.map(item =>
                item.name === 'copyright'
                  ? `${copyright} ${new Date().getFullYear()}`
                  : item.label
              )
            : null}
        </div>
      </div>
    </div>
  );
}
