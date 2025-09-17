import { OptionItemShape } from '@metafox/framework';
import { FormElementShape, FormSchemaShape } from '@metafox/form';
import { Theme } from '@mui/material';
import { range, uniq, get } from 'lodash';

export function toSlotArray<T = any>(items: Record<string, any>): T[] {
  if (!items) {
    return [];
  }

  return Object.keys(items)
    .map((key: string, index: number) => ({
      ...items[key],
      slotName: key,
      priority: items[key].priority ?? index
    }))
    .sort((a, b) => a.priority - b.priority)
    .map((a, index) => ({ ...a, priority: index }));
}

export function getElementPath(parentPath: string, name: string): string {
  return `${parentPath}.elements.${name}`;
}

export function parseElementPath(elementPath: string): [string, string] {
  const arr = elementPath.split('.elements.');
  const name = arr.pop();

  return [name, arr.join('.elements.')];
}

export function getSlotMaxWidthOptions(theme: Theme): OptionItemShape[] {
  const options = [{ value: 'none', label: 'No' }];
  const points = theme.layoutSlot.points;

  Object.keys(points)
    .filter(v => 0 < points[v])
    .forEach(k => {
      options.push({ value: k, label: `${points[k]} px` });
    });

  return options;
}

export function getSlotMaxContentWidthOptions(theme: Theme): OptionItemShape[] {
  const options = [{ value: 'none', label: 'No' }];
  const points = theme.layoutSlot.points;

  Object.keys(points)
    .filter(v => 0 < points[v])
    .forEach(k => {
      options.push({ value: k, label: `${points[k]} px` });
    });

  return options;
}

export function getSpacingOptions(
  name: string,
  limit: number,
  theme: any
): {
  value: string;
  label: string;
}[] {
  return [
    {
      value: 'none',
      label: '0'
    }
  ].concat(
    range(1, limit).map(x => ({
      value: x.toString(),
      label: `${theme.spacing(x)}px`
    }))
  );
}

export function getSlotFreezeOptions(): OptionItemShape[] {
  const options = [
    {
      value: 0,
      label: 'No'
    }
  ].concat(
    range(1, 3).map(key => ({
      value: key,
      label: `${key} rows`
    }))
  );

  return options as any;
}

export function getSlotBackgroundOptions(theme: Theme) {
  return [
    { label: 'No', value: 'none' },
    { label: 'Paper', value: 'background.paper' }
  ];
}

export function getFlexWeightOptions(): OptionItemShape[] {
  return [{ value: '', label: "Don't use flex weight" }].concat(
    range(1, 5).map(x => ({ value: x.toString(), label: x.toString() }))
  );
}

export function getSlotWidthOptions(slotName: string): OptionItemShape[] {
  return range(1, 12).map(x => ({
    value: x.toString(),
    label: `${x}/12 of row`
  }));
}

export function createEmptyDialogFormSchema(title: string): FormSchemaShape {
  const formSchema: FormSchemaShape = {
    component: 'Form',
    dialog: true,
    elements: {
      header: {
        component: 'dialogHeader',
        elements: {
          title: {
            component: 'Typo',
            tagName: 'h2',
            plainText: title
          }
        }
      },
      content: {
        component: 'dialogContent',
        elements: {}
      },
      footer: {
        component: 'dialogFooter',
        elements: {}
      }
    }
  };

  return formSchema;
}

export function getDividerVariantOptions(theme: Theme) {
  const data = theme.blockDivider;

  return [{ label: 'None', value: '' }].concat(
    Object.keys(data).map(x => ({ label: x, value: x }))
  );
}

export function getDividerColorOptions(theme: Theme) {
  return Object.keys(theme.palette.dividerColor).map(x => ({
    label: x,
    value: x
  }));
}

type OverrideShape = Partial<FormElementShape>;

export function selectTextColorField(theme: Theme, overrides: OverrideShape) {
  return Object.assign(
    {
      component: 'Dropdown',
      label: 'Text Color',
      size: 'medium',
      margin: 'normal',
      variant: 'outlined',
      defaultValue: 'none',
      fullWidth: true,
      displayEmpty: true,
      options: [
        { label: 'Inherit', value: 'none' },
        { label: 'Text Primary', value: 'text.primary' },
        { label: 'Text Secondary', value: 'text.secondary' },
        { label: 'Text Disabled', value: 'text.disabled' },
        { label: 'Text Hint', value: 'text.hint' }
      ]
    },
    overrides
  );
}

