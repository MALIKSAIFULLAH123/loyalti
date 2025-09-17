import { get, isArray } from 'lodash';

const fill = (char: string, length: number): string => {
  let c = '';
  for (let i = 0; i < length; ++i) {
    c += char;
  }

  return c;
};

type ItemShape = {
  label: string;
  value: number;
  prefix?: string;
  disabled?: boolean;
  level?: number;
  parent_id?: number;
  ordering?: number;
  is_active?: boolean;
};

function toOptionsRecursive(
  dest: ItemShape[],
  options: ItemShape[],
  subOptions: Record<string, ItemShape[]>,
  allowParent: boolean,
  subOptionKey: string,
  prefix: string,
  level: number, // forward level
  allowLevel: number, // transform maximum level, initial level =1,
  valueProp: string,
  labelProp: string
): void {
  if (!options?.length) return;

  const filled = level ? fill(prefix, level) : '';

  options.forEach(option => {
    if (!option) return;

    const children =
      get(option, subOptionKey) || get(subOptions, option[valueProp]);

    const disabled =
      !allowParent && children && isArray(children) && children.length;

    dest.push({
      value: option[valueProp],
      label: option[labelProp],
      prefix: filled,
      disabled: Boolean(option.disabled || disabled),
      level: option.level,
      is_active: option.is_active
    });

    if (allowLevel && level >= allowLevel) {
      return;
    }

    if (children && isArray(children) && children.length)
      toOptionsRecursive(
        dest,
        children,
        subOptions,
        allowParent,
        subOptionKey,
        prefix,
        level + 1,
        allowLevel,
        valueProp,
        labelProp
      );
  });
}

const sortSub = (data, destSort, optionsSort) => {
  data.forEach(x => {
    const sub = optionsSort.filter(y => y.parent_id === x.value);
    destSort.push(x);

    if (sub.length < 1) return;

    sortSub(sub, destSort, optionsSort);
  });
};

export function toOptions(
  options: ItemShape[], // array of initial options
  subOptions: Record<string, ItemShape[]>, // has "subOptions"
  allowParent: boolean = false, // allow or disabled parent options
  subOptionKey: string = 'options', // if you are using "options" or "categories"
  prefix: string = '--', // prefix on choice field
  allowLevel: number = 0, // maximum allow level
  valueProp: string = 'value',
  labelProp: string = 'label'
): ItemShape[] {
  // need improve, some app API response old structure
  const dest: ItemShape[] = [];
  const destSort: ItemShape[] = [];
  const isHaveLevel = options?.length && options[0]?.level;

  if (isHaveLevel) {
    const optionsSort = options.sort(
      (a, b) =>
        a.level - b.level ||
        a.parent_id - b.parent_id ||
        a.ordering - b.ordering ||
        a.value - b.value
    );
    const mainLevel = options.filter(x => x?.level === 1);

    if (mainLevel.length) {
      sortSub(mainLevel, destSort, optionsSort);
    }
  }

  toOptionsRecursive(
    dest,
    isHaveLevel ? destSort : options,
    subOptions,
    allowParent,
    subOptionKey,
    prefix,
    0,
    allowLevel,
    valueProp,
    labelProp
  );

  return dest;
}

export default toOptions;
