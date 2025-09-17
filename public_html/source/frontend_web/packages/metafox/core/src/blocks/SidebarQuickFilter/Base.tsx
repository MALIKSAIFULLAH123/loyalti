import {
  BlockViewProps,
  useGlobal,
  useResourceAction,
  useResourceForm
} from '@metafox/framework';
import { FormBuilder } from '@metafox/form';
import { Block, BlockContent } from '@metafox/layout';
import {
  whenParamRules,
  decodeSearchParams,
  encodeSearchParams
} from '@metafox/utils';
import { isNil, omitBy, debounce, isEqualWith, isNumber } from 'lodash';
import React, { useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { Button, Box, styled } from '@mui/material';

export interface Props extends BlockViewProps {
  resourceNameAction?: string;
  formName?: string;
  isSidebarAppMobile?: boolean;
  visibleAppBar?: boolean;
}
const SearchMobile = styled(Box, {
  name: 'FilterSearchBlockInApp',
  slot: 'SearchMobile'
})(({ theme, visibleAppBar }) => ({
  padding: theme.spacing(1),
  position: 'fixed',
  bottom: 0,
  '.appbar-bottom &': {
    bottom: visibleAppBar ? theme.appBarMobileConfig?.bottom ?? '48px' : 0
  },
  left: 0,
  right: 0,
  display: 'flex',
  justifyContent: 'flex-end',
  background: 'rgba(0,0,0,0.2)',
  zIndex: '1500'
}));
const serializeParams = x => ({ view: 'search', ...x });

const compareParams = (values, currentValue) =>
  isEqualWith(
    omitBy(serializeParams(values), v => isNil(v)),
    omitBy(serializeParams(currentValue), v => isNil(v)),
    (a, b) => {
      // url query string will not distinguish between numbers and string
      if (isNumber(a) || isNumber(b)) {
        // eslint-disable-next-line eqeqeq
        return a == b;
      }
    }
  );

export default function SidebarQuickFilter({
  formName = 'search',
  resourceNameAction,
  isSidebarAppMobile,
  visibleAppBar
}: Props) {
  const { usePageParams, useContentParams, i18n } = useGlobal();
  const pageParams = usePageParams();
  const contentParams = useContentParams();
  const { appName, resourceName } = pageParams;
  const [searchParams, setSearchParams] = useSearchParams();
  const objSearchParams = decodeSearchParams(searchParams);
  const searchValuesRef = React.useRef({});
  const config = useResourceAction(
    appName,
    resourceName,
    resourceNameAction || 'viewAll'
  );

  const formSchema = useResourceForm(appName, resourceName, formName);
  const [currentValue, setCurrentValue] = useState(objSearchParams);

  const onSubmit = (values, actions) => {
    actions.setSubmitting(false);
  };

  const handleSearch = React.useCallback(
    values => {
      if (compareParams(values, currentValue)) {
        return;
      }

      setCurrentValue(values);

      const apiRules =
        contentParams?.mainListing?.dataSource?.apiRules || config?.apiRules;

      const params = whenParamRules(values, apiRules);
      setSearchParams(encodeSearchParams(params), { replace: true });
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [currentValue, setCurrentValue]
  );
  // eslint-disable-next-line react-hooks/exhaustive-deps
  const debounceSearch = React.useCallback(debounce(handleSearch, 200), [
    handleSearch
  ]);

  const onChange = ({ values }: any) => {
    if (isSidebarAppMobile) {
      searchValuesRef.current = values;

      return;
    }

    debounceSearch(values);
  };

  const triggerSearch = () => {
    debounceSearch(searchValuesRef.current);
  };

  React.useEffect(() => {
    return () => {
      debounceSearch.cancel();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return (
    <Block testid="blockSearch">
      <BlockContent>
        <FormBuilder
          noHeader
          noBreadcrumb
          formSchema={formSchema}
          onSubmit={onSubmit}
          onChange={onChange}
          formContext={{ isSidebarAppMobile }}
        />
        {isSidebarAppMobile ? (
          <Box height={48}>
            <SearchMobile visibleAppBar={visibleAppBar}>
              <Button
                variant="contained"
                disableRipple
                size="small"
                color="primary"
                onClick={triggerSearch}
              >
                {i18n.formatMessage({ id: 'search' })}
              </Button>
            </SearchMobile>
          </Box>
        ) : null}
      </BlockContent>
    </Block>
  );
}
