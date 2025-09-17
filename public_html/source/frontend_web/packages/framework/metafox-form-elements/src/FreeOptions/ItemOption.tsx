import {
  Box,
  styled,
  TextField,
  Tooltip,
  IconButton,
  Link
} from '@mui/material';
import React from 'react';
import { LineIcon } from '@metafox/ui';
import ErrorMessage from '../ErrorMessage';
import { useGlobal } from '@metafox/framework';
import ItemOrder from './ItemOrder';
import { get } from 'lodash';

enum TypeStatus {
  Create = 'new',
  Remove = 'remove',
  Edit = 'update'
}

const name = 'FreeOptionsField';

const StyledIconClose = styled(IconButton, {
  name,
  slot: 'IconClose',
  shouldForwardProp: prop => prop !== 'enable'
})(({ theme }) => ({
  display: 'flex',
  justifyContent: 'center',
  alignItems: 'center',
  width: theme.spacing(5),
  height: theme.spacing(5),
  margin: theme.spacing(0, 0.5),
  '& .ico': {
    fontSize: theme.mixins.pxToRem(16),
    color: theme.palette.text.hint
  }
}));

const MutipleLanguage = styled(Box, {
  name,
  slot: 'MutipleLanguage',
  shouldForwardProp: prop => prop !== 'minimal'
})<{ minimal: boolean }>(({ theme, minimal }) => ({
  display: 'block',
  padding: theme.spacing(2, 1, 1, 1),
  border: theme.mixins.border('secondary'),
  borderRadius: theme.shape.borderRadius,
  ...(minimal
    ? {
        '& > .item-optional': {
          display: 'none !important'
        }
      }
    : {})
}));

const RowItem = styled(Box, {
  name,
  slot: 'RowItem'
})(({ theme }) => ({
  width: '100%'
}));

const OptionRow = props => {
  const {
    option,
    error,
    disabled,
    onChange,
    onRemove,
    canRemove: canRemoveProp,
    sortable,
    index,
    onMoveRow,
    handleDrop,
    setTouched,
    translatableOptions,
    collapseOptions = true
  } = props;
  const { i18n } = useGlobal();
  const [minimal, setMinimal] = React.useState(!!collapseOptions);

  if (!option || option?.status === TypeStatus.Remove) return null;

  const { removable = true, value } = option;

  return (
    <ItemOrder
      sortable={sortable}
      index={index}
      onMoveRow={onMoveRow}
      handleDrop={handleDrop}
      disabled={disabled}
    >
      <RowItem>
        <Box sx={{ display: 'flex', width: '100%', alignItems: 'center' }}>
          {sortable ? (
            <Box
              sx={{
                display: 'inline-flex',
                alignItems: 'center',
                justifyContent: 'center',
                width: '32px'
              }}
            >
              <LineIcon icon={'ico-arrows-move'} sx={{ mr: 1 }} />
            </Box>
          ) : null}
          {translatableOptions ? (
            <MutipleLanguage
              sx={{
                flex: 1,
                minWidth: 0,
                '& > .item': {
                  '& + .item': {
                    mt: 2
                  }
                }
              }}
              minimal={minimal}
            >
              {translatableOptions.map(x => (
                <Box
                  key={x.value}
                  className={x.required ? 'item' : 'item item-optional'}
                >
                  <TextField
                    size="small"
                    fullWidth
                    id="outlined-basic"
                    value={get(value, x.value)}
                    variant="outlined"
                    onChange={e => onChange({ [x.value]: e.target.value })}
                    error={get(error, `value.${x.value}`)}
                    disabled={disabled}
                    onBlur={() => {
                      setTouched(true);
                    }}
                    required={x.required}
                    label={x.label}
                  />
                  {get(error, `value.${x.value}`) ? (
                    <ErrorMessage error={get(error, `value.${x.value}`)} />
                  ) : null}
                </Box>
              ))}
              {collapseOptions ? (
                <Box mt={1}>
                  <Link
                    color="primary"
                    component="span"
                    onClick={() => {
                      setMinimal(prev => !prev);
                    }}
                    style={{ cursor: 'pointer' }}
                    variant="body2"
                  >
                    {i18n.formatMessage({
                      id: minimal
                        ? collapseOptions?.more_label || 'view_more'
                        : collapseOptions?.less_label || 'view_less'
                    })}
                  </Link>
                </Box>
              ) : null}
            </MutipleLanguage>
          ) : (
            <Box sx={{ flex: 1, minWidth: 0, width: '100%' }}>
              <TextField
                size="small"
                fullWidth
                id="outlined-basic"
                value={value}
                variant="outlined"
                onChange={e => onChange(e.target.value)}
                error={error}
                disabled={disabled}
                onBlur={() => {
                  setTouched(true);
                }}
              />
              {get(error, 'value') ? (
                <ErrorMessage error={get(error, 'value')} />
              ) : null}
            </Box>
          )}
          <Tooltip title={i18n.formatMessage({ id: 'remove' })} placement="top">
            <StyledIconClose
              onClick={onRemove}
              disabled={!canRemoveProp || !removable}
            >
              <LineIcon icon="ico-close" />
            </StyledIconClose>
          </Tooltip>
        </Box>
      </RowItem>
    </ItemOrder>
  );
};

export default OptionRow;