export function selectFontWeightField(theme: Theme, overrides: OverrideShape) {
  return Object.assign(
    {
      component: 'Dropdown',
      label: 'Font Weight',
      size: 'medium',
      margin: 'normal',
      variant: 'outlined',
      defaultValue: '',
      fullWidth: true,
      displayEmpty: true,
      options: [
        { label: 'Inherit', value: 'inherit' },
        { label: 'Light', value: 300 },
        { label: 'Regular', value: 400 },
        { label: 'Medium', value: 500 },
        { label: 'Bold', value: 700 }
      ]
    },
    overrides
  );
}

export function selectTypographyVariantField(
  theme: Theme,
  overrides: OverrideShape
) {
  return Object.assign(
    {
      component: 'Dropdown',
      label: 'Typography',
      variant: 'outlined',
      margin: 'normal',
      sx: { minWidth: 220 },
      defaultValue: 'h3',
      options: [
        { label: 'H1', value: 'h1' },
        { label: 'H2', value: 'h2' },
        { label: 'H3', value: 'h3' },
        { label: 'H4', value: 'h4' },
        { label: 'H5', value: 'h5' },
        { label: 'H6', value: 'h6' }
      ]
    },
    overrides
  );
}

export function selectTypographyComponentField(
  theme: Theme,
  overrides: OverrideShape
) {
  return Object.assign(
    {
      component: 'Dropdown',
      variant: 'outlined',
      margin: 'normal',
      fullWidth: true,
      defaultValue: 'h3',
      options: [
        { label: 'H1', value: 'h1' },
        { label: 'H2', value: 'h2' },
        { label: 'H3', value: 'h3' },
        { label: 'H4', value: 'h4' },
        { label: 'H5', value: 'h5' },
        { label: 'H6', value: 'h6' }
      ]
    },
    overrides
  );
}

export function selectBackgroundColorField(
  theme: Theme,
  overrides: OverrideShape
) {
  return Object.assign(
    {
      component: 'Dropdown',
      label: 'Background Color',
      variant: 'outlined',
      margin: 'normal',
      sx: { minWidth: 220 },
      defaultValue: 'none',
      description: 'Choose predefined background color in theme settings',
      options: [
        { label: 'No', value: 'none' },
        { label: 'Paper', value: 'background.paper' },
        { label: 'Auto', value: 'background.auto' }
      ]
    },
    overrides
  );
}

export function selectBorderRadiusField(
  theme: Theme,
  overrides: OverrideShape
) {
  return Object.assign(
    {
      component: 'Dropdown',
      label: 'Border Radius',
      size: 'medium',
      margin: 'normal',
      sx: { minWidth: 220 },
      defaultValue: 0,
      description:
        // eslint-disable-next-line max-len
        `This setting will base on config "Edit Styling → Shape → borderRadius(baseRadius: ${theme.shape.borderRadius})" \n Example: 8px -> 1 * baseRadius`,
      options: [
        { label: 'No', value: 0 },
        { label: '4px (0.5x)', value: 0.5 },
        { label: '8px (1x)', value: 1 },
        { label: '12px (1.5x)', value: 1.5 }
      ]
    },
    overrides
  );
}

export function selectPaddingField(
  theme: Theme,
  overrides: OverrideShape & Required<{ prefix: string; description: string }>
) {
  return Object.assign(
    {
      component: 'PaddingSpaceGroup',
      fullWidth: true
    },
    overrides
  );
}

export function selectDividerField(theme: Theme, overrides: OverrideShape) {
  return Object.assign(
    {
      component: 'Dropdown',
      label: 'Divider',
      variant: 'outlined',
      margin: 'dense',
      defaultValue: 'none',
      sx: { minWidth: 220 },
      options: getDividerVariantOptions(theme),
      style: { width: '100%' },
      showWhen: ['falsy', 'noFooter']
    },
    overrides
  );
}

