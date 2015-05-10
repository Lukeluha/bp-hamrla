CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `surname` varchar(255) NOT NULL DEFAULT '',
  `login` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `role` enum('admin','teacher','student') NOT NULL DEFAULT 'student',
  `room` varchar(30) DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `subjects` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `abbreviation` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `school_years` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `from` date NOT NULL,
  `to` date NOT NULL,
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `classes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `type` enum('class','group') NOT NULL DEFAULT 'class',
  `school_year_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `school_year_id` (`school_year_id`),
  CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `teachings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `subject_id` int(11) unsigned NOT NULL,
  `class_id` int(11) unsigned NOT NULL,
  `chat` enum('allowed','disallowed','no-anonymous') NOT NULL DEFAULT 'allowed',
  PRIMARY KEY (`id`),
  KEY `subject_id` (`subject_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `teachings_ibfk_1` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  CONSTRAINT `teachings_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `teaching_time` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `from` time NOT NULL,
  `to` time NOT NULL,
  `week_day` int(11) NOT NULL,
  `week_parity` enum('odd','even') DEFAULT NULL,
  `teaching_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `teaching_id` (`teaching_id`),
  CONSTRAINT `teaching_time_ibfk_1` FOREIGN KEY (`teaching_id`) REFERENCES `teachings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `teaching_teachers` (
  `teacher_id` int(11) unsigned NOT NULL,
  `teaching_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`teacher_id`,`teaching_id`),
  KEY `teaching_id` (`teaching_id`),
  CONSTRAINT `teaching_teachers_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teaching_teachers_ibfk_2` FOREIGN KEY (`teaching_id`) REFERENCES `teachings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `lessons` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `teaching_id` int(10) unsigned NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` longtext,
  PRIMARY KEY (`id`),
  KEY `teaching_id` (`teaching_id`),
  CONSTRAINT `lessons_ibfk_1` FOREIGN KEY (`teaching_id`) REFERENCES `teachings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `activity_points` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` int(11) unsigned NOT NULL,
  `lesson_id` int(11) unsigned NOT NULL,
  `count` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `activity_points_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activity_points_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `posts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned DEFAULT NULL,
  `text` text,
  `teaching_id` int(11) unsigned NOT NULL,
  `lesson_id` int(11) unsigned DEFAULT NULL,
  `created` datetime NOT NULL,
  `anonymous` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`teaching_id`,`lesson_id`),
  KEY `teaching_id` (`teaching_id`),
  KEY `lesson_id` (`lesson_id`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`teaching_id`) REFERENCES `teachings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `posts_ibfk_3` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `text` text,
  `reply_to` int(11) unsigned DEFAULT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `reply_to` (`reply_to`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`reply_to`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `questions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `question_text` text NOT NULL,
  `reason_require` tinyint(1) NOT NULL DEFAULT '0',
  `max_points` int(11) DEFAULT NULL,
  `lesson_id` int(11) unsigned DEFAULT NULL,
  `group_id` int(11) unsigned NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1',
  `correct_text_answer` text,
  `question_type` enum('choice','multipleChoice','text') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lesson_id` (`lesson_id`,`group_id`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `questions_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `question_options` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `option_text` text NOT NULL,
  `question_id` int(11) unsigned NOT NULL,
  `correct_answer` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `question_options_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tasks` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `task_name` varchar(255) NOT NULL DEFAULT '',
  `task_text` text NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '0',
  `end` datetime DEFAULT NULL,
  `limit_type` enum('strict','nostrict') DEFAULT NULL,
  `lesson_id` int(10) unsigned DEFAULT NULL,
  `student_rating` tinyint(1) NOT NULL DEFAULT '0',
  `group_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lesson_id` (`lesson_id`,`group_id`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `tasks_completed` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `note` text,
  `points` int(11) DEFAULT NULL,
  `student_id` int(11) unsigned NOT NULL,
  `task_id` int(11) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `filename` text NOT NULL,
  `image` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `task_id` (`task_id`),
  CONSTRAINT `tasks_completed_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tasks_completed_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `answers` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `answer_text` text,
  `question_id` int(11) unsigned NOT NULL,
  `student_id` int(11) unsigned NOT NULL,
  `points` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `option_id` (`question_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `answers_ibfk_1` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `answers_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `answer_option` (
  `option_id` int(11) unsigned NOT NULL,
  `answer_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`option_id`,`answer_id`),
  KEY `answer_id` (`answer_id`),
  CONSTRAINT `answer_option_ibfk_1` FOREIGN KEY (`option_id`) REFERENCES `question_options` (`id`) ON DELETE CASCADE,
  CONSTRAINT `answer_option_ibfk_2` FOREIGN KEY (`answer_id`) REFERENCES `answers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `chat_messages` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `from_user_id` int(11) unsigned NOT NULL,
  `to_user_id` int(11) unsigned NOT NULL,
  `message` text,
  `read` tinyint(1) NOT NULL DEFAULT '1',
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `from` (`from_user_id`),
  KEY `to` (`to_user_id`),
  CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`from_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`to_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE `ratings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `points` float DEFAULT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `task_completed_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `task_completed_id` (`task_completed_id`),
  CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`task_completed_id`) REFERENCES `tasks_completed` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE `student_class` (
  `student_id` int(11) unsigned NOT NULL,
  `class_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`student_id`,`class_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `student_class_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_class_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;





