import { ItemViewProps } from '@metafox/ui';

export interface ActivityPointItem {
  label: string;
  value: number;
  hint: string;
}

export interface ActivityDataShape {
  id: number;
  items: ActivityPointItem[];
}

export interface ActivityItemActions {
  purchase: () => void;
}

export type ActivityItemProps = ItemViewProps<
  ActivityDataShape,
  ActivityItemActions
>;
