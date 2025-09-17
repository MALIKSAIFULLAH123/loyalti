/**
 * @type: formElement
 * name: form.element.GridStylePreset
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

export default function GridStylePresetField({ config, name }: FormFieldProps) {
  const { label } = config;
  const [field] = useField(name);
  const { layoutBackend, jsxBackend } = useGlobal();

  const [open, setOpen] = React.useState<boolean>(false);

  const presets = layoutBackend.getGridPresets();
  const classes = useStyles();

  const addNew = () => {};

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
          <Button variant="text" size="medium" onClick={togglePreview}>
            Preview
          </Button>
          <Button variant="text" size="medium" onClick={addNew}>
            Add New
          </Button>
        </ButtonList>
      </div>
    </div>
  );
}
