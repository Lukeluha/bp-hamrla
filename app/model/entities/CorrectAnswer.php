<?php

namespace App\Model\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class CorrectAnswer
 * @package App\Model\Entities
 * @ORM\Table(name="correct_answers")
 * @ORM\Entity()
 */
class CorrectAnswer extends BaseEntity
{
	/**
	 * @var string
	 * @ORM\Column()
	 */
	protected $answerText;

	/**
	 * @var QuestionOption
	 * @ORM\ManyToOne(targetEntity="QuestionOption")
	 */
	protected $option;

	/**
	 * @var Question
	 * @ORM\ManyToOne(targetEntity="Question", inversedBy="correctAnswers")
	 */
	protected $question;

	/**
	 * @return string
	 */
	public function getAnswerText()
	{
		return $this->answerText;
	}

	/**
	 * @param string $answerText
	 * @return $this
	 */
	public function setAnswerText($answerText)
	{
		$this->answerText = $answerText;
		return $this;
	}

	/**
	 * @return QuestionOption
	 */
	public function getOption()
	{
		return $this->option;
	}

	/**
	 * @param QuestionOption $option
	 * @return $this
	 */
	public function setOption($option)
	{
		$this->option = $option;
		return $this;
	}

	/**
	 * @return Question
	 */
	public function getQuestion()
	{
		return $this->question;
	}

	/**
	 * @param Question $question
	 * @return $this
	 */
	public function setQuestion($question)
	{
		$this->question = $question;
		return $this;
	}





}