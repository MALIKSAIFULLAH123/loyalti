/**
 * Local store testing
 */

import { Manager } from '@metafox/framework';
import LocalStore from './LocalStore';

describe('LocalStore', () => {
  it('+constructor', () => {
    const localStore = new LocalStore({});
    expect(localStore).toBeDefined();
  });
});

describe('+Manager', () => {
  const manager = Manager.factory({}).use({ localStore: LocalStore });
  const localStore: LocalStore = manager.localStore;

  afterAll(() => {
    localStorage.clear();
  });

  beforeAll(() => {
    localStorage.clear();
  });

  it('+bootstrap', () => {
    expect(localStore).toBeDefined();
  });

  it('+get', () => {
    expect(localStore.get('name')).toEqual('');
    expect(localStore.set('name', 'value')).toBeUndefined();
    expect(localStore.get('name')).toEqual('value');

    expect(localStore.remove('name')).toBeUndefined();
    expect(localStore.get('name')).toEqual('');
  });

  it('+getInt', () => {
    expect(localStore.getInt('int')).toEqual(0);
    expect(localStore.set('int', 10)).toBeUndefined();
    expect(localStore.getInt('int')).toEqual(10);
    expect(localStore.clear()).toBeUndefined();
    expect(localStore.getInt('name')).toEqual(0);
  });

  it('+json', () => {
    expect(localStore.getJSON('json')).toBeUndefined();

    expect(
      localStore.set('json', JSON.stringify({ user: { id: 2 } }))
    ).toBeUndefined();

    expect(
      localStore.getJSON<{ user: { id: number } }>('json').user?.id
    ).toEqual(2);
  });
});
