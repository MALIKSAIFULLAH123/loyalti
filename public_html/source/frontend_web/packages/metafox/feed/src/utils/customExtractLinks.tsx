import linkifyIt from 'linkify-it';
import tlds from 'tlds';

interface Props {
  text: string;
  recognizeMail?: boolean;
}

export const customExtractLinks = ({ text, recognizeMail = true }: Props) =>
  linkifyIt().tlds(tlds).set({ fuzzyEmail: recognizeMail }).match(text);
