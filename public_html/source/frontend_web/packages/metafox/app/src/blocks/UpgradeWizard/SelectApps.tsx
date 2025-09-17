/**
 * @type: ui
 * name: app.step.SelectApps
 * bundle: admincp
 */
import { useUpgradeState } from '@metafox/app/hooks';
import { createBlock, useGlobal } from '@metafox/framework';
import { useStepNavigate } from '@metafox/ui/steps';
import { Box, Button, Checkbox, FormControlLabel } from '@mui/material';
import React from 'react';
import { AppItemShape } from './types';

function AppItem({
  item,
  checked,
  onChange
}: {
  item: AppItemShape;
  checked: boolean;
  onChange: any;
}) {
  return (
    <Box
      component="div"
      pr={1}
      sx={{
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center'
      }}
    >
      <FormControlLabel
        onChange={onChange}
        control={<Checkbox checked={checked} disabled={item?.required} />}
        label={item.name}
      />
      v{item?.version}
    </Box>
  );
}

function SelectApps() {
  const { dispatch, i18n } = useGlobal();
  const [next] = useStepNavigate();
  const initialValues = useUpgradeState();
  const { recommendApps: data } = initialValues;
  const [apps, setApps] = React.useState<AppItemShape[]>(
    initialValues.selectedApps
  );
  const handleChecked = React.useCallback(
    (id: string, value: boolean) => {
      let newApps: AppItemShape[];

      if (value && id === 'all') {
        newApps = data.map(x => ({
          identity: x.identity,
          name: x.name,
          version: x.version
        }));
      } else if (id === 'all' && !value) {
        newApps = data
          .filter(x => x.required)
          .map(x => ({
            identity: x.identity,
            name: x.name,
            version: x.version
          }));
      } else if (apps.map(x => x.identity).includes(id)) {
        newApps = apps.filter(x => x.identity !== id);
      } else {
        const x = data.find(x => x.identity === id);

        newApps = [
          ...apps,
          { identity: x.identity, name: x.name, version: x.version }
        ];
      }

      setApps(newApps);
      dispatch({ type: '@install/update', payload: { selectedApps: newApps } });
    },
    [apps, setApps, data, dispatch]
  );

  return (
    <Box>
      <Box component="div">
        <FormControlLabel
          onChange={(evt: unknown, value: boolean) =>
            handleChecked('all', value)
          }
          control={
            <Checkbox
              checked={apps.length === data.length && data.length > 0}
            />
          }
          label={'All'}
        />
      </Box>
      <Box>
        {data.map(item => {
          return (
            <AppItem
              item={item}
              key={item.identity}
              onChange={(evt: unknown, value: boolean) =>
                handleChecked(item.identity, value)
              }
              checked={apps.map(x => x.identity).includes(item.identity)}
            />
          );
        })}
      </Box>
      <Box sx={{ pt: 2 }}>
        <Button
          variant="contained"
          color="primary"
          size="small"
          data-testid="buttonSubmit"
          onClick={next}
        >
          {i18n.formatMessage({ id: 'Continue' })}
        </Button>
      </Box>
    </Box>
  );
}

export default createBlock({
  extendBlock: SelectApps
});
