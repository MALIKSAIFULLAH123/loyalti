/**
 * @type: formElement
 * name: form.element.SegmentContainer
 */
import { Button, ButtonGroup, Theme } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import { map } from 'lodash';
import React from 'react';
import Element from '@metafox/form/Element';

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      root: {
        position: 'relative',
        paddingTop: theme.spacing(2)
      },
      header: {
        paddingBottom: theme.spacing(1)
      },
      content: {
        padding: theme.spacing(1, 0, 1, 2),
        borderLeft: '2px solid rgba(0, 0, 0, 0.1)'
      },
      srOnly: { display: 'none' },
      smallButton: { textTransform: 'none' },
      smallButtonActive: {
        color: theme.palette.primary.main
      }
    }),
  { name: 'TabContainer' }
);

export default function SegmentContainer({ config: { elements }, formik }) {
  const first = Object.keys(elements).shift();
  const [tab, setTab] = React.useState<string>(first ?? '');
  const classes = useStyles();
  const tabs = React.useMemo(
    () =>
      map(elements, (config, key) => ({
        label: config.label,
        value: key
      })),
    [elements]
  );

  if (!first) {
    return null;
  }

  return (
    <div className={classes.root}>
      <div className={classes.header}>
        <ButtonGroup variant="outlined" size="medium">
          {tabs.map(({ label, value }) => (
            <Button
              key={value.toString()}
              onClick={() => setTab(value)}
              className={tab === value && classes.smallButtonActive}
            >
              <small className={classes.smallButton}>{label}</small>
            </Button>
          ))}
        </ButtonGroup>
      </div>
      <div className={classes.content}>
        {map(elements, (config, key) => (
          <div
            key={key.toString()}
            className={tab === key ? '' : classes.srOnly}
          >
            <Element config={config} formik={formik} />
          </div>
        ))}
      </div>
    </div>
  );
}
