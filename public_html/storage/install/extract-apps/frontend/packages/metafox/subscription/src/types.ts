import {
  ItemShape,
  ItemViewProps,
  EmbedItemInFeedItemProps
} from '@metafox/ui';
import { BlockViewProps } from '@metafox/framework';
import { ButtonProps } from '@mui/material';

export interface SubscriptionPackageItemShape extends ItemShape {
  title: string;
  description: string;
  recurring_price: string;
  is_purchased: boolean;
  is_popular: boolean;
  success_role?: string;
}

export interface PaymentButtonShape extends ButtonProps {
  value: string;
  label: string;
}

export interface SubscriptionInvoiceTableItemShape {
  label: string;
  value: string;
}
export interface SubscriptionInvoiceItemShape extends ItemShape {
  recurring_price: string;
  expired_at: Date;
  package_title: string;
  is_purchased: boolean;
  payment_status: string;
  payment_status_label: string;
  upgraded_membership: string;
  payment_buttons: PaymentButtonShape[];
  table_fields: SubscriptionInvoiceTableItemShape[];
  transactions: Record<string, any>[];
  activated_at?: Date;
}
export interface AppState {
  entities: {
    subscription_package: Record<string, SubscriptionPackageItemShape>;
  };
}

export type PackageItemProps = ItemViewProps<
  SubscriptionPackageItemShape,
  SubscriptionPackageItemActions
>;

export type InvoiceItemProps = ItemViewProps<
  SubscriptionInvoiceItemShape,
  SubscriptionInvoiceItemActions
>;

export type InvoiceDetailViewProps = InvoiceItemProps & BlockViewProps;

export type EmbedPackageItemInFeedItemProps =
  EmbedItemInFeedItemProps<SubscriptionPackageItemShape>;

export interface SubscriptionPackageItemActions {
  paymentItem: () => void;
}
export interface SubscriptionInvoiceItemActions {}
