export interface AnswerQuizProps {
  answer: string;
  answer_id?: number;
  is_correct?: 0 | 1;
  ordering?: number;
}

export interface QuestionItemProps {
  question: string;
  question_id?: number;
  answers: AnswerQuizProps[];
  ordering?: number;
}

export interface QuizItemProps {
  index: number;
  name: string;
  disabled: boolean;
  item: QuestionItemProps;
  lastElement: boolean;
  maxLength?: any;
  formik: any;
  error: string | Record<string, any>;
  submitCount?: number;
  handleChangeAnswer: (e, questionIndex: number, answerIndex: number) => void;
  handleChangeQuestion: (e, index: number) => void;
  addMoreQuestion: () => void;
  handleDeleteQuestion: () => void;
  addMoreAnswer: () => void;
  handleDeleteAnswer: (value) => void;
  handleChangeCorrectAnswer: (
    e,
    questionIndex: number,
    answerIndex: number
  ) => void;
  handleTouched?: () => void;
  maxAnswerLength?: number;
}
