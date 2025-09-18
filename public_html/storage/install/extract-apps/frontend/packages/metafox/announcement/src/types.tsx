import { ItemShape, ItemViewProps } from '@metafox/ui';

export interface AnnouncementItemShape extends ItemShape {
  title: string;
  description: string;
  text: string;
  [key: string]: any;
}

export type AnnouncementItemProps = ItemViewProps<
  AnnouncementItemShape,
  AnnouncementItemActions
>;

export type AnnouncementItemActions = {
  closeAnnouncement: (onSuccess: any) => void;
};

export interface AppState {
  loaded?: boolean;
  statistic: {
    total?: number;
  };
}
