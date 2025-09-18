import { BlockViewProps } from '@metafox/framework';
import {
  EmbedItemInFeedItemProps,
  ItemShape,
  ItemViewProps,
  ItemExtraShape
} from '@metafox/ui';
import { ButtonProps } from '@mui/material';

export interface ListingItemShape extends ItemShape {
  title: string;
  description: string;
  location: Record<string, any>;
  categories: any;
  text: string;
  attach_photos: string[];
  tags: string[];
  is_sold: boolean;
  extra: ItemExtraShape & {
    can_invite?: boolean;
    can_payment?: boolean;
    can_message?: boolean;
    can_show_message?: boolean;
    can_show_payment_button?: boolean;
  };
  is_expired?: boolean;
  is_free?: boolean;
  expires_label?: string;
  has_payment_gateway?: boolean;
  expires_label_detail?: string;
  expires_label_item?: string;
  external_link?: string;
}

export interface ListingImageItemShape extends ItemShape {
  image: Record<string, any>;
}

export type ListingItemActions = {
  approveList: () => void;
  deleteList: () => void;
  paymentItem: () => void;
};

export type ListingItemState = {
  menuOpened?: boolean;
};

export type ListingItemProps = ItemViewProps<
  ListingItemShape,
  ListingItemActions,
  ListingItemState
> & {
  categories: any;
};

export type EmbedListingInFeedItemProps =
  EmbedItemInFeedItemProps<ListingItemShape>;

export type MarketplaceDetailViewProps = ListingItemProps &
  BlockViewProps & {
    isModalView?: boolean;
  };

export type AppState = {};

export interface PaymentButtonShape extends ButtonProps {
  value: string;
  label: string;
}
export interface InvoiceTableItemShape {
  label: string;
  value: string;
}
export interface MarketplaceInvoiceItemShape extends ItemShape {
  is_purchased: boolean;
  status: string;
  status_label: string;
  payment_buttons: PaymentButtonShape[];
  transactions: string[];
  payment_date: string;
  listing: string;
  table_fields: InvoiceTableItemShape[];
}

export type MarketplaceInvoiceItemProps = ItemViewProps<
  MarketplaceInvoiceItemShape,
  MarketplaceInvoiceItemActions
>;

export type InvoiceDetailViewProps = MarketplaceInvoiceItemProps &
  BlockViewProps;

export interface MarketplaceInvoiceItemActions {}
