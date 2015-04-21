<?php

namespace App\Model\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Answer
 * @package App\Model\Entities
 * @ORM\Table(name="answers")
 * @ORM\Entity()
 */
class Answer extends BaseEntity
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
	 * @ORM\ManyToOne(targetEntity="Question", inversedBy="answers")
	 */
	protected $question;

	/**
	 * @var Student
	 * @ORM\ManyToOne(targetEntity="Student")
	 */
	protected $student;

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

	/**
	 * @return Student
	 */
	public function getStudent()
	{
		return $this->student;
	}

	/**
	 * @param Student $student
	 * @return $this
	 */
	public function setStudent($student)
	{
		$this->student = $student;
		return $this;
	}

}