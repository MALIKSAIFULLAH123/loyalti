import { filterShowWhen } from './index';

describe('filterShowWhen', () => {
  const items = [
    {
      name: 'friend',
      parent_name: '',
      label: 'Friends',
      to: '/friend',
      tab: 'friend',
      testid: 'friend',
      ordering: 3,
      version: 0
    },
    {
      name: 'photo',
      parent_name: '',
      label: 'Photos',
      to: '/photo',
      tab: 'photo',
      testid: 'photo',
      ordering: 4,
      showWhen: ['and', ['truthy', 'item.profile_menu_settings.photo']],
      version: 0
    }
  ];

  it('empty', () => {
    const result = filterShowWhen(items, {});
    expect(result.length).toEqual(1);
    expect(result[0].name).toEqual('friend');
  });

  it('+value', () => {
    const result = filterShowWhen(items, {
      item: { profile_menu_settings: { photo: true } }
    });
    expect(result.length).toEqual(2);
    expect(result[0].name).toEqual('friend');
    expect(result[1].name).toEqual('photo');
  });

  it('+value-2', () => {
    const result = filterShowWhen(items, {
      item: { profile_menu_settings: { photo: false } }
    });
    expect(result.length).toEqual(1);
    expect(result[0].name).toEqual('friend');
  });
});
