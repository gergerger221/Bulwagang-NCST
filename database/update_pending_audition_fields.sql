-- Add birth_date, gender, student_level, shs_strand, college_course to pending_audition table
ALTER TABLE `pending_audition`
  ADD COLUMN `birth_date` DATE NULL AFTER `last_name`,
  ADD COLUMN `gender` ENUM('male','female','other','prefer_not_to_say') NULL AFTER `birth_date`,
  ADD COLUMN `student_level` ENUM('shs','college','other') NOT NULL DEFAULT 'other' AFTER `gender`,
  ADD COLUMN `shs_strand` ENUM(
    'ABM',
    'HUMSS',
    'STEM',
    'Culinary Arts (TVL-HE)',
    'Hotel & Restaurant Services (TVL-HE)'
  ) NULL AFTER `student_level`,
  ADD COLUMN `college_course` ENUM(
    'ACT','AOM','AB Comm','BSA','BS Arch','BSBA-FM','BSBA-MM','BSBA-OM','BSCrim','BSCA','BSCpE','BSCS','BSECE','BSEntrep','BSHM','BSIE','BSISM','BSIT','BSMA','BSOA','BSPsy','BSPA','BSREM','BSTM','BSEd-Eng','BSEd-Fil','BSEd-Math','BSEd-SS'
  ) NULL AFTER `shs_strand`;

-- Optional defaults/init
UPDATE `pending_audition` 
  SET `gender` = COALESCE(`gender`, 'prefer_not_to_say'),
      `student_level` = COALESCE(`student_level`, 'other')
WHERE 1=1;
