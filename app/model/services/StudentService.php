<?php

namespace App\Model\Services;


use App\Model\Entities\ClassEntity;
use App\Model\Entities\SchoolYear;
use App\Model\Entities\Student;
use Kdyby\Doctrine\EntityManager;

/**
 * Class StudentService
 * Class for business logic with students entity
 * @package App\Model\Services
 */
class StudentService extends BaseService
{
	/**
	 * @var SchoolYear
	 */
	private $actualYear;

	/**
	 * @var SchoolYear
	 */
	private $prevYear;

	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * @var ClassService
	 */
	private $classService;

	public function __construct(EntityManager $em, UserService $userService, ClassService $classService)
	{
		parent::__construct($em);
		$this->actualYear = $this->em->getRepository(SchoolYear::getClassName())->findCurrentSchoolYear();
		$this->prevYear = $this->em->getRepository(SchoolYear::getClassName())->findPreviousSchoolYear($this->actualYear);
		$this->userService = $userService;
		$this->classService = $classService;
	}

	/**
	 * Import students from given xls file to given class
	 * @param $file
	 * @param ClassEntity $classEntity
	 * @return array Passwords of new created students
	 * @throws BadClassNameException
	 * @throws BadFormatException
	 * @throws \PHPExcel_Reader_Exception
	 */
	public function importStudents($file, ClassEntity $classEntity)
	{
		$reader = \PHPExcel_IOFactory::createReaderForFile($file);
		$reader->setReadDataOnly(true);
		$excel = $reader->load($file);

		$excel->setActiveSheetIndex(0);

		$column = 'B';
		$row = 2;

		while ($column <= 'F' && $row <= 5) {
			$startCell = $excel->getActiveSheet()->getCell($column . $row);
			if ($startCell->getValue()) {
				break;
			}
			$column++;
			$row++;
		}

		if (!$startCell->getValue()) throw new BadFormatException();

		while (substr($startCell->getValue(), 0, 5) != 'téma' || $row > 30) {
			$startCell = $excel->getActiveSheet()->getCell($column . ++$row);
		}

		if (substr($startCell->getValue(), 0, 5) != 'téma') throw new BadFormatException();


		$row++;

		$passwords = array();
		while ($startCell->getValue()) {
			$myColumn = $column;
			$surname = $excel->getActiveSheet()->getCell($myColumn . $row)->getValue();
			$name = $excel->getActiveSheet()->getCell(++$myColumn . $row)->getValue();
			$class = $excel->getActiveSheet()->getCell(++$myColumn . $row)->getValue();

			if (!$name || !$surname || !$class) break;

			$surname = str_replace(' ', '', $surname);
			$name = str_replace(' ', '', $name);
			$class = mb_strtoupper(str_replace(' ', '', $class));

			if (!$classEntity->isGroup() && $classEntity->getName() != $class) {
				throw new BadClassNameException($class);
			}

			$student = $this->em->getRepository(Student::getClassName())->findByNameInClass($name, $surname, $class, $this->actualYear);

			if (!$classEntity->isGroup()) { // if this is regular class, only create new student and attach it to this class

				if (!$student) {
					$student = new Student();
					$student->setName($name)->setSurname($surname);
					$pass = $this->userService->addUser($student);
					$this->em->persist($student);
					$this->em->flush();
					$passwords[$student->getId()] = $pass;

					$classEntity->addStudent($student);
				}

			} else { // if this is group, search deeper for student in previous year
				if (!$student) { // not found student in this year
					$student = $this->em->getRepository(Student::getClassName())->findByNameInClass($name, $surname, $class, $this->prevYear);
					if (!$student) { // not found student in last year => create new one and create his class
						$student = new Student();
						$student->setName($name)->setSurname($surname);
						$pass = $this->userService->addUser($student);

						$newClass = $this->em->getRepository(ClassEntity::getClassName())->findBy(array('name' => $class, 'schoolYear' => $this->actualYear));
						if (!is_array($newClass) || !$newClass) {
							$newClass = new ClassEntity();
							$newClass->setSchoolYear($this->actualYear)->setName($class)->setType(ClassEntity::TYPE_CLASS);
						} else {
							$newClass = $newClass[0];
						}

						$newClass->addStudent($student);


						$this->em->persist($student);
						$this->em->persist($newClass);
						$this->em->flush();

						$passwords[$student->getId()] = $pass;
					} else { // found student in last year, copy old class
						$this->classService->copyClass($classEntity, $this->actualYear);
					}
				}

				if (!$student->isInClass($classEntity->getId())) {
					$classEntity->addStudent($student);
				}
			}

			$this->em->persist($classEntity);
			$this->em->flush();

			$row++;
		}

		return $passwords;
	}
}

class BadFormatException extends \Exception {}
class BadClassNameException extends \Exception{}