import { BlockViewProps } from '@metafox/framework';
import { ItemViewProps } from '@metafox/ui';

export const REMOVE = 0;
export const NEW = 1;
export const UPDATE = 2;
export const MINIMUM_ANSWERS = 2;

export enum TypeQuestion {
  FreeAnswer,
  Select,
  CheckBox
}

export interface AnswerProps {
  type: number;
  value?: string;
  onRemove: () => void;
  onChange: React.ChangeEventHandler<HTMLTextAreaElement | HTMLInputElement>;
  onBlur: React.FocusEventHandler<HTMLTextAreaElement | HTMLInputElement>;
}

export interface GroupRuleShape {
  description: string;
  module_name: string;
  ordering: number;
  resource_name: string;
  title: string;
}

export type GroupRule = ItemViewProps<GroupRuleShape> & {
  description: string;
  module_name: string;
  ordering: number;
  resource_name: string;
  title: string;
};

export type GroupRuleProps = GroupRule & BlockViewProps;
