const composerConfig = {
  editorPlugins: [
    { as: 'statusComposer.plugin.mention', testid: 'mention' },
    { as: 'statusComposer.plugin.hashtag', testid: 'hashtag' },
    {
      as: 'statusComposer.plugin.linkify',
      testid: 'linkify'
    }
  ],
  editorControls: [
    {
      as: 'statusComposer.control.AttachBackgroundStatusButton',
      enabledWhen: [
        'and',
        [
          'or',
          ['and', ['falsy', 'attachmentType']],
          ['eq', 'attachmentType', 'backgroundStatus'],
          [
            'and',
            ['eq', 'attachmentType', 'link'],
            ['truthy', 'attachments.link.value.is_preview_hidden'],
            ['truthy', 'attachments.link.value.link']
          ]
        ],
        ['lte', 'lengthText', 150],
        ['lte', 'textLines', 3]
      ],
      testid: 'attachBackgroundStatusButton',
      showWhen: ['gte', 'setting.backgroundstatus.total_active', 1]
    },
    {
      as: 'lexical.control.attachEmoji',
      showWhen: ['eq', 'strategy', 'dialog'],
      testid: 'attachEmojiButton'
    }
  ],
  attachers: [
    {
      as: 'statusComposer.control.StatusTagsFriendButton',
      showWhen: [
        'and',
        ['eq', 'strategy', 'dialog'],
        ['truthy', 'setting.activity.feed.enable_tag_friends']
      ],
      testid: 'StatusTagsFriendButton'
    },
    {
      as: 'statusComposer.control.StatusUploadPhotoButton',
      showWhen: [
        'and',
        ['truthy', 'setting.feed.types.photo_set.can_create_feed'],
        [
          'or',
          ['truthy', 'acl.photo.photo.create'],
          ['truthy', 'acl.video.video.create']
        ]
      ],
      enabledWhen: [
        'or',
        [
          'and',
          ['falsy', 'isEdit'],
          [
            'and',
            ['eq', 'attachmentType', 'link'],
            ['truthy', 'attachments.link.value.link'],
            ['falsy', 'attachments.statusBackground.value.id']
          ]
        ],
        ['and', ['falsy', 'attachmentType'], ['falsy', 'isEdit']],
        [
          'or',
          ['eq', 'attachmentType', 'photo'],
          ['eq', 'attachmentType', 'photo_set']
        ]
      ],
      testid: 'StatusUploadPhotoButton'
    },
    {
      as: 'statusComposer.control.StatusUploadVideo',
      showWhen: [
        'and',
        ['truthy', 'setting.video'],
        ['truthy', 'acl.video.video.create'],
        ['truthy', 'acl.video.video.share_video_url'],
        ['truthy', 'setting.feed.types.video.can_create_feed'],
        ['neq', 'strategy', 'dialog'],
        ['includes', 'parentType', ['feed']]
      ],
      enabledWhen: [
        'or',
        ['falsy', 'attachmentType'],
        ['eq', 'data.attachmentType', 'video']
      ],
      testid: 'StatusUploadVideo'
    },
    {
      as: 'statusComposer.control.LiveStreamingButton',
      showWhen: [
        'and',
        ['eq', 'strategy', 'block'],
        [
          'or',
          ['includes', 'parentType', ['feed']],
          [
            'and',
            ['includes', 'parentType', ['user']],
            ['truthy', 'item.is_owner']
          ],
          ['truthy', 'item.profile_settings.live_video_share_live_videos']
        ],
        ['truthy', 'acl.livestreaming.live_video.create'],
        ['truthy', 'setting.livestreaming.streaming_service_enable']
      ],
      testid: 'LiveStreamingButton'
    },
    {
      as: 'statusComposer.control.CheckInButton',
      showWhen: [
        'and',
        ['eq', 'strategy', 'dialog'],
        ['truthy', 'setting.activity.feed.enable_check_in']
      ],
      testid: 'checkinButton'
    },
    {
      as: 'statusComposer.StatusAddPollButton',
      enabledWhen: [
        'or',
        [
          'and',
          ['falsy', 'isEdit'],
          [
            'and',
            ['eq', 'attachmentType', 'link'],
            ['truthy', 'attachments.link.value.is_preview_hidden'],
            ['truthy', 'attachments.link.value.link'],
            ['falsy', 'attachments.statusBackground.value.id']
          ]
        ],
        ['and', ['falsy', 'attachmentType'], ['falsy', 'isEdit']]
      ],
      showWhen: [
        'or',
        [
          'and',
          ['truthy', 'acl.poll.poll.create'],
          ['truthy', 'setting.feed.types.poll.can_create_feed'],
          ['eq', 'parentType', 'feed']
        ],
        [
          'and',
          ['truthy', 'item.profile_settings.poll_share_polls'],
          ['truthy', 'acl.poll.poll.create'],
          ['eq', 'parentType', 'group'],
          ['truthy', 'acl.poll.poll.create']
        ],
        [
          'and',
          ['truthy', 'item.profile_settings.poll_share_polls'],
          ['truthy', 'acl.poll.poll.create'],
          ['eq', 'parentType', 'page'],
          ['truthy', 'acl.poll.poll.create']
        ],
        [
          'and',
          ['truthy', 'acl.poll.poll.create'],
          ['truthy', 'setting.feed.types.poll.can_create_feed'],
          ['eq', 'parentType', 'user'],
          ['falsy', 'isUserProfileOther']
        ]
      ],
      testid: 'StatusAddPollButton'
    },
    {
      as: 'statusComposer.control.FeedScheduleButton',
      showWhen: [
        'and',
        ['eq', 'strategy', 'dialog'],
        ['neq', 'attachmentType', 'share'],
        ['falsy', 'isEdit'],
        ['truthy', 'acl.activity.feed.schedule_post'],
        [
          'or',
          ['neqeqeq', 'parentType', 'user'],
          ['and', ['eqeqeq', 'parentType', 'user'], ['truthy', 'item.is_owner']]
        ]
      ],
      testid: 'FeedScheduleButton'
    }
  ]
};
export default composerConfig;
