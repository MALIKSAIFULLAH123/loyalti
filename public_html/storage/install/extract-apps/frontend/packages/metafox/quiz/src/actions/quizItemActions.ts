import { HandleAction } from '@metafox/framework';

export default function quizItemActions(handleAction: HandleAction) {
  return {
    deleteQuiz: () => handleAction('deleteItem'),
    approveQuiz: () => handleAction('approveItem')
  };
}
