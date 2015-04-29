<?php

namespace App\Model\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Answer
 * @package App\Model\Entities
 * @ORM\Table(name="answers")
 * @ORM\Entity(repositoryClass="App\Model\Repositories\Answers")
 */
class Answer extends BaseEntity
{
	/**
	 * @var string
	 * @ORM\Column()
	 */
	protected $answerText;

	/**
	 * @var ArrayCollection
	 * @ORM\ManyToMany(targetEntity="QuestionOption")
	 * @ORM\JoinTable(name="answer_option",
	 *      joinColumns={@ORM\JoinColumn(name="answer_id", referencedColumnName="id")},
	 *		inverseJoinColumns={@ORM\JoinColumn(name="option_id", referencedColumnName="id")}
	 *		)
	 */
	protected $options;

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
	 * @var int
	 * @ORM\Column()
	 */
	protected $points;

	public function __construct()
	{
		$this->options = new ArrayCollection();
	}

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
	 * @return ArrayCollection
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * @param ArrayCollection $options
	 * @return $this
	 */
	public function setOptions($options)
	{
		$this->options = $options;
		return $this;
	}

	public function addOption(QuestionOption $option)
	{
		$this->options->add($option);
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

	/**
	 * @return int
	 */
	public function getPoints()
	{
		return $this->points;
	}

	/**
	 * @param int $points
	 * @return $this
	 */
	public function setPoints($points)
	{
		$this->points = $points;
		return $this;
	}

}