<?php
/**
 * Created by PhpStorm.
 * User: lukas
 * Date: 28.04.15
 * Time: 22:43
 */

namespace App\Model\Services;


use App\Model\Entities\Answer;
use App\Model\Entities\Question;
use App\Model\Entities\QuestionOption;

class AnswerService extends BaseService
{
	public function computePointsPercentage(Answer $answer)
	{
		$question = $answer->getQuestion();
		if ($question->getQuestionType() == Question::TYPE_CHOICE) {
			$rightAnswer = $this->em->getRepository(QuestionOption::getClassName())
									->findOneBy(array(
										'question' => $question->getId(),
										'correct' => true
									));
			$chosenOption = $answer->getOptions()->first();
			if ($rightAnswer->getId() == $chosenOption->getId()) {
				return 100;
			} else {
				return 0;
			}
		} elseif ($question->getQuestionType() == Question::TYPE_MULTIPLECHOICE) {
			$rightAnswers = $this->em->getRepository(QuestionOption::getClassName())
				->findAssoc(array(
					'question' => $question->getId(),
					'correct' => true
				), 'id');


			$chosenOptions = $answer->getOptions();

			$chosenOptionsIds = array();
			$fails = 0;

			foreach ($chosenOptions as $chosenOption) {
				if (!isset($rightAnswers[$chosenOption->getId()])) $fails++;
				$chosenOptionsIds[$chosenOption->getId()] = true;
			}


			foreach ($rightAnswers as $id => $rightAnswer) {
				if (!isset($chosenOptionsIds[$id])) $fails++;
			}

			$points = ($question->getOptions()->count() - $fails) / $question->getOptions()->count();

			return $points * 100;
		} else { // text question
			if ($question->getCorrectTextAnswer()) {
				if (mb_strtolower(trim($question->getCorrectTextAnswer())) == mb_strtolower(trim($answer->getAnswerText()))) {
					return 100;
				} else {
					return 0;
				}
			} else {
				return null;
			}
		}



	}

}