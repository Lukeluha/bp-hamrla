<?php

namespace App\Model\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Question
 * @package App\Model\Entities
 * @ORM\Entity(repositoryClass="App\Model\Repositories\Questions")
 * @ORM\Table(name="questions")
 */
class Question extends BaseEntity
{

	const TYPE_CHOICE = 'choice';
	const TYPE_MULTIPLECHOICE = 'multipleChoice';
	const TYPE_TEXT = 'text';

	/**
	 * @var string
	 * @ORM\Column()
	 */
	protected $questionText;

	/**
	 * @var boolean
	 * @ORM\Column(type="integer")
	 */
	protected $reasonRequire = false;

	/**
	 * @var int
	 * @ORM\Column()
	 */
	protected $maxPoints;

	/**
	 * @var Lesson
	 * @ORM\ManyToOne(targetEntity="Lesson", inversedBy="questions")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $lesson;

	/**
	 * @var boolean
	 * @ORM\Column(type="integer")
	 */
	protected $visible = false;

	/**
	 * @var Group
	 * @ORM\ManyToOne(targetEntity="Group")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $group;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="QuestionOption", mappedBy="question", fetch="EXTRA_LAZY")
	 */
	protected $options;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="Answer", mappedBy="question")
	 */
	protected $answers;

	/**
	 * @var string
	 * @ORM\Column()
	 */
	protected $questionType;

	/**
	 * @var string
	 * @ORM\Column()
	 */
	protected $correctTextAnswer;

	public function __construct()
	{
		$this->options = new ArrayCollection();
		$this->answers = new ArrayCollection();
	}

	/**
	 * @return string
	 */
	public function getQuestionText()
	{
		return $this->questionText;
	}

	/**
	 * @param string $questionText
	 * @return $this
	 */
	public function setQuestionText($questionText)
	{
		$this->questionText = $questionText;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getReasonRequire()
	{
		return $this->reasonRequire;
	}

	/**
	 * @return boolean
	 */
	public function isReasonRequire()
	{
		return $this->reasonRequire;
	}

	/**
	 * @param boolean $reasonRequire
	 * @return $this
	 */
	public function setReasonRequire($reasonRequire)
	{
		$this->reasonRequire = $reasonRequire;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getMaxPoints()
	{
		return $this->maxPoints;
	}

	/**
	 * @param int $maxPoints
	 * @return $this
	 */
	public function setMaxPoints($maxPoints)
	{
		$this->maxPoints = $maxPoints;
		return $this;
	}

	/**
	 * @return Lesson
	 */
	public function getLesson()
	{
		return $this->lesson;
	}

	/**
	 * @param Lesson $lesson
	 * @return $this
	 */
	public function setLesson($lesson)
	{
		$this->lesson = $lesson;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getVisible()
	{
		return $this->visible;
	}

	/**
	 * @return boolean
	 */
	public function isVisible()
	{
		return $this->visible;
	}

	/**
	 * @param boolean $visible
	 * @return $this
	 */
	public function setVisible($visible)
	{
		$this->visible = $visible;
		return $this;
	}

	/**
	 * @return Group
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * @param Group $group
	 * @return $this
	 */
	public function setGroup($group)
	{
		$this->group = $group;
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

	public function getOptionsCount()
	{
		return $this->options->count();
	}


	/**
	 * @return ArrayCollection
	 */
	public function getAnswers()
	{
		return $this->answers;
	}

	/**
	 * @param ArrayCollection $answers
	 * @return $this
	 */
	public function setAnswers($answers)
	{
		$this->answers = $answers;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getQuestionType()
	{
		return $this->questionType;
	}

	/**
	 * @param string $questionType
	 * @return $this
	 */
	public function setQuestionType($questionType)
	{
		$this->questionType = $questionType;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCorrectTextAnswer()
	{
		return $this->correctTextAnswer;
	}

	/**
	 * @param string $correctTextAnswer
	 * @return $this
	 */
	public function setCorrectTextAnswer($correctTextAnswer)
	{
		$this->correctTextAnswer = $correctTextAnswer;
		return $this;
	}


	public function getQuestionTypeText()
	{
		if ($this->questionType == self::TYPE_CHOICE) {
			return "Uzavřená (jedna možná odpověď)";
		} elseif ($this->questionType == self::TYPE_MULTIPLECHOICE) {
			return "Uzavřená (více možných odpovědí)";
		} else {
			return "Otevřená";
		}
	}

}