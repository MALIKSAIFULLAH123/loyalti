/**
 * @type: formElement
 * name: form.element.BlockStylePreset
 */
import { useGlobal } from '@metafox/framework';
import { ButtonList } from '@metafox/ui';
import {
  Button,
  FormControl,
  InputLabel,
  MenuItem,
  Select
} from '@mui/material';
import clsx from 'clsx';
import { useField } from 'formik';
import { map } from 'lodash';
import React from 'react';
import { FormFieldProps } from '@metafox/form';
import useStyles from './styles';

export default function BlockStylePresetField({
  config,
  name
}: FormFieldProps) {
  const { label } = config;
  const [field] = useField(name);
  const { layoutBackend, jsxBackend, dialogBackend } = useGlobal();

  const [open, setOpen] = React.useState<boolean>(false);

  const [presets, setPresets] = React.useState(layoutBackend.getBlockPresets());
  const classes = useStyles();

  const addNew = () => {
    const data = window.prompt('Style Name');

    if (!data) {
      return;
    }

    if (presets[name]) {
      dialogBackend.alert({ message: 'Duplicated Style Name' });

      return;
    }

    presets[name] = {};
    setPresets({ ...presets });
  };

  const togglePreview = () => setOpen(x => !x);
  const PreviewBlock = jsxBackend.get('layout.block.StylePreviewBlock');

  return (
    <div>
      <div
        className={clsx(classes.previewSlot, open && classes.previewSlotOpen)}
      >
        <PreviewBlock {...presets[field.value]} />
      </div>
      <div style={{ display: 'flex', alignItems: 'center' }}>
        <div style={{ flex: 1, marginRight: 16 }}>
          <FormControl fullWidth margin="normal">
            <InputLabel>{label}</InputLabel>
            <Select {...field} placeholder="No Preset">
              <MenuItem value="none">No Style</MenuItem>
              {map(presets, (_, key) => (
                <MenuItem key={key} value={key}>
                  {key}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
        </div>
        <ButtonList>
          <Button
            variant="text"
            size="medium"
            onClick={togglePreview}
            disableRipple
          >
            Preview
          </Button>
          <Button variant="text" size="medium" onClick={addNew} disableRipple>
            Add New
          </Button>
        </ButtonList>
      </div>
    </div>
  );
}
