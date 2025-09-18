const initialState = {
  sidebarHeader: {
    homepageHeader: {
      title: 'events',
      icon: 'ico-calendar',
      to: '/event'
    }
  },
  sidebarSearch: {
    placeholder: 'Search Events'
  },
  sidebarCategory: {
    title: 'categories',
    href: '/event/category',
    dataSource: { apiUrl: '/event-category' }
  },
  menus: {
    sidebarMenu: {
      items: [
        {
          to: '/event',
          label: 'Home',
          testid: 'landing',
          icon: 'ico-calendar-o',
          tab: 'landing'
        },
        {
          to: '/event/all',
          label: 'All Events',
          testid: 'all',
          icon: 'ico-hashtag',
          tab: 'all'
        },
        {
          to: '/event/hosted',
          label: 'Hosting Events',
          testid: 'hosted',
          icon: 'ico-user-man-o',
          tab: 'hosted'
        },
        {
          to: '/event/friend',
          label: "Friend's Events",
          testid: 'friend',
          icon: 'ico-user1-two-o',
          tab: 'friend'
        },
        {
          to: '/event/past',
          label: 'Past Events',
          testid: 'past',
          icon: 'ico-user-man-o',
          tab: 'past'
        },
        {
          to: '/event/invites',
          label: 'Invites Event',
          testid: 'invites',
          icon: 'ico-user-man-o',
          tab: 'invites'
        },
        {
          to: '/event/pending',
          label: 'Pending Events',
          testid: 'pending',
          icon: 'ico-user-man-o',
          tab: 'pending'
        },
        {
          as: 'sidebarButton',
          icon: 'ico-plus',
          to: '/event/add',
          label: 'Create New Event',
          testid: 'add',
          tab: 'add',
          showWhen: ['and', ['truthy', 'session.loggedIn']],
          buttonProps: {
            fullWidth: true,
            color: 'primary',
            variant: 'contained'
          }
        }
      ]
    },
    manageItemMenu: {
      items: [
        {
          as: 'sidebarHeading',
          label: 'Manage Event',
          testid: 'manage event'
        },
        {
          to: '/event/manage/:id',
          label: 'Event Detail',
          testid: 'general',
          icon: 'ico-calendar-o',
          tab: 'general'
        },
        {
          to: '/event/manage/:id/invite',
          label: 'Invite Guests',
          testid: 'invite_guest',
          icon: 'ico-hashtag',
          tab: 'invite_guest'
        },
        {
          to: '/event/manage/:id/guests',
          label: 'Guest List',
          testid: 'guest_list',
          icon: 'ico-user-man-o',
          tab: 'guest_list'
        },
        {
          to: '/event/manage/:id/mass-email',
          label: 'Mass Email',
          testid: 'mass_email',
          icon: 'ico-user1-two-o',
          tab: 'mass_email'
        }
      ]
    }
  }
};

export default initialState;
