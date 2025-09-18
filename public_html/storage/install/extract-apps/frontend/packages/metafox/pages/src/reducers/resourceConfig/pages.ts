import { AppResource } from '@metafox/framework';
import { MEMBERSHIP } from '@metafox/pages/constant';

const profileMenu = {
  items: [
    {
      to: '/',
      label: 'Overview',
      active: true,
      tab: 'home',
      testid: 'home'
    },
    {
      to: '/about',
      label: 'About',
      tab: 'about',
      testid: 'about'
    },
    {
      to: '/member',
      label: 'Members',
      tab: 'member',
      testid: 'members'
    },
    {
      to: '/photo',
      label: 'Photos',
      tab: 'photo',
      testid: 'photo'
    },
    {
      to: '/video',
      label: 'Videos',
      tab: 'video',
      testid: 'video'
    },
    {
      to: '/poll',
      label: 'polls',
      tab: 'poll',
      testid: 'poll'
    },
    {
      to: '/quiz',
      label: 'quizzes',
      tab: 'quiz',
      testid: 'quiz'
    },
    {
      to: '/listing',
      label: 'Listing',
      tab: 'marketplace',
      testid: 'marketplace'
    },
    {
      to: '/blog',
      label: 'Blogs',
      tab: 'blog',
      testid: 'blog'
    },
    {
      to: '/event',
      label: 'events',
      tab: 'event',
      testid: 'event'
    }
  ]
};

const profileActionMenu = {
  items: [
    {
      icon: 'ico-thumbup-o',
      label: 'Like',
      testid: 'like',
      color: 'primary',
      value: 'closeMenu, page/like',
      showWhen: ['and', ['falsy', 'item.is_liked']]
    },
    {
      icon: 'ico-thumbup-o',
      label: 'Liked',
      testid: 'liked',
      color: 'default',
      value: 'closeMenu, page/unlike',
      showWhen: ['and', ['truthy', 'item.is_liked']]
    },
    {
      icon: 'ico-comment-o',
      label: 'Message',
      testid: 'message',
      value: 'comingSoon'
    },
    {
      icon: 'ico-compose-alt',
      label: 'Claim Page',
      testid: 'claim',
      value: 'comingSoon'
    },
    {
      icon: 'ico-gear-o',
      label: 'Manage',
      testid: 'manage',
      value: 'closeMenu, pages/manage'
    },
    {
      icon: 'ico-diamond-o',
      label: 'Featured',
      color: 'primary',
      testid: 'feature',
      value: 'closeMenu, featureItem',
      showWhen: [
        'and',
        ['falsy', 'item.is_featured'],
        ['truthy', 'item.extra.can_feature']
      ]
    },
    {
      icon: 'ico-diamond-o',
      label: 'Un Featured',
      color: 'primary',
      testid: 'unfeature',
      value: 'closeMenu, unfeatureItem',
      showWhen: [
        'and',
        ['truthy', 'item.is_featured'],
        ['truthy', 'item.extra.can_feature']
      ]
    },
    {
      icon: 'ico-sponsor',
      label: 'Sponsor',
      color: 'primary',
      testid: 'sponsor',
      value: 'closeMenu, sponsorItem',
      showWhen: [
        'and',
        ['falsy', 'item.is_sponsor'],
        ['truthy', 'item.extra.can_sponsor']
      ]
    },
    {
      icon: 'ico-sponsor',
      label: 'Un Sponsor',
      color: 'primary',
      testid: 'unsponsor',
      value: 'closeMenu, unsponsorItem',
      showWhen: [
        'and',
        ['truthy', 'item.is_sponsor'],
        ['truthy', 'item.extra.can_sponsor']
      ]
    },
    {
      icon: 'ico-user2-next-o',
      label: 'Reassign Owner',
      color: 'primary',
      testid: 'reassign owner',
      value: 'pages/reassignOwner'
    },
    {
      icon: 'ico-share-o',
      label: 'Share',
      testid: 'share',
      value: 'closeMenu, shareToNewsFeed'
    }
  ]
};

const profilePopoverMenu = {
  items: [
    {
      icon: 'ico-comment-o',
      label: 'Message',
      testid: 'message',
      value: 'comingSoon'
    },
    {
      label: 'Report',
      icon: 'ico-warning-o',
      value: 'closeMenu, reportItem',
      testid: 'report',
      showWhen: ['and', ['truthy', 'item.extra.can_report']]
    },
    {
      label: 'Invite Members',
      icon: 'ico-user-man-plus-o',
      value: 'closeMenu, comingSoon',
      testid: 'report',
      showWhen: ['eq', 'item.membership', MEMBERSHIP]
    }
  ]
};

