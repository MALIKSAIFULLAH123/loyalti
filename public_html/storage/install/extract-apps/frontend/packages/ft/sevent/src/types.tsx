import { BlockViewProps } from '@metafox/framework';
import {
  EmbedItemInFeedItemProps,
  ItemShape,
  ItemViewProps
} from '@metafox/ui';

export interface SeventItemShape extends Omit<ItemShape, 'user'> {
  title: string;
  description: string;
  user: string;
  text: string;
  is_draft?: boolean;
  categories?: string[];
  attachments: string[];
  tags?: string[];
}

export interface SeventItemActions {
  deleteItem: () => void;
  approveItem: () => void;
  addTicketItem: () => void;
}

export interface SeventItemState {
  menuOpened: boolean;
}

export type EmbedSeventItemInFeedItemProps =
  EmbedItemInFeedItemProps<SeventItemShape>;

export type SeventItemProps = ItemViewProps<
  SeventItemShape,
  SeventItemActions,
  SeventItemState
> & {
  isModalView?: boolean;
};

export interface AppState {
  entities: {
    sevent: Record<string, SeventItemShape>;
  };
}

export type SeventDetailViewProps = SeventItemProps & BlockViewProps;

export interface InvoiceTableItemShape {
  label: string;
  value: string;
}
export interface SeventInvoiceItemShape extends ItemShape {
  is_purchased: boolean;
  status: string;
  status_label: string;
  transactions: string[];
  payment_date: string;
  listing: string;
  table_fields: InvoiceTableItemShape[];
}

export type SeventInvoiceItemProps = ItemViewProps<
  SeventInvoiceItemShape,
  SeventInvoiceItemActions
>;

export type InvoiceDetailViewProps = SeventInvoiceItemProps &
  BlockViewProps;

export interface SeventInvoiceItemActions {}
