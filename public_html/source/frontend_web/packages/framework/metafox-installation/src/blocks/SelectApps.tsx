/**
 * @type: block
 * name: install.SelectApps
 * bundle: installation
 */
import { createBlock, GlobalState, useGlobal } from '@metafox/framework';
import {
  HelpBlock,
  InstallMenu,
  Panel,
  PanelBody,
  PanelContent,
  PanelFooter,
  PanelHeader,
  useInstallationState
} from '@metafox/installation';
import {
  Box,
  Button,
  Checkbox,
  FormControlLabel,
  LinearProgress,
  Typography
} from '@mui/material';
import React from 'react';
import { AppItemShape } from '../types';

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
  const { dispatch } = useGlobal();
  const initialValues = useInstallationState();
  const { recommendApps: data, recommendAppsLoaded } = initialValues;
  const [apps, setApps] = React.useState<
    GlobalState['installation']['selectedApps']
  >(initialValues.selectedApps);

  const handleChecked = React.useCallback(
    (id: string, value: boolean) => {
      let newApps: GlobalState['installation']['selectedApps'];

      if (value && id === 'all') {
        newApps = data.map(x => ({
          identity: x.identity,
          name: x.name,
          version: x.version,
          release_channel: x.version_detail?.release_channel
        }));
      } else if (id === 'all' && !value) {
        newApps = data
          .filter(x => x.required)
          .map(x => ({
            identity: x.identity,
            name: x.name,
            version: x.version,
            release_channel: x.version_detail?.release_channel
          }));
      } else if (apps.map(x => x.identity).includes(id)) {
        newApps = apps.filter(x => x.identity !== id);
      } else {
        const x = data.find(x => x.identity === id);

        newApps = [
          ...apps,
          {
            identity: x.identity,
            name: x.name,
            version: x.version,
            release_channel: x.version_detail?.release_channel
          }
        ];
      }

      setApps(newApps);
      dispatch({ type: '@install/update', payload: { selectedApps: newApps } });
    },
    [apps, setApps, data, dispatch]
  );

  const handleContinue = () => {
    dispatch({ type: '@install/next' });
  };

  React.useEffect(() => {
    dispatch({ type: '@install/select-apps/fetch' });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <Panel>
      <PanelHeader />
      <PanelBody hasMenu>
        <InstallMenu />
        <PanelContent data-testid="SelectApps">
          {recommendAppsLoaded && data.length ? (
            <div>
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
            </div>
          ) : null}
          {recommendAppsLoaded && !data.length ? (
            <Box>
              <Typography paragraph>There are no apps.</Typography>
            </Box>
          ) : null}
          {recommendAppsLoaded ? null : (
            <Box>
              <Typography paragraph>
                Checking downloadable apps from AppStore.
              </Typography>
              <LinearProgress variant="indeterminate" />
            </Box>
          )}
          <HelpBlock />
        </PanelContent>
      </PanelBody>
      <PanelFooter>
        <Button
          variant="contained"
          color="primary"
          onClick={handleContinue}
          data-testid="buttonContinue"
        >
          Continue
        </Button>
      </PanelFooter>
    </Panel>
  );
}

export default createBlock({
  extendBlock: SelectApps
});
