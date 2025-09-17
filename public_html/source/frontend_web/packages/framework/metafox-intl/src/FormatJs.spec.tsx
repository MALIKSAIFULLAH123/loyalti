import { createIntl, createIntlCache } from '@formatjs/intl';
import messages from './__mocks__/messages.json';

describe('test intl formatter', () => {
  const cache = createIntlCache();

  const intl = createIntl(
    {
      locale: 'en-US',
      messages
    },
    cache
  );

  it('select_simple', () => {
    expect(
      intl.formatMessage({ id: 'select_simple' }, { gender: 'male' })
    ).toEqual('He will respond shortly.');
  });

  // Call imperatively
  it('user_posted_a_post_on_timeline', () => {
    expect(
      intl.formatMessage(
        { id: 'user_posted_a_post_on_timeline' },
        { appName: 'feed' }
      )
    ).toEqual('');

    expect(
      intl.formatMessage(
        { id: 'user_posted_a_post_on_timeline' },
        {
          appName: 'user',
          isCreator: '0',
          isAuthUser: '0',
          profile: () => 'profile'
        }
      )
    ).toEqual("write on profile's timeline");
  });
});
