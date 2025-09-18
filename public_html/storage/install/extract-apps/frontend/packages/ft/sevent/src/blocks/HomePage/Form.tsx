import React, { useState } from 'react';
import TextField from '@mui/material/TextField';
import InputAdornment from '@mui/material/InputAdornment';
import IconButton from '@mui/material/IconButton';
import SearchIcon from '@mui/icons-material/Search';
import { useGlobal } from '@metafox/framework';

const SearchForm = () => {
  const [searchValue, setSearchValue] = useState('');
  const { useIsMobile, navigate } = useGlobal();
  const isMobile = useIsMobile();

  const handleChange = (event) => {
    setSearchValue(event.target.value);
  };

  const handleSubmit = (event) => {
    event.preventDefault(); 
    navigate(`/sevent/all?q=${searchValue}`);
  };
  
  return (
    <form onSubmit={handleSubmit} action='/sevent/all' method='get' style={{ width: isMobile ? '100%' : 'initial' }}>
      <TextField
        fullWidth
        label="Search"
        variant="outlined"
        name="q"
        value={searchValue}
        onChange={handleChange}
        InputProps={{
          endAdornment: (
            <InputAdornment position="end">
              <IconButton type="submit">
                <SearchIcon />
              </IconButton>
            </InputAdornment>
          )
        }}
      />
    </form>
  );
};

export default SearchForm;
