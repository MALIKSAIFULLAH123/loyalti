import { Link, useGlobal } from '@metafox/framework';
import { Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import * as React from 'react';

type Props = {
  label?: string;
  values?: Array<InfoItem>;
};

type InfoItem = { title: string; link?: string };

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        display: 'block'
      },
      label: {
        marginRight: theme.spacing(0.5)
      },
      row: {
        color: theme.palette.text.secondary
      },
      infoListing: {},
      infoItem: {
        '&:not(:last-child)': {
          '&:after': {
            content: '","',
            marginRight: theme.spacing(0.5)
          }
        }
      }
    }),
  { name: 'MuiInformationList' }
);

const MinorInformation = (props: Props) => {
  const { label, values = [] } = props;
  const classes = useStyles();
  const { i18n } = useGlobal();

  return (
    <div className={classes.root}>
      <div className={classes.row}>
        <span className={classes.label}>
          {`${i18n.formatMessage({ id: `${label}` })}:`}
        </span>
        <span className={classes.infoListing}>
          {values.map((item, index) => (
            <span className={classes.infoItem} key={index.toString()}>
              {item.link ? (
                <Link to={item.link}>{item.title}</Link>
              ) : (
                <span>{item.title}</span>
              )}
            </span>
          ))}
        </span>
      </div>
    </div>
  );
};

export default MinorInformation;