export function selectMarginField(
  theme: Theme,
  overrides: OverrideShape & Required<{ prefix: string; description: string }>
) {
  return Object.assign(
    {
      component: 'MarginSpaceGroup',
      fullWidth: true
    },
    overrides
  );
}

export function selectHeightField(theme: Theme, overrides: OverrideShape) {
  return Object.assign(
    {
      component: 'Text',
      label: 'Height',
      placeholder: '56px...',
      fullWidth: true,
      size: 'medium',
      margin: 'normal',
      variant: 'outlined'
    },
    overrides
  );
}

export function selectAlginItemsField(theme: Theme, overrides: OverrideShape) {
  return Object.assign(
    {
      component: 'Dropdown',
      label: 'Align Items',
      size: 'medium',
      margin: 'normal',
      variant: 'outlined',
      defaultValue: 'none',
      fullWidth: true,
      displayEmpty: true,
      description: 'Refer to css flex box align-items property.',
      options: [
        { label: 'No', value: 'none' },
        { label: 'Center', value: 'center' },
        { label: 'Start', value: 'flex-start' },
        { label: 'End', value: 'flex-end' }
      ]
    },
    overrides
  );
}

export function selectAlginContentField(
  theme: Theme,
  overrides: OverrideShape
) {
  return Object.assign(
    {
      component: 'Dropdown',
      label: 'Align Content',
      size: 'medium',
      margin: 'normal',
      variant: 'outlined',
      defaultValue: 'none',
      fullWidth: true,
      displayEmpty: true,
      description: 'Refer to css flex box align-content property.',
      options: [
        { label: 'No', value: 'none' },
        { label: 'Start', value: 'flex-start' },
        { label: 'Center', value: 'center' },
        { label: 'End', value: 'flex-end' },
        { label: 'Space between', value: 'space-between' },
        { label: 'Space around', value: 'space-around' }
      ]
    },
    overrides
  );
}

export function selectJustifyContentField(
  theme: Theme,
  overrides: OverrideShape
) {
  return Object.assign(
    {
      component: 'Dropdown',
      label: 'Justify Contents',
      size: 'medium',
      margin: 'normal',
      variant: 'outlined',
      defaultValue: 'none',
      fullWidth: true,
      displayEmpty: true,
      description: 'Refer to css flex box justify-content property.',
      options: [
        { label: 'No', value: 'none' },
        { label: 'Center', value: 'center' }
      ]
    },
    overrides
  );
}

export function selectTextAlginField(theme: Theme, overrides: OverrideShape) {
  return Object.assign(
    {
      component: 'Dropdown',
      label: 'Text Align',
      size: 'medium',
      margin: 'normal',
      variant: 'outlined',
      defaultValue: 'none',
      fullWidth: true,
      displayEmpty: true,
      options: [
        { label: 'No', value: 'none' },
        { label: 'Center', value: 'center' }
      ]
    },
    overrides
  );
}

export function selectSlotMinHeightField(
  theme: Theme,
  overrides: OverrideShape
) {
  return Object.assign(
    {
      component: 'Dropdown',
      label: 'Minimum height',
      variant: 'outlined',
      margin: 'normal',
      defaultValue: '',
      displayEmpty: true,
      fullWidth: true,
      options: [
        { value: 'none', label: 'No' },
        { value: 'screen', label: 'Screen Height' },
        { value: 'appbar', label: 'AppBar Height' }
      ]
    },
    overrides
  );
}

function getLookupMediaSizes(size, allSizes) {
  const foundIndex = allSizes.indexOf(size);

  return uniq(
    [size]
      .concat(
        allSizes.slice(0, foundIndex).reverse(),
        allSizes.slice(foundIndex + 1)
      )
      .filter(x => !!x)
  );
}

export function getItemsPerRowOnMediaScreen(size, grid, allSizes) {
  const sizeApproxiate = getLookupMediaSizes(size, allSizes).find(
    size => grid[size]
  );
  const gridNumberPerRow = 12 / parseFloat(get(grid, sizeApproxiate) || 12);

  return gridNumberPerRow;
}
