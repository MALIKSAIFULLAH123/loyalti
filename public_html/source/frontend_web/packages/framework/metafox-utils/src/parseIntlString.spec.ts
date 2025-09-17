import parseIntlString from './parseIntlString';

describe('praseIntlProps', () => {
  it('test with <a>', () => {
    const str =
      '<a>[url:https://metafox.com/questions/41129628/?c=333][target:_blank]Support data for this feature provided by metafox</a>';

    const props = parseIntlString(str);

    expect(props.props.url).toEqual(
      'https://metafox.com/questions/41129628/?c=333'
    );
    expect(props.props.target).toEqual('_blank');
    expect(props.child).toEqual(
      '<a>Support data for this feature provided by metafox</a>'
    );
  });
});
