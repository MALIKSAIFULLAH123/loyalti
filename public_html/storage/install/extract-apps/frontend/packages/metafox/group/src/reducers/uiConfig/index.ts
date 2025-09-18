import { AppUIConfig } from '@metafox/framework';

const initialState: AppUIConfig = {
  sidebarHeader: {
    homepageHeader: {
      title: 'groups',
      icon: 'ico-user1-three-o',
      to: '/group'
    },
    mangerHeader: {
      breadcrumbsData: [
        {
          title: 'group'
        },
        { to: '/group/manage/:id', title: 'manage_group' }
      ],
      title: 'group',
      icon: 'ico-user1-three-o'
    },
    backToProfile: {
      title: 'back_to_group',
      icon: 'ico-user1-three-o'
    }
  },
  sidebarSearch: {
    placeholder: 'search_groups'
  },
  menus: {
    tabMenuProfilePhoto: {
      items: [
        {
          to: 'photo',
          label: 'Photos',
          tab: 'photo',
          testid: 'photo'
        },
        {
          to: 'album',
          label: 'Albums',
          tab: 'album',
          testid: 'album'
        }
      ]
    },
    actionMenuProfilePhoto: {
      items: [
        {
          as: 'feed.ui.addPhotoButton',
          testid: 'addPhotos'
        }
      ]
    },
    actionMenuProfileMedia: {
      items: [
        {
          as: 'feed.ui.addPhotoButton',
          testid: 'addPhotos'
        }
      ]
    },
    sidebarMenu: {
      items: [
        {
          to: '/groups',
          label: 'Home',
          testid: 'landing',
          icon: 'ico-user1-three-o',
          tab: 'landing'
        },
        {
          to: '/group/all',
          label: 'All Groups',
          testid: 'all',
          icon: 'ico-hashtag',
          tab: 'all'
        },
        {
          to: '/group/my',
          label: 'My Groups',
          testid: 'my',
          icon: 'ico-user-man-o',
          tab: 'my',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          to: '/group/friend',
          // eslint-disable-next-line quotes
          label: "Friends' Groups",
          // eslint-disable-next-line quotes
          testid: 'friend',
          tab: 'friend',
          icon: 'ico-user1-two-o',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          to: '/group/joined',
          // eslint-disable-next-line quotes
          label: 'Joined Groups',
          // eslint-disable-next-line quotes
          testid: 'joined',
          tab: 'joined',
          icon: 'ico-check-circle-o',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          to: '/group/invited',
          // eslint-disable-next-line quotes
          label: 'Invites',
          // eslint-disable-next-line quotes
          testid: 'invited',
          tab: 'invited',
          icon: 'ico-user3-plus-o',
          showWhen: ['and', ['truthy', 'session.loggedIn']]
        },
        {
          as: 'sidebarButton',
          icon: 'ico-plus',
          to: '/group/add',
          label: 'Create Group',
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
    groupManagerMenu: {
      items: [
        {
          as: 'sidebarHeading',
          label: 'Manage Group',
          testid: 'manager group '
        },
        {
          to: '/group/manage/:id/membership_questions',
          label: 'Membership Questions',
          tab: 'landing',
          testid: 'membership questions'
        },
        {
          to: '/group/manage/:id/pending_post',
          label: 'Pending Posts',
          tab: 'landing',
          testid: 'pending posts'
        },
        {
          to: '/group/manage/:id/group_rules',
          label: 'Group Rules',
          tab: 'landing',
          testid: 'group rules'
        },
        {
          to: '/group/manage/:id/report',
          label: 'Member-Reported Content',
          tab: 'landing',
          testid: 'member-reported content'
        },
        {
          to: '/group/manage/:id/moderation_rights',
          label: 'Moderation Rights',
          tab: 'landing',
          testid: 'moderation rights'
        },
        {
          to: '/group/settings/:id',
          label: 'Settings',
          tab: 'landing',
          testid: 'settings'
        }
      ]
    },
    groupManagerSettings: {
      items: [
        {
          as: 'sidebarHeading',
          label: 'Group Settings',
          testid: 'manager group '
        },
        {
          to: '/group/settings/:id/info',
          label: 'Group Info',
          tab: 'landing',
          testid: 'group info'
        },
        {
          to: '/group/settings/:id/about',
          label: 'About Group',
          tab: 'landing',
          testid: 'about group'
        },
        {
          as: 'sidebarDivider',
          testid: 'divider'
        },
        {
          as: 'sidebarHeading',
          label: 'Privacy Settings',
          testid: 'privacy settings '
        },
        {
          to: '/group/settings/:id/permissions',
          label: 'Permissions',
          tab: 'landing',
          testid: 'permissions'
        },
        {
          as: 'sidebarDivider',
          testid: 'divider'
        },
        {
          as: 'sidebarHeading',
          label: 'Group Menu Settings',
          testid: 'group menu settings '
        },
        {
          to: '/group/settings/:id/menu',
          label: 'Menu',
          tab: 'landing',
          testid: 'menu'
        }
      ]
    },
    sidebarSearchInGroupMenu: {
      items: [
        {
          label: 'Posts',
          tab: 'post',
          testid: 'manage-hidden'
        },
        {
          label: 'Photos',
          tab: 'photo',
          testid: 'manage-hidden'
        },
        {
          label: 'Videos',
          tab: 'video',
          testid: 'manage-hidden'
        }
      ]
    }
  },
  sidebarCategory: {
    dataSource: { apiUrl: '/group-type' },
    href: '/group/type',
    title: 'categories'
  }
};

export default initialState;
