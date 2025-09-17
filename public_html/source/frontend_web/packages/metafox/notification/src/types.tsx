import { AppResource, AppUIConfig } from '@metafox/framework';
import { ItemShape, ItemViewProps } from '@metafox/ui';

export type NotificationItemShape = {
  title: string;
  message: string;
  is_read: boolean;
  is_notified: boolean;
  asModal?: boolean;
  target?: '_blank' | '_self' | '_parent' | '_top' | string;
} & ItemShape;

export type NotificationItemActions = {
  markAsRead: () => void;
  markAllRead: () => void;
  deleteItem: () => void;
  viewItem: () => void;
};

export type NotificationItemProps = ItemViewProps<
  NotificationItemShape,
  NotificationItemActions
>;

export type NotificationItemState = {};

export type AppState = {
  entities: {
    notification: Record<string, NotificationItemShape>;
  };
  resourceConfig: {
    notification: AppResource;
  };
  uiConfig: AppUIConfig;
};
