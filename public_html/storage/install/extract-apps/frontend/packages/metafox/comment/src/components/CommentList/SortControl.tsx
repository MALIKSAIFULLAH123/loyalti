/**
 * @type: service
 * name: SortCommentList
 */
import { ClickOutsideListener, LineIcon, Popper } from '@metafox/ui';
import {
  Box,
  Button,
  MenuItem,
  Paper,
  Typography,
  styled
} from '@mui/material';
import React from 'react';
import { useGlobal, useLocation } from '@metafox/framework';
import qs from 'query-string';
import { alpha } from '@mui/system/colorManipulator';

const PaperMenu = styled(Paper, {
  name: 'MuiActionMenu-menu'
})<{}>(({ theme }) => ({
  padding: theme.spacing(1, 0)
}));

const MenuItemStyled = styled(MenuItem, {
  name: 'MenuItem',
  shouldForwardProp: props => props !== 'actived'
})<{ actived?: boolean }>(({ theme, actived }) => ({
  ...(actived && {
    backgroundColor: alpha(theme.palette.primary.main, 0.08)
  }),
  '&:hover': {
    backgroundColor: 'rgba(0, 0, 0, 0.04)'
  }
}));

export interface SortControlProps {
  label?: string;
  disabled?: boolean;
  disablePortal?: boolean;
  value?: any;
  item?: any;
  setValue?: (value: string) => void;
}

enum Sort {
  Newest = 'newest',
  Oldest = 'oldest',
  All = 'all',
  Relevant = 'relevant'
}

const optionsDefault = [
  {
    label: 'all_comments',
    value: Sort.All,
    desc: 'all_comments_description'
  },
  {
    label: 'oldest',
    value: Sort.Oldest,
    desc: 'oldest_description'
  },
  {
    label: 'newest',
    value: Sort.Newest,
    desc: 'newest_description'
  }
];

const optionsDetail = [
  {
    label: 'most_relevant',
    value: Sort.Relevant,
    desc: 'most_relevant_description'
  },
  {
    label: 'all_comments',
    value: Sort.All,
    desc: 'all_comments_description'
  },
  {
    label: 'oldest',
    value: Sort.Oldest,
    desc: 'oldest_description'
  },
  {
    label: 'newest',
    value: Sort.Newest,
    desc: 'newest_description'
  }
];

const SortCommentStage = styled('div', {
  name: 'CommentList',
  slot: 'SortStage',
  overridesResolver(props, styles) {
    return [styles.sortStage];
  }
})(({ theme }) => ({
  display: 'flex',
  justifyContent: 'flex-end'
}));

function SortControl({
  value,
  item,
  setValue,
  disabled,
  disablePortal = true
}: SortControlProps) {
  const { i18n } = useGlobal();
  const location = useLocation();
  const searchParams = location?.search
    ? qs.parse(location.search.replace(/^\?/, ''))
    : {};
  const { comment_id } = searchParams;
  const options = comment_id ? optionsDetail : optionsDefault;
  const anchorRef = React.useRef<HTMLButtonElement>();
  const [open, setOpen] = React.useState<boolean>(false);
  const [sortType, setSortType] = React.useState(
    options.find(x => x.value === (value as Sort)) || options[0]
  );
  const ref = React.useRef();

  React.useEffect(() => {
    const opt = options.find(x => x.value === (value as Sort));

    if (opt) {
      setSortType(opt);
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [value]);

  const handleClose = () => {
    setOpen(false);
  };

  const handleClick = () => {
    if (!disabled) setOpen(prev => !prev);
  };

  const handleClickMenu = (item: any) => {
    handleClose();

    setValue(item.value);
  };

  return (
    <Box ref={ref} sx={{ overflow: 'hidden' }}>
      <SortCommentStage>
        <Button
          size="smaller"
          variant="text"
          ref={anchorRef}
          onClick={handleClick}
          disabled={disabled}
          data-testid={'buttonSortComment'}
          disableRipple
          sx={{
            background: 'transparent !important',
            padding: 0,
            height: '20px'
          }}
        >
          <Typography
            fontWeight="fontWeightBold"
            variant="body2"
            color="text.secondary"
            sx={{
              display: 'flex',
              alignItems: 'center',
              '> span': { marginLeft: '4px' }
            }}
          >
            {i18n.formatMessage({ id: 'sort_comment' })}:{' '}
            {i18n.formatMessage({ id: sortType.label })}
            {!disabled && <LineIcon icon="ico-caret-down" />}
          </Typography>
        </Button>
      </SortCommentStage>
      <ClickOutsideListener onClickAway={handleClose} excludeRef={anchorRef}>
        <Popper
          disablePortal={disablePortal}
          data-testid="menusortType"
          id={open ? 'sortType-popover' : undefined}
          anchorEl={anchorRef.current}
          open={Boolean(open)}
          popperOptions={{
            strategy: 'fixed'
          }}
          placement="bottom-end"
          modifiers={[
            {
              name: 'preventOverflow',
              options: {
                enabled: true,
                escapeWithReference: true,
                boundariesElement: ref
              }
            }
          ]}
        >
          <PaperMenu>
            {options
              ? options.map((item, index) => (
                  <MenuItemStyled
                    value={item.value}
                    data-value={item.value}
                    key={index.toString()}
                    onClick={() => handleClickMenu(item)}
                    actived={value === item.value}
                  >
                    <Box sx={{ display: 'flex', flexDirection: 'column' }}>
                      <Typography
                        sx={{ marginBottom: '4px' }}
                        variant="body1"
                        fontWeight="fontWeightSemiBold"
                      >
                        {i18n.formatMessage({ id: item.label })}
                      </Typography>
                      <Typography variant="body2" color="text.secondary">
                        {i18n.formatMessage({ id: item.desc })}
                      </Typography>
                    </Box>
                  </MenuItemStyled>
                ))
              : null}
          </PaperMenu>
        </Popper>
      </ClickOutsideListener>
    </Box>
  );
}

export default SortControl;
