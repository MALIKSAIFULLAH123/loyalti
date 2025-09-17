/**
 * @type: dialog
 * name: core.dialog.ChooseTheme
 */

import {
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle
} from '@metafox/dialog';
import { DEFAULT_THEME, useGlobal } from '@metafox/framework';
import { Button, Divider, Grid, Theme, Box, styled } from '@mui/material';
import { createStyles, makeStyles } from '@mui/styles';
import React from 'react';

interface ItemViewShape {
  id: string;
  title: string;
  image?: string;
}

export interface ItemViewProps extends ItemViewShape {
  selected?: boolean;
  onChange: (value: string) => void;
}

const Root = styled(Box, { name: 'ThemeItem', slot: 'Root' })<{
  selected?: boolean;
}>(({ theme, selected }) => ({
  borderRadius: theme.shape.borderRadius,
  overflow: 'hidden',
  border: `2px solid ${
    selected ? theme.palette.primary.main : 'rgba(0,0,0,0.1)'
  }}`
}));

const Content = styled(Box, { name: 'ThemeItem', slot: 'Content' })(
  ({ theme }) => ({
    borderRadius: theme.shape.borderRadius,
    display: 'block',
    paddingBottom: '60%',
    backgroundColor: 'rgba(0,0,0,0.1)',
    backgroundSize: 'cover'
  })
);

export function ItemView({
  title,
  id,
  image: url,
  selected,
  onChange
}: ItemViewProps) {
  return (
    <Root selected={selected}>
      <Content
        title={title}
        onClick={() => onChange(id)}
        style={{ backgroundImage: `url(${url})` }}
      />
    </Root>
  );
}

const useStyles = makeStyles(
  (theme: Theme) =>
    createStyles({
      content: {
        display: 'flex',
        flexWrap: 'nowrap',
        flexDirection: 'row'
      },
      divider: {
        marginLeft: theme.spacing(2),
        marginRight: theme.spacing(2)
      },
      dialogActions: {
        margin: theme.spacing(1),
        '& button': {
          width: 94,
          height: 40
        }
      }
    }),
  {
    name: 'ChooseThemeDialog'
  }
);

export default function ChooseThemeDialog() {
  const { useDialog, getSetting, i18n, usePreference } = useGlobal();
  const { themeId = DEFAULT_THEME } = usePreference();
  const { dialogProps, closeDialog, setDialogValue } = useDialog();

  const [themeValue, setThemeValue] = React.useState<string>(themeId);

  const classes = useStyles();
  const allVariants = getSetting('layout.variants', []);

  const handleSubmit = React.useCallback(() => {
    setDialogValue(themeValue);
  }, [setDialogValue, themeValue]);

  const handleVariantChange = React.useCallback((value: string) => {
    setThemeValue(value);
  }, []);

  return (
    <Dialog {...dialogProps} maxWidth="sm" data-testid="dialogThemePicker">
      <DialogTitle>
        {i18n.formatMessage({ id: 'choose_your_preferred_theme' })}
      </DialogTitle>
      <DialogContent>
        <div className={classes.content}>
          <Grid container columnSpacing={1} rowSpacing={2}>
            {allVariants.map(item => (
              <Grid item key={item.id?.toString()} xs={12}>
                <ItemView
                  {...item}
                  image={item.image}
                  selected={item.id === themeValue}
                  onChange={handleVariantChange}
                />
              </Grid>
            ))}
          </Grid>
        </div>
      </DialogContent>
      <Divider className={classes.divider} />
      <DialogActions className={classes.dialogActions}>
        <Button
          onClick={handleSubmit}
          variant="contained"
          color="primary"
          disabled={!themeValue}
          data-testid="buttonSubmit"
        >
          {i18n.formatMessage({ id: 'apply' })}
        </Button>
        <Button
          onClick={closeDialog}
          type="button"
          variant="outlined"
          color="primary"
          data-testid="buttonCancel"
        >
          {i18n.formatMessage({ id: 'cancel' })}
        </Button>
      </DialogActions>
    </Dialog>
  );
}
