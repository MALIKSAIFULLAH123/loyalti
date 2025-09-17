import { withDisabledWhen } from './index';

describe('withDisabledWhen', () => {
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
      enabledWhen: ['and', ['truthy', 'item.profile_menu_settings.photo']],
      version: 0
    }
  ];

  it('empty', () => {
    const result = withDisabledWhen(items, {});
    expect(result[0].disabled).toBeFalsy();
    expect(result[1].disabled).toBeTruthy();
  });
  it('+value', () => {
    const result = withDisabledWhen(items, {
      item: { profile_menu_settings: { photo: true } }
    });
    expect(result[0].disabled).toBeFalsy();
    expect(result[1].disabled).toBeFalsy();
  });

  it('+value-2', () => {
    const result = withDisabledWhen(items, {
      item: { profile_menu_settings: { photo: false } }
    });
    expect(result[0].disabled).toBeFalsy();
    expect(result[1].disabled).toBeTruthy();
  });
});
