<?php

namespace App\Model\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class QuestionOption
 * @package App\Model\Entities
 * @ORM\Table(name="question_options")
 * @ORM\Entity()
 */
class QuestionOption extends BaseEntity
{
	/**
	 * @var string
	 * @ORM\Column()
	 */
	protected $optionText;

	/**
	 * @var Question
	 * @ORM\ManyToOne(targetEntity="Question", inversedBy="options")
	 */
	protected $question;

	/**
	 * @return string
	 */
	public function getOptionText()
	{
		return $this->optionText;
	}

	/**
	 * @param string $optionText
	 * @return $this
	 */
	public function setOptionText($optionText)
	{
		$this->optionText = $optionText;
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