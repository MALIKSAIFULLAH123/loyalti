/* eslint-disable @typescript-eslint/no-unused-vars */
/**
 * @type: ui
 * name: core.ui.searchBoxBlock
 */
import {
  SearchBoxBlockProps,
  useGlobal,
  useResourceAction,
  Link,
  useResourceForm
} from '@metafox/framework';
import { Block, BlockContent } from '@metafox/layout';
// layouts
import { LineIcon, SearchBox } from '@metafox/ui';
import { styled, Tooltip } from '@mui/material';
import qs from 'query-string';
import React, { useEffect, useState } from 'react';
import { FormBuilder } from '@metafox/form';
import { isEmpty, isEqual } from 'lodash';
import { whenParamRules } from '@metafox/utils';

export interface Props extends SearchBoxBlockProps {}

const IconClearStyled = styled(LineIcon, { name: 'IconClearStyled' })(
  ({ theme }) => ({
    marginRight: theme.spacing(0.5),
    cursor: 'pointer',
    color: theme.palette.text.hint
  })
);

const IconPlusStyled = styled(LineIcon, { name: 'IconPlusStyled' })(
  ({ theme }) => ({
    color: theme.palette.text.hint,
    fontSize: theme.mixins.pxToRem(16),
    marginLeft: theme.spacing(2),
    '&:hover': {
      color: theme.palette.primary.main
    }
  })
);

const FormStyled = styled('form', { name: 'FormStyled' })(({ theme }) => ({
  display: 'flex',
  alignItems: 'center',
  zIndex: theme.zIndex.appBar
}));

const SearchWrapper = styled(SearchBox, {
  name: 'SearchWrapper'
})(({ theme }) => ({
  '& input::placeholder, .ico': {
    color: theme.palette.text.hint
  }
}));

export default function SearchBoxBlock(props) {
  const { eventCenter, usePageParams, navigate, i18n, compactData } =
    useGlobal();
  const pageParams = usePageParams();
  const { search, appName, resourceName } = pageParams;
  const { icon, titleIcon, action } = props;

  const searchItem = useResourceAction(appName, resourceName, 'searchItem');
  const formSchema = useResourceForm(appName, resourceName, 'search_simple');
  const searchSimpleTrigger = React.useRef(false);

  const [value, setValue] = useState('');
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const [currentValue, setCurrentValue] = useState({});

  useEffect(() => {
    eventCenter.dispatch('minimizeGlobalSearchForm', true);
  });

  useEffect(() => {
    setValue(search || '');
  }, [search]);

  if (!searchItem) {
    return null;
  }

  const {
    placeholder,
    pageParams: searchParams = {},
    pageRules: searchRules
  } = searchItem;

  const handleSubmitForm = ({ values }) => {
    if (isEqual(values, currentValue) || searchSimpleTrigger.current) {
      return;
    }

    const url = searchItem.pageUrl ?? '/appName/search';
    const apiRules = searchItem?.apiRules;
    setCurrentValue(values);

    if (!isEmpty(values)) {
      const params = whenParamRules(values, apiRules);
      // searchSimpleTrigger prevent navigate mutiple and make history route chaotic when backHistory
      searchSimpleTrigger.current = true;

      navigate({
        pathname: url,
        search: qs.stringify(params)
      });
    }
  };

  const handleSubmit = (evt: React.FormEvent<HTMLFormElement>) => {
    if (evt) {
      evt.preventDefault();
    }

    const url = searchItem.pageUrl ?? '/appName/search';

    const query = isEmpty(value) ? {} : { q: value };
    const extraParams = compactData(
      searchParams,
      pageParams,
      searchRules,
      true
    );
    const params = {
      ...extraParams,
      ...query
    };

    navigate({
      pathname: url,
      search: qs.stringify(params)
    });
  };

  const clearValue = () => {
    setValue('');
  };

  if (formSchema)
    return (
      <Block testid="blockSearch">
        <BlockContent>
          <FormBuilder
            noHeader
            noBreadcrumb
            formSchema={formSchema}
            onChange={handleSubmitForm}
          />
        </BlockContent>
      </Block>
    );

  return (
    <Block>
      <BlockContent>
        <FormStyled onSubmit={handleSubmit} data-testid="formSearch">
          <SearchWrapper
            name="search"
            value={value}
            onChange={e => setValue(e.target.value)}
            placeholder={placeholder}
            endAdornment={
              value ? (
                <IconClearStyled icon="ico-close" onClick={clearValue} />
              ) : null
            }
          />
          <button type="submit" className="srOnly" aria-hidden="true" />
          {icon && titleIcon && (
            <Tooltip title={i18n.formatMessage({ id: titleIcon })}>
              <Link to={action} sx={{ textDecoration: 'none!important' }}>
                <IconPlusStyled icon={icon} />
              </Link>
            </Tooltip>
          )}
        </FormStyled>
      </BlockContent>
    </Block>
  );
}
