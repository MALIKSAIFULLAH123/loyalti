import { BlockViewProps } from '@metafox/framework';
import {
  EmbedItemInFeedItemProps,
  ItemExtraShape,
  ItemShape,
  ItemViewProps
} from '@metafox/ui';

interface EventExtraShape extends ItemExtraShape {
  can_create_discussion: boolean;
  can_manage_pending_post: boolean;
  can_view_discussion: boolean;
  can_view_hosts: boolean;
  can_view_members: boolean;
  can_rsvp: boolean;
  can_invite: boolean;
  can_remove_host: boolean;
  can_manage_host: boolean;
}
export interface EventItemShape extends ItemShape {
  title: string;
  is_host?: boolean;
  is_pending_host_invite: boolean;
  description: string;
  location?: Location;
  start_time?: string;
  event_url?: string;
  end_time?: string;
  rsvp?: number;
  extra?: EventExtraShape;
  pending_invite: string;
  text?: string;
  status?: 0 | 1 | 2;
  categories?: string[];
  coordinate?: {
    latitude: number;
    longitude: number;
  };
  is_ended?: boolean;
  image_position?: string;
}

export interface Location {
  address: string;
  lat: number;
  lng: number;
  short_name?: string;
}

export interface InviteItemShape {
  resource_name: string;
  event_id: number;
  type_id: number;
  rsvp_id: number;
  user: {
    user_name: string;
    full_name: string;
    id: 2;
  };
  id: 14;
  creation_date: string;
  link: string;
}

export type InviteItemProps = ItemViewProps<InviteItemShape, {}, {}>;

export type EventItemActions = {
  approveCoHostInvite: () => void;
  denyCoHostInvite: () => void;
  interestedEvent: () => void;
  notInterestedEvent: () => void;
  joinEvent: () => void;
  cancelInvitation: (onSuccess: any) => void;
  cancelHostInvitation: (onSuccess: any) => void;
  removeHost: (onSuccess: any) => void;
  removeMemberGuest: (onSuccess: any) => void;
  showMutualFriends: () => void;
};

export type EventItemState = { menuOpened?: boolean };

export type EventItemProps = ItemViewProps<
  EventItemShape,
  EventItemActions,
  EventItemState
>;

export type EmbedEventItemInFeedProps =
  EmbedItemInFeedItemProps<EventItemShape> & BlockViewProps;

export type AppState = {};

export type EventDetailViewProps = EventItemProps & BlockViewProps;