const itemActionMenu = {
  items: [
    {
      label: 'Edit',
      icon: 'ico-pencilline-o',
      value: 'closeMenu, editItem',
      testid: 'edit',
      showWhen: ['and', ['truthy', 'item.extra.can_edit']]
    },
    {
      label: 'Report',
      icon: 'ico-warning-o',
      value: 'closeMenu, reportItem',
      testid: 'report',
      showWhen: ['and', ['truthy', 'item.extra.can_report']]
    },
    {
      icon: 'ico-sponsor',
      label: 'Sponsor in Feed',
      value: 'closeMenu, sponsorItemInFeed',
      testid: 'sponsor_in_feed',
      showWhen: [
        'and',
        ['truthy', 'item.extra.can_sponsor_in_feed'],
        ['falsy', 'item.is_sponsored_feed']
      ]
    },
    {
      icon: 'ico-sponsor',
      label: 'Unsponsor in Feed',
      testid: 'unsponsor in feed',
      value: 'closeMenu, unsponsorItemInFeed',
      showWhen: [
        'and',
        ['truthy', 'item.extra.can_sponsor_in_feed'],
        ['truthy', 'item.is_sponsored_feed']
      ]
    },
    {
      icon: 'ico-sponsor',
      label: 'Sponsor this item',
      testid: 'sponsor',
      value: 'closeMenu, sponsorItem',
      showWhen: [
        'and',
        ['truthy', 'item.extra.can_sponsor'],
        ['falsy', 'item.is_sponsor']
      ]
    },
    {
      icon: 'ico-sponsor',
      label: 'Unsponsor this item',
      testid: 'unsponsor',
      value: 'closeMenu, unsponsorItem',
      showWhen: [
        'and',
        ['truthy', 'item.extra.can_sponsor'],
        ['truthy', 'item.is_sponsor']
      ]
    },
    {
      label: 'Feature',
      icon: 'ico-diamond',
      value: 'closeMenu, featureItem',
      testid: 'feature',
      showWhen: [
        'and',
        ['falsy', 'item.is_featured'],
        ['truthy', 'item.extra.can_feature']
      ]
    },
    {
      label: 'Un-Feature',
      icon: 'ico-diamond',
      value: 'closeMenu, unfeatureItem',
      testid: 'unfeature',
      showWhen: [
        'and',
        ['truthy', 'item.is_featured'],
        ['truthy', 'item.extra.can_feature']
      ]
    },
    {
      icon: 'ico-trash',
      label: 'Delete',
      testid: 'delete',
      value: 'closeMenu, deleteItem',
      showWhen: ['and', ['truthy', 'item.extra.can_delete']],
      className: 'itemDelete'
    }
  ]
};
const initialState: AppResource = {
  actions: {
    searchItem: {
      pageUrl: '/page/search',
      placeholder: 'Search pages'
    },
    reassignOwner: {
      apiUrl: '/'
    },
    updateProfileCover: {
      apiUrl: 'page/cover/:id',
      apiMethod: 'post'
    },
    removeProfileCover: {
      apiUrl: '/page/cover/:id',
      apiMethod: 'delete',
      confirm: {
        message: 'Are you sure you want to delete this photo?'
      }
    },
    updateAvatar: {
      apiUrl: '/page/avatar/:id'
    },
    viewAll: {
      apiUrl: '/page',
      apiRules: {
        q: ['truthy', 'q'],
        sort: [
          'includes',
          'sort',
          ['recent', 'most_viewed', 'total_member', 'most_discussed']
        ],
        type_id: ['truthy', 'type_id'],
        category_id: ['truthy', 'category_id'],
        when: ['includes', 'when', ['this_month', 'this_week', 'today']],
        view: [
          'includes',
          'view',
          ['my', 'friend', 'pending', 'invited', 'liked']
        ]
      }
    },
    viewSearchInPage: {
      apiUrl: 'feed/?item_type=pages&item_id=:id',
      apiRules: {
        q: ['truthy', 'q'],
        sort: [
          'includes',
          'sort',
          ['recent', 'most_viewed', 'total_member', 'most_discussed']
        ],
        when: ['includes', 'when', ['this_month', 'this_week', 'today']],
        view: ['includes', 'view', ['my', 'friend', 'invited', 'joined']],
        related_comment_friend_only: [
          'or',
          ['truthy', 'related_comment_friend_only'],
          ['falsy', 'related_comment_friend_only']
        ]
      }
    },
    viewItem: {
      apiUrl: '/page/:id',
      pageUrl: '/page/:id'
    },
    deleteItem: {
      apiUrl: '/page/:id',
      confirm: {
        title: 'Confirm',
        message: 'Are you sure you want to delete this item permanently?'
      }
    },
    addItem: {
      pageUrl: '/page/add',
      apiUrl: '/page/form'
    },
    editItem: {
      pageUrl: '/page/edit/:id',
      apiUrl: '/page/form/:id'
    },
    sponsorItem: {
      apiUrl: '/page/sponsor/:id'
    },
    featureItem: {
      apiUrl: '/page/feature/:id'
    }
  },
  menus: {
    profilePopoverMenu,
    profileMenu,
    profileActionMenu,
    itemActionMenu
  },
  forms: {}
};

export default initialState;
