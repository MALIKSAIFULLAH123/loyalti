import { useContext } from 'react';
import BlockContext from './LayoutBlock/BlockContext';

const useBlock = () => useContext(BlockContext);

export default useBlock;
