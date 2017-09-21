SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE FUNCTION DOW(date DATE) RETURNS INTEGER DETERMINISTIC RETURN DAYOFWEEK(date)-1;

CREATE TABLE `absences` (
  `student_id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `number` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bugreports` (
  `id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED DEFAULT NULL,
  `student_id` int(10) UNSIGNED DEFAULT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `config` (
  `key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `courses` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_id` int(10) UNSIGNED DEFAULT NULL,
  `maxstudents` smallint(5) UNSIGNED DEFAULT NULL,
  `yearfrom` tinyint(3) UNSIGNED DEFAULT NULL,
  `yearto` tinyint(3) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `course_group` (
  `course_id` int(10) UNSIGNED NOT NULL,
  `group_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `forms` (
  `group_id` int(10) UNSIGNED NOT NULL,
  `year` tinyint(3) UNSIGNED NOT NULL,
  `kv_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `groups` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `group_student` (
  `group_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `group_teacher` (
  `group_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `lessons` (
  `id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `number` tinyint(3) UNSIGNED NOT NULL,
  `cancelled` tinyint(1) NOT NULL DEFAULT '0',
  `room_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL,
  `substitute_id` int(10) UNSIGNED DEFAULT NULL,
  `course_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
  (1, '2017_06_01_000000_initial', 1);

CREATE TABLE `offdays` (
  `id` int(10) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `number` tinyint(3) UNSIGNED DEFAULT NULL,
  `group_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `registrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `lesson_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `obligatory` tinyint(1) NOT NULL,
  `attendance` tinyint(1) DEFAULT NULL,
  `documentation` text COLLATE utf8mb4_unicode_ci,
  `feedback` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rooms` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `capacity` smallint(5) UNSIGNED NOT NULL,
  `yearfrom` tinyint(3) UNSIGNED DEFAULT NULL,
  `yearto` tinyint(3) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `students` (
  `id` int(10) UNSIGNED NOT NULL,
  `firstname` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `untis_id` varchar(14) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `subjects` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `subject_teacher` (
  `subject_id` int(10) UNSIGNED NOT NULL,
  `teacher_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `teachers` (
  `id` int(10) UNSIGNED NOT NULL,
  `firstname` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shortname` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `admin` tinyint(1) NOT NULL DEFAULT '0',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `info` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `timetable` (
  `day` tinyint(3) UNSIGNED NOT NULL,
  `number` tinyint(3) UNSIGNED NOT NULL,
  `form_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `absences`
  ADD PRIMARY KEY (`student_id`, `date`, `number`),
  ADD KEY `absences_date_number_index` (`date`, `number`),
  ADD KEY `absences_number_index` (`number`);

ALTER TABLE `bugreports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bugreports_teacher_id_foreign` (`teacher_id`),
  ADD KEY `bugreports_student_id_foreign` (`student_id`),
  ADD KEY `bugreports_date_index` (`date`);

ALTER TABLE `config`
  ADD PRIMARY KEY (`key`);

ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `courses_subject_id_index` (`subject_id`);

ALTER TABLE `course_group`
  ADD PRIMARY KEY (`course_id`, `group_id`),
  ADD KEY `course_group_group_id_index` (`group_id`);

ALTER TABLE `forms`
  ADD PRIMARY KEY (`group_id`),
  ADD KEY `forms_kv_id_index` (`kv_id`);

ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `groups_name_unique` (`name`);

ALTER TABLE `group_student`
  ADD PRIMARY KEY (`group_id`, `student_id`),
  ADD KEY `group_student_student_id_index` (`student_id`);

ALTER TABLE `group_teacher`
  ADD PRIMARY KEY (`group_id`, `teacher_id`),
  ADD KEY `group_teacher_teacher_id_index` (`teacher_id`);

ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_reserved_at_index` (`queue`, `reserved_at`);

ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lessons_teacher_id_date_number_unique` (`teacher_id`, `date`, `number`),
  ADD KEY `lessons_room_id_foreign` (`room_id`),
  ADD KEY `lessons_teacher_id_date_number_cancelled_course_id_index` (`teacher_id`, `date`, `number`, `cancelled`, `course_id`),
  ADD KEY `lessons_date_number_cancelled_index` (`date`, `number`, `cancelled`),
  ADD KEY `lessons_date_number_room_id_index` (`date`, `number`, `room_id`),
  ADD KEY `lessons_course_id_date_number_index` (`course_id`, `date`, `number`),
  ADD KEY `lessons_cancelled_course_id_index` (`cancelled`, `course_id`),
  ADD KEY `lessons_course_id_teacher_id_index` (`course_id`, `teacher_id`);

ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `offdays`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `offdays_group_id_date_number_unique` (`group_id`, `date`, `number`),
  ADD KEY `offdays_date_number_index` (`date`, `number`);

ALTER TABLE `registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registrations_lesson_id_student_id_unique` (`lesson_id`, `student_id`),
  ADD KEY `registrations_student_id_attendance_index` (`student_id`, `attendance`),
  ADD KEY `registrations_lesson_id_attendance_index` (`lesson_id`, `attendance`);

ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rooms_name_unique` (`name`),
  ADD KEY `rooms_type_index` (`type`);

ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `students_username_unique` (`username`),
  ADD UNIQUE KEY `students_untis_id_unique` (`untis_id`),
  ADD KEY `students_lastname_firstname_index` (`lastname`, `firstname`);

ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subjects_name_unique` (`name`);

ALTER TABLE `subject_teacher`
  ADD PRIMARY KEY (`subject_id`, `teacher_id`),
  ADD KEY `subject_teacher_teacher_id_foreign` (`teacher_id`);

ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teachers_username_unique` (`username`),
  ADD KEY `teachers_lastname_firstname_index` (`lastname`, `firstname`);

ALTER TABLE `timetable`
  ADD PRIMARY KEY (`form_id`, `day`, `number`);

ALTER TABLE `bugreports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `courses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `groups`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `lessons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `offdays`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `registrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `rooms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `subjects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `teachers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `absences`
  ADD CONSTRAINT `absences_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

ALTER TABLE `bugreports`
  ADD CONSTRAINT `bugreports_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `bugreports_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`);

ALTER TABLE `courses`
  ADD CONSTRAINT `courses_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

ALTER TABLE `course_group`
  ADD CONSTRAINT `course_group_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`),
  ADD CONSTRAINT `course_group_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

ALTER TABLE `forms`
  ADD CONSTRAINT `forms_kv_id_foreign` FOREIGN KEY (`kv_id`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `forms_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`);

ALTER TABLE `group_student`
  ADD CONSTRAINT `group_student_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `group_student_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`);

ALTER TABLE `group_teacher`
  ADD CONSTRAINT `group_teacher_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `group_teacher_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`);

ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `lessons_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`),
  ADD CONSTRAINT `lessons_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `lessons_substitute_id_foreign` FOREIGN KEY (`substitute_id`) REFERENCES `teachers` (`id`);

ALTER TABLE `offdays`
  ADD CONSTRAINT `offdays_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`);

ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `registrations_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`);

ALTER TABLE `subject_teacher`
  ADD CONSTRAINT `subject_teacher_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `subject_teacher_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

ALTER TABLE `timetable`
  ADD CONSTRAINT `timetable_form_id_foreign` FOREIGN KEY (`form_id`) REFERENCES `forms` (`group_id`);

COMMIT;
