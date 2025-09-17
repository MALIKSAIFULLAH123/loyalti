import { useGlobal, useResourceForm, useLocation } from '@metafox/framework';
import React from 'react';
import { FormControl, styled, Button, Box, Typography } from '@mui/material';
import { isEqual } from 'lodash';
import qs from 'query-string';
import { LineIcon } from '@metafox/ui';
import { FormBuilder } from '@metafox/form';
import PaginationSelectPage from './PaginationSelectPage';

export interface PaginationProps {
  'data-testid'?: string;
  currentPage: number;
  from: number;
  to: number;
  total: number;
  itemsPerPage: number;
  message?: string;
  hasSort?: boolean;
  moduleName?: string;
  resourceName?: string;
  actionName?: string;
  onPageChange?: () => void;
}

const name = 'PaginationListView';
const Wrapper = styled(Box, {
  name,
  slot: 'wrapper',
  shouldForwardProp: props =>
    props !== 'isMobile' && props !== 'isMobileWithSort',
  overridesResolver(props, styles) {
    return [styles.wrapper];
  }
})<{ isMobileWithSort?: boolean }>(({ theme, isMobileWithSort }) => ({
  display: 'flex',
  ...(isMobileWithSort
    ? {
        display: 'block !important'
      }
    : { justifyContent: 'space-between' }),

  flexFlow: 'wrap',
  alignItems: 'center',
  padding: theme.spacing(2),
  '&:first-of-type': {
    paddingBottom: theme.spacing(1)
  }
}));
const List = styled('ul', {
  name: 'PaginationList'
})(({ theme }) => ({
  margin: theme.spacing(0, 0, 0, 1.5),
  listStyle: 'none',
  padding: 0,
  display: 'flex',
  '& .MuiInputBase-root': {
    fontSize: '13px',
    paddingTop: (40 - 13) / 2,
    paddingBottom: (40 - 13) / 2,
    height: '32px',
    boxSizing: 'border-box',
    '& .MuiSelect-select': {
      padding: '5px 8px !important'
    }
  }
}));

const ButtonFirst = styled(Button, {
  name,
  slot: 'ButtonFirst'
})(({ theme }) => ({
  marginLeft: theme.spacing(1),
  minWidth: theme.spacing(4)
}));

const ButtonLast = styled(Button, {
  name,
  slot: 'ButtonLast'
})(({ theme }) => ({
  marginRight: theme.spacing(1),
  minWidth: theme.spacing(4)
}));

const WrapperPages = styled(Box, {
  slot: 'wrapperPages',
  name,
  shouldForwardProp: props => props !== 'isMobile' && props !== 'hasSort',
  overridesResolver(props, styles) {
    return [styles.wrapperPages];
  }
})<{ hasSort?: boolean }>(({ theme, hasSort }) => ({
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'space-between',
  ...(!hasSort && { flex: 1, minWidth: 0 })
}));

const ButtonNav = styled(Button, {
  name: 'ButtonNav'
})(({ theme }) => ({
  minWidth: theme.spacing(4)
}));

export default function Pagination({
  currentPage,
  from,
  to,
  total: totalTemp,
  itemsPerPage,
  message = 'showing_from_to_of_total_items',
  'data-testid': testid = 'pagination',
  hasSort,
  moduleName,
  resourceName,
  actionName,
  onPageChange
}: PaginationProps) {
  const { i18n, navigate, useIsMobile } = useGlobal();
  const totalPage = Math.ceil(totalTemp / itemsPerPage);
  const location = useLocation();
  const isMobile = useIsMobile();
  const searchParams = location?.search
    ? qs.parse(location.search.replace(/^\?/, ''))
    : {};

  React.useEffect(() => {
    if (onPageChange) {
      onPageChange();
    }
  }, [currentPage, onPageChange]);

  const handlePageChange = value => {
    if (!value) return;

    navigate(
      {
        search: qs.stringify({
          ...searchParams,
          page: value
        })
      },
      { replace: true }
    );
  };

  const handlePrevious = () => {
    navigate(
      {
        search: qs.stringify({
          ...searchParams,
          page: currentPage - 1
        })
      },
      { replace: true }
    );
  };

  const handleNext = () => {
    navigate(
      {
        search: qs.stringify({
          ...searchParams,
          page: currentPage + 1
        })
      },
      { replace: true }
    );
  };

  const formSchema = useResourceForm(moduleName, resourceName, actionName);

  React.useEffect(() => {
    // Only run first init
    if (formSchema?.value) {
      // searchParams higher priority on this case
      navigate(
        {
          search: qs.stringify({
            ...formSchema?.value,
            ...searchParams
          })
        },
        { replace: true }
      );
    }
  }, []);

  const onChangeSort = ({ values, schema, form }) => {
    if (isEqual(values, form?.initialValues)) {
      return;
    }

    navigate(
      {
        search: qs.stringify({
          ...searchParams,
          ...values,
          page: 1
        })
      },
      { replace: true }
    );
  };

  const onSubmit = (values, actions) => {
    actions.setSubmitting(false);
  };

  if (!hasSort && totalPage < 2) return null;

  const total = i18n.formatNumber(totalTemp);

  return (
    <Wrapper isMobileWithSort={hasSort && isMobile}>
      {hasSort && (
        <FormBuilder
          noHeader
          noBreadcrumb
          formSchema={formSchema}
          onSubmit={onSubmit}
          onChange={onChangeSort}
        />
      )}
      {totalPage > 1 && (
        <WrapperPages my={1} hasSort={hasSort}>
          <Typography component="div" color="textHint" variant="body2">
            {i18n.formatMessage(
              {
                id: message
              },
              { from, to, total }
            )}
          </Typography>
          <List>
            {currentPage !== 1 && (
              <ButtonNav
                disabled={currentPage === 1}
                size="small"
                variant="outlined"
                onClick={handlePrevious}
              >
                <LineIcon icon="ico-angle-left" />
              </ButtonNav>
            )}
            <ButtonFirst
              size="small"
              variant={currentPage === 1 ? 'contained' : 'outlined'}
              onClick={() => handlePageChange(1)}
            >
              1
            </ButtonFirst>
            <FormControl sx={{ minWidth: 50, mx: 1 }} size="small">
              <PaginationSelectPage
                onPageChange={handlePageChange}
                currentPage={currentPage}
                totalPage={totalPage}
              />
            </FormControl>
            <ButtonLast
              size="small"
              variant={currentPage === totalPage ? 'contained' : 'outlined'}
              onClick={() => handlePageChange(totalPage)}
            >
              {totalPage}
            </ButtonLast>
            {currentPage !== totalPage && (
              <ButtonNav
                disabled={currentPage === totalPage}
                size="small"
                variant="outlined"
                onClick={handleNext}
              >
                <LineIcon icon="ico-angle-right" />
              </ButtonNav>
            )}
          </List>
        </WrapperPages>
      )}
    </Wrapper>
  );
}
