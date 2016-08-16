<?php

/* File with data in json format */
define('DATA_FILE', 'clickstream_export');

/** Database */
define('DB_NAME', 'databasename');
define('DB_USER', 'databaseuser');
define('DB_PASSWORD', 'databasepassword');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8');

/** Database Coursera with general information about the course and anonimized users */
define('DB_NAME_G', 'databasename2');
define('DB_USER_G', 'databaseuser2');
define('DB_PASSWORD_G', 'databasepassword2');
define('DB_HOST_G', 'localhost');
define('DB_CHARSET_G', 'utf8');

/** Course dates: day-month-year */
define('COURSE_END', '28-09-2014');
define('COURSE_EXTRA', '29-09-2014');
define('COURSE_URL', 'https://class.coursera.org/coursepath');
define('COURSE_PATH', 'coursepath');
define('SEMANA1_INI', '01-09-2014 00:00:00'); // 1-7 de septiembre 2014
define('SEMANA1_FIN', '07-09-2014 23:59:59');
define('SEMANA2_INI', '08-09-2014 00:00:00'); // 8-14 de septiembre 2014
define('SEMANA2_FIN', '14-09-2014 23:59:59');
define('SEMANA3_INI', '15-09-2014 00:00:00'); // 15-21 de septiembre 2014
define('SEMANA3_FIN', '21-09-2014 23:59:59');
define('SEMANA4_INI', '22-09-2014 00:00:00'); // 22-30 de septiembre 2014
define('SEMANA4_FIN', '28-09-2014 23:59:59');

/** CSV */
define('CSV_DELIMITER', ';');

/** DEBUG */
define('DEBUG', false);

/* That's all, stop editing! */
/* Data directory */
define('DATA_DIR', 'data/');
define('DATA_CSV', 'csv/');

set_time_limit(0);