import { useGlobal } from '@metafox/framework';
import {
  Box,
  Button,
  ClickAwayListener,
  List,
  Paper,
  Popper,
  TextField,
  Typography,
  styled
} from '@mui/material';
import { isNaN, isNumber } from 'lodash';
import React from 'react';

const name = 'select-page';

const PaperStyled = styled(Paper, { name, slot: 'paper' })(({ theme }) => ({
  padding: theme.spacing(1.5)
}));

const ListStyled = styled(List, { name, slot: 'List' })(({ theme }) => ({
  display: 'flex',
  paddingBottom: 0,
  paddingTop: theme.spacing(1.5)
}));

const WapperList = styled(Box, { name, slot: 'WapperList' })(({ theme }) => ({
  '& > *': {
    height: '35px',
    marginRight: `${theme.spacing(0.5)} !important`
  },
  '& .MuiInputBase-root': {
    height: '35px',
    width: '100px',
    boxSizing: 'border-box',
    '& input': {
      textAlign: 'end'
    }
  },
  marginRight: theme.spacing(0.5)
}));

const ButtonChangeStyled = styled(Button, { name, slot: 'ButtonChange' })(
  ({ theme }) => ({
    height: 35,
    width: 35,
    minWidth: 'auto'
  })
);

const ButtonNextStyled = styled(Button, { name, slot: 'ButtonNext' })(
  ({ theme }) => ({
    height: 35
  })
);

export interface DropdownMenuProps {
  onPageChange: (value: number) => void;
  currentPage: number;
  totalPage: number;
}

export default function PaginationSelectPage({
  onPageChange,
  currentPage = 1,
  totalPage
}: DropdownMenuProps) {
  const { i18n } = useGlobal();
  const anchorRef = React.useRef<HTMLButtonElement>();
  const [open, setOpen] = React.useState<boolean>(false);
  const [pageNumber, setPageNumber] = React.useState<number | string>(
    currentPage
  );

  const variant = React.useMemo(() => {
    if (currentPage > 1 && currentPage < totalPage) {
      return 'contained';
    }

    return 'outlined';
  }, [currentPage, totalPage]);

  const handleOnClick = (evt: React.MouseEvent<HTMLButtonElement>) => {
    if (evt) evt.stopPropagation();

    setOpen(!open);
  };

  const handleOutsideClick = React.useCallback(() => {
    if (open) setOpen(false);
  }, [open]);

  const handlePrevious = React.useCallback(() => {
    if (!pageNumber || pageNumber === 1) return;

    setPageNumber(prev => (parseInt(prev) || 0) - 1);
  }, [pageNumber]);

  const handleNext = React.useCallback(() => {
    if (!pageNumber || pageNumber === totalPage) return;

    setPageNumber(prev => (parseInt(prev) || 0) + 1);
  }, [totalPage, pageNumber]);

  const verifiyValue = React.useCallback(
    value => {
      const data = parseInt(value);

      if (data > totalPage) return '';

      return !isNaN(data) && isNumber(data) ? data : '';
    },
    [totalPage]
  );

  const handleChangePage = React.useCallback(
    e => {
      const data = verifiyValue(e.target.value);

      setPageNumber(data);
    },
    [verifiyValue]
  );

  const handlePageChange = React.useCallback(
    (pageNumber): any => {
      if (!pageNumber || !isNumber(pageNumber)) return;

      onPageChange(pageNumber);
      handleOutsideClick();
    },
    // eslint-disable-next-line react-hooks/exhaustive-deps
    [handleOutsideClick]
  );

  const handleKeyDown = (evt: any) => {
    if (evt.keyCode === 13) {
      // enter
      const data = verifiyValue(evt.target.value);

      if (!data) return;

      handlePageChange(data);
    }
  };

  React.useEffect(() => {
    setPageNumber(currentPage);
  }, [currentPage]);

  return (
    <>
      <Button
        size="small"
        variant={variant}
        onClick={handleOnClick}
        ref={anchorRef}
      >
        {i18n.formatMessage(
          { id: 'pagination_current_of_total_page' },
          {
            current: currentPage,
            total: totalPage
          }
        )}
      </Button>
      {open ? (
        <ClickAwayListener
          onClickAway={handleOutsideClick}
          excludeRef={anchorRef}
        >
          <Popper
            open={Boolean(open)}
            anchorEl={anchorRef.current}
            placement={'bottom-end'}
          >
            <PaperStyled>
              <Typography variant="h5">
                {i18n.formatMessage({ id: 'pagination_go_to_page' })}
              </Typography>
              <ListStyled>
                <WapperList>
                  <ButtonChangeStyled
                    disabled={!pageNumber || pageNumber === 1}
                    size="small"
                    variant="outlined"
                    onClick={handlePrevious}
                  >
                    -
                  </ButtonChangeStyled>
                  <TextField
                    size="small"
                    inputProps={{ type: 'numeric' }}
                    value={pageNumber}
                    onChange={handleChangePage}
                    onKeyDown={handleKeyDown}
                  />
                  <ButtonChangeStyled
                    disabled={!pageNumber || pageNumber === totalPage}
                    size="small"
                    variant="outlined"
                    onClick={handleNext}
                  >
                    +
                  </ButtonChangeStyled>
                </WapperList>
                <ButtonNextStyled
                  disabled={!pageNumber || !isNumber(pageNumber)}
                  size="small"
                  variant="contained"
                  color="primary"
                  onClick={() => handlePageChange(pageNumber)}
                >
                  {i18n.formatMessage({ id: 'pagination_go' })}
                </ButtonNextStyled>
              </ListStyled>
            </PaperStyled>
          </Popper>
        </ClickAwayListener>
      ) : null}
    </>
  );
}
