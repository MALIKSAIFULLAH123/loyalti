import { Autocomplete, Box, FormControl, TextField } from '@mui/material';
import { createFilterOptions } from '@mui/material/Autocomplete';
import { get } from 'lodash';
import React, { KeyboardEventHandler } from 'react';

interface ItemShape {
  [key: string]: any;
}

function SelectCustom({ onSelect, data, valueSelect, isOrtherTab }: any) {
  const { options = [] } = data;

  const isOptionEqualToValue = (option: ItemShape, value: ItemShape): boolean =>
    // eslint-disable-next-line eqeqeq
    get(option, 'id') == get(value, 'id');

  const getOptionLabel = (option: ItemShape): string => {
    return get(option, 'label') ?? '';
  };

  const filterOptions = createFilterOptions<any>({
    stringify: ({ label }) => `${label}`
  });

  // prevent enter
  const handleKeyDown: KeyboardEventHandler<HTMLDivElement> = evt => {
    if (evt.keyCode === 13) {
      evt.preventDefault();
    }
  };

  const renderInput = params => {
    return (
      <TextField
        {...params}
        fullWidth
        onKeyDown={handleKeyDown}
        inputProps={{
          ...params.inputProps,
          autoComplete: 'on'
        }}
      />
    );
  };

  const renderOption = React.useCallback(
    (props: Record<string, any>, option: ItemShape) => {
      return (
        <Box
          data-value={option?.value}
          component="li"
          {...props}
          key={`${option?.id}${option?.question}`}
        >
          {isOrtherTab ? option.label : `${option?.ordering}. ${option.label}`}
        </Box>
      );
    },
    [isOrtherTab]
  );

  return (
    <FormControl margin={'normal'} fullWidth data-testid="field select custom">
      <Autocomplete
        disableClearable
        filterOptions={filterOptions}
        openOnFocus
        value={valueSelect || options[0]}
        defaultValue={options[0]}
        onChange={onSelect}
        isOptionEqualToValue={isOptionEqualToValue}
        options={options}
        getOptionLabel={getOptionLabel}
        autoHighlight
        renderInput={renderInput}
        renderOption={renderOption}
      />
    </FormControl>
  );
}

export default SelectCustom;
