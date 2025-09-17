import { AppUIConfig } from '@metafox/framework';

const initialState: AppUIConfig = {
  sidebarHeader: {
    homepageHeader: {
      title: 'friends',
      icon: 'ico-user1-two',
      to: '/friend'
    }
  },
  sidebarSearch: {
    placeholder: 'Search'
  },
  menus: {
    sidebarMenu: {
      items: [
        {
          to: '/friends',
          label: 'All friends',
          testid: 'all_friend',
          icon: 'ico-hashtag',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          to: '/friends/incoming',
          label: 'Incoming Requests',
          tab: 'incoming',
          testid: 'incoming_request',
          icon: 'ico-user2-down-o',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          to: '/friends/pending',
          label: 'Pending Requests',
          tab: 'pending',
          testid: 'pending_request',
          icon: 'ico-user3-clock-o',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          to: '/friends/lists',
          label: 'Friend Lists',
          tab: 'friend_lists',
          testid: 'friend_lists',
          icon: 'ico-list-o',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          as: 'sidebarButton',
          icon: 'ico-plus',
          value: 'friend/addNewList',
          label: 'Add New List',
          buttonProps: {
            fullWidth: true,
            color: 'primary',
            variant: 'contained'
          },
          testid: 'add_new_list',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        }
      ]
    }
  }
};

export default initialState;
