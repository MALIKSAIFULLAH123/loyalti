import { whenParamRules } from './index';

describe('whenParamRules', () => {
  const apiRules = {
    q: ['truthy', 'q'],
    sort: ['includes', 'sort', ['full_name', 'last_login', 'last_activity']],
    gender: ['includes', 'gender', ['1', '2']],
    view: ['includes', 'view', ['recommend', 'featured', 'recent']],
    country: ['truthy', 'country'],
    city: ['truthy', 'city']
  };

  it('allows', () => {
    const result = whenParamRules(
      {
        q: 'metafox?',
        sort: 'full_name',
        gender: 1,
        view: 'recommend',
        country: 'AF',
        city: 'Texas'
      },
      apiRules
    );
    expect(result.q).toEqual('metafox?');
    expect(result.sort).toEqual('full_name');
    expect(result.view).toEqual('recommend');
    expect(result.country).toEqual('AF');
    expect(result.city).toEqual('Texas');
  });

  it('deny:2', () => {
    const result = whenParamRules(
      {
        q: 'metafox?',
        sort: 'full_name',
        gender: 1,
        view: 'recommend',
        country: 'AF',
        city: 'Texas'
      },
      {}
    );
    expect(result.q).toBeUndefined();
    expect(result.sort).toBeUndefined();
    expect(result.view).toBeUndefined();
    expect(result.country).toBeUndefined();
    expect(result.city).toBeUndefined();
    expect(result.other).toBeUndefined();
  });

  it('deny', () => {
    const result = whenParamRules(
      {
        q: '',
        sort: '',
        gender: 0,
        view: 'recommended',
        country: false,
        city: '',
        other: 'bla bla'
      },
      apiRules
    );
    expect(result.q).toBeUndefined();
    expect(result.sort).toBeUndefined();
    expect(result.view).toBeUndefined();
    expect(result.country).toBeUndefined();
    expect(result.city).toBeUndefined();
    expect(result.other).toBeUndefined();
  });
});
