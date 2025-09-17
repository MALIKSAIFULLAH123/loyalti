import { ScrollContainer } from '@metafox/layout';
import { OnEmojiClick } from '@metafox/emoji';
import { Box } from '@mui/material';
import { styled } from '@mui/material/styles';
import { isNil } from 'lodash';
import React from 'react';
import emojiData from './EmojiData.json';
import EmojiList from './EmojiList';

const Tabs = styled('div', {
  name: 'EmojiPicker',
  slot: 'Tabs'
})(({ theme }) => ({
  display: 'flex',
  flexDirection: 'row',
  borderTop: theme.mixins.border('secondary'),
  overflowX: 'auto',
  overflowY: 'hidden'
}));

const Tab = styled('div', {
  name: 'EmojiPicker',
  slot: 'Tab',
  shouldForwardProp: prop => prop !== 'active'
})<{
  active: boolean;
}>(({ theme, active }) =>
  Object.assign(
    {
      height: 32,
      fontSize: 16,
      padding: theme.spacing(0, 0.8, 0, 0.8),
      alignItems: 'center',
      display: 'flex',
      borderTop: '2px solid',
      borderTopColor: 'transparent'
    },
    active && {
      borderTopColor: theme.palette.primary.main
    }
  )
);
export interface Props {
  onEmojiClick?: OnEmojiClick;
}

export default function EmojiPicker({ onEmojiClick }: Props) {
  const scrollRef = React.useRef<HTMLDivElement>();
  const [activeTab, setActiveTab] = React.useState<number>(0);
  const refs = React.useRef<Record<string, any>>({});

  const setRefs = (id: number, node: unknown) => {
    refs.current[id] = node;
  };

  const changeTab = React.useCallback((id: number) => {
    const top = refs.current[id]?.offsetTop;

    if (!isNil(top)) {
      scrollRef.current.scrollTo({ top, behavior: 'auto' });
    }

    setActiveTab(id);
  }, []);

  const onScroll = () => {
    if (
      scrollRef.current.scrollTop + 150 >
      refs.current[activeTab + 1]?.offsetTop
    ) {
      setActiveTab(prev => prev + 1);

      return;
    }

    if (
      scrollRef.current.scrollTop + 150 <
      refs.current[activeTab]?.offsetTop
    ) {
      setActiveTab(prev => prev - 1);

      return;
    }
  };

  return (
    <>
      <Box>
        <ScrollContainer
          autoHeight
          autoHeightMax={232}
          ref={scrollRef}
          onScroll={onScroll}
        >
          {emojiData
            // .filter(data => data.label === activeTab)
            .map((data, index) => (
              <EmojiList
                onEmojiClick={onEmojiClick}
                ref={node => setRefs(index, node)}
                data={data}
                key={data.label.toString()}
              />
            ))}
        </ScrollContainer>
      </Box>
      <Tabs>
        {emojiData.map((data, index) => (
          <Tab
            role="button"
            onClick={() => changeTab(index)}
            key={data.label.toString()}
            active={activeTab === index}
          >
            {data.data}
          </Tab>
        ))}
      </Tabs>
    </>
  );
}
