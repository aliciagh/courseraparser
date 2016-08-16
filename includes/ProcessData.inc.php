<?php
/**
 * Created by PhpStorm.
 * User: mambanegra
 * Date: 08/03/16
 * Time: 19:14
 */
require_once('ManageDatabase.php');

class ProcessData {
    public $gtotal = 0;
    public $gsemana1 = 0;
    public $gsemana2 = 0;
    public $gsemana3 = 0;
    public $gsemana4 = 0;
    public $gpageview = 0;
    public $gvideo = 0;
    public $ghg = 0;
    public $gblank = 0;
    public $gtime = 0;
    public $gextratime = 0;
    public $gsessions = 0;
    public $gextrasessions = 0;
    public $gresources = array(
        '/' => 0,
        '/auth' => 0, // Clicks-auth
        '/forum' => 0, // Clicks-forum
        '/human_grading' => 0, // Clicks-human-grading
        '/lecture' => 0, // Clicks-lecture
        '/quiz' => 0, // Clicks-quiz
        '/wiki' => 0, // Clicks-wiki
        '/search' => 0, // Clicks-search
        '/other' => 0
    );

    function searchData($wrappers = array(), $userid) {

        if(empty($wrappers)) {
            $wrappers['pre'] = '<span>';
            $wrappers['post'] = '</span>';
        }

        $db = ManageDatabase::getInstance();
        $clicks = $db->searchUser($userid);

        if(empty($clicks)) {
            echo $wrappers['pre'];
            echo "Debes indicar un identificador de usuario que exista en la base de datos";
            echo $wrappers['post'];
        }

        foreach($clicks as $c) {
            echo isset($wrappers['preline']) ? $wrappers['preline'] : '';
            echo $wrappers['pre']. date('d-m-Y H:i:s', $c->timestamp_end/1000).$wrappers['post'];
            echo $wrappers['pre']. $this->getPath($c->page_from).$wrappers['post'];
            echo $wrappers['pre']. date('d-m-Y H:i:s', $c->timestamp/1000).$wrappers['post'];
            echo $wrappers['pre']. $this->getPath($c->page_url).$wrappers['post'];
            echo $wrappers['pre']. $c->type.$wrappers['post'];
            echo isset($wrappers['postline']) ? $wrappers['postline'] : '';
        }
    }

    function getUserStatistics($userslist, $open = '') {
        if(empty($userslist)) {
            echo '<p class="lead text-center">Debes indicar al menos un session user id.</p>';
            return false;
        }

        $users = trim($userslist);
        $ids = explode("\n", $users);
        $ids = array_map('trim', $ids);

        $name = time().'-users.csv';
        $headers = array(
            'Clicks', // $total
            'Semana1', //$semanas
            'Semana2',
            'Semana3',
            'Semana4',
            'Clicks_homepage',
            'Clicks_auth',
            'Clicks_forum',
            'Clicks_human_grading',
            'Clicks_lecture',
            'Clicks_quiz',
            'Clicks_wiki',
            'Clicks_search',
            'Clicks_other',
            'Videos_diferentes', // $resources['totallectures']
            'Autoevaluaciones_diferentes', // $resources['totalquizes']
            'Total_dias_distintos', // $diferentdays
            'Dias_entre_primera_ultima_conexion', // $period
            'Primera_conexion', // $firstconnection
            'Ultima_conexion', // $lastconnection
            'Total_segundos_reales', // $time['seconds']
            'Total_sesiones', // $time['sessions']
            'Dias_distintos_despues_septiembre', // $extradays
            'Segundos_reales_despues_septiembre', // $extratime['seconds']
            'Total_sesiones_despues_septiembre' // $extratime['sessiones']
        );

        $file = fopen(DATA_CSV.$name, 'w');
        fputcsv($file, $headers, CSV_DELIMITER);

        $db = ManageDatabase::getInstance();

        echo ' <div class="panel-group" id="accordion">';

        foreach($ids as $id) {
            $data = $db->getNClicks($id);
            $dates = $db->getConections($id);
            $total = 0;

            echo '<div class="panel panel-default">';
            echo '<div class="panel-heading">';
            echo'<h4 class="panel-title">';
            echo '<a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$id.'">'. $id;

            if(empty($data)) {
                echo ' <span class="label label-danger">Not found</span></a></h4></div>';
            } else {
                echo '</a>';
                echo '</h4>';
                echo '</div>';

                echo '<div id="collapse'.$id.'" class="panel-collapse collapse '.$open.'">';
                echo '<div class="panel-body">';

                // Número de clics de cada tipo
                echo '<strong>Clics de cada tipo:</strong>';
                echo '<div class="row">';
                foreach ($data as $c) {
                    $total += $c->count;
                    $this->totalSum($c);

                    echo '<div class="col-sm-3">';
                    echo empty($c->type) ? 'blank':$c->type;
                    echo ': ' . $c->count . '</div>';
                }
                echo '</div>';

                // Número de clics
                echo '<p><strong>Total de clics:</strong> ' . $total . '</p>';


                // Clics por semana del curso
                $semanas = $this->countClicksWeek($id);

                // Recursos en los que se ha hecho clic
                $resources = $this->analysesUserResources($id);
                foreach($resources['count'] as $k => $c) {
                    if (isset($this->gresources[$k])) {
                        $this->gresources[$k] += $c;
                    } else {
                        $this->gresources[$k] = $c;
                    }
                }
                $resources_home = !empty($resources['count']['/']) ? $resources['count']['/'] : 0;
                $resources_auth = !empty($resources['count']['/auth']) ? $resources['count']['/auth'] : 0;
                $resources_forum = !empty($resources['count']['/forum']) ? $resources['count']['/forum'] : 0;
                $resources_hg = !empty($resources['count']['/human_grading']) ? $resources['count']['/human_grading'] : 0;
                $resources_lecture = !empty($resources['count']['/lecture']) ? $resources['count']['/lecture'] : 0;
                $resources_quiz = !empty($resources['count']['/quiz']) ? $resources['count']['/quiz'] : 0;
                $resources_wiki = !empty($resources['count']['/wiki']) ? $resources['count']['/wiki'] : 0;
                $resources_search = !empty($resources['count']['/search']) ? $resources['count']['/search'] : 0;
                $resources_other = !empty($resources['count']['/other']) ? $resources['count']['/other'] : 0;

                // Número de días conectado
                $firstconnection = current($dates)->date;
                $lastconnection = end($dates)->date;
                $period = (strtotime($lastconnection)-strtotime($firstconnection))/86400;
                $period = abs($period);
                $period = floor($period);
                $diferentdays = count($dates);

                echo '<p><strong>Número de días conectado:</strong> '. $diferentdays .'</p>';
                echo '<p><strong>Días transcurridos:</strong> '. $period .'</p>';
                echo '<p><strong>Primera conexión:</strong> ' . $firstconnection .'</p>';
                echo '<p><strong>Última conexión:</strong> '. $lastconnection .'</p>';

                // Tiempo total de conexión
                $time = $this->getConnectionTime($id);
                $this->gtime += $time['seconds'];
                $this->gsessions += $time['sessions'];

                echo '<p><strong>Tiempo total conectado (min de diferencia entre clics menor que 1h):</strong> '.$this->secondsHours($time['seconds']).'</p>';
                echo '<p><strong>Sesiones totales (se considera sesión nueva si hay más de 1h de diferencia entre clics):</strong> '.$time['sessions'].'</p>';

                // Conexión después de finalizar el curso
                $extradays = 0;
                $extratime = array('seconds' => 0, 'sessions' => 0);
                if(strtotime($lastconnection) > strtotime(COURSE_END)) {
                    $extra = $db->getClicksExtra($id);

                    $extraurls = array();
                    $extradates = array();
                    foreach($extra as $e) {
                        $extraurls[] = $e->page_url;
                        $extradates[] = date('d-m-Y', $e->timestamp/1000);
                    }

                    $extradays = count(array_unique($extradates));

                    echo '<p><strong>Días conectado después de septiembre:</strong> '. $extradays .'</p>';

                    $extratime = $this->getConnectionTime($extra, false);
                    $this->gextratime += $extratime['seconds'];
                    $this->gextrasessions += $extratime['sessions'];

                    echo '<p><strong>Tiempo total conectado después de septiembre:</strong> '.$this->secondsHours($extratime['seconds']).'</p>';
                    echo '<p><strong>Sesiones totales después de septiembre:</strong> '.$extratime['sessions'].'</p>';

                    $this->analysesUserResources($extraurls, false);
                }

                // Build learning paths
                //$graph = $this->buildLearningPaths(array($id));
                //$this->paintGraph($graph);

                $line = array($total, // Clicks
                    $semanas[1], // Semana1
                    $semanas[2], // Semana2
                    $semanas[3], // Semana3
                    $semanas[4], // Semana4
                    $resources_home, // Clicks-homepage
                    $resources_auth, // Clicks-auth
                    $resources_forum, // Clicks-forum
                    $resources_hg, // Clicks-human-grading
                    $resources_lecture, // Clicks-lecture
                    $resources_quiz, // Clicks-quiz
                    $resources_wiki, // Clicks-wiki
                    $resources_search, // Clicks-search
                    $resources_other, // Clicks-other
                    $resources['totallectures'], // Videos diferentes
                    $resources['totalquizes'], // Autoevaluaciones diferentes
                    $diferentdays, // Días diferentes que se conectó (total)
                    $period, // Días transcurridos desde la primera conexión a la última
                    $firstconnection, // Fecha de la primera conexión
                    $lastconnection, // Fecha de la última conexión
                    $time['seconds'], // Tiempo total en segundos que interactuó con el curso
                    $time['sessions'], // Número total de sesiones
                    $extradays, // Días diferentes que se conectó después de que finalizara el curso
                    $extratime['seconds'], // Tiempo total en segundos que se conectó después de que finalizara
                    $extratime['sessions']);
                fputcsv($file, $line, CSV_DELIMITER);

                echo '</div>';
                echo '</div>';
            }

            echo '</div>';
        }

        fclose($file);

        echo '</div>';

        return true;
    }

    function analysesUserResources($list, $user = true) {
        $db = ManageDatabase::getInstance();

        if($user) {
            $resources = $db->getResources(array($list));
        } else {
            $resources = $list;
        }

        $clean = $this->countResources($resources);

        echo '<p><strong>Clics por recurso:</strong></p>';

        foreach($clean['count'] as $k => $c) {
            echo '<p>'.$k.': '. $c .'</p>';
        }

        echo '<p><strong>Vídeos consultados:</strong> '.$clean['totallectures'].'</p>';

        echo '<p><strong>Autoevaluaciones realizadas:</strong> '.$clean['totalquizes'].'</p>';

        return $clean;
    }

    function buildLearningPaths($list) {
        $db = ManageDatabase::getInstance();
        $clicks = array();
        foreach($list as $l) {
            $temp = $db->searchUser($l);
            $clicks = array_merge($clicks, $temp);
        }

        $graph = array();

        foreach($clicks as $c) {
            $from = $this->getSimpleFromPath($c->page_from);
            $current = $this->getSimplePath($c->page_url);

            if(!empty($from)) {
                // Si es la primera vez que se navega desde from
                if (!isset($graph[$from])) {
                    $graph[$from] = array();
                }

                // Si es la primera que se navega desde from a current
                if (!isset($graph[$from][$current])) {
                    $graph[$from][$current] = array();
                    $graph[$from][$current] = 1;
                } else { // Incrementamos el contador
                    $graph[$from][$current]++;
                }
            }
        }

        return $graph;
    }

    function getConnectionTime($data, $user = true) {
        $db = ManageDatabase::getInstance();

        if($user) {
            $clicks = $db->searchUser($data);
        } else {
            $clicks = $data;
        }

        $total = 0;
        $sessions = 1;
        $previous_ini = 0;
        foreach($clicks as $c) {
            $current_ini = $c->timestamp / 1000;
            $current_before = $c->timestamp_end / 1000;

            if($previous_ini != 0) {
                $diff = round(abs($current_before - $previous_ini));

                if($diff < 3600) {
                    $total += $diff;
                } else {
                    $sessions++;
                }
            }

            $previous_ini = $current_ini;
        }

        $return = array('seconds' => $total, 'sessions' => $sessions);
        return $return;
    }

    private function countClicksWeek($id) {
        $db = ManageDatabase::getInstance();
        $semanas = $db->getClicksWeek($id);

        echo '<p><strong>Clics por semana:</strong></p>';

        for($i=1; $i<=4; $i++) {
            $variable = 'gsemana'.$i;
            $this->{$variable} += $semanas[$i];
            echo '<p>Semana '.$i.': '.$semanas[$i].'</p>';
        }

        return $semanas;
    }

    function getGlobalStatistics() {
        // Estadísticas globales
        echo '<div class="jumbotron">';
        echo '<h1>Estadísticas globales</h1>';
        echo '<p><strong>Total de clics:</strong> '.$this->gtotal.'</p>';
        echo '<div class="col-sm-3"><p><strong>blank:</strong> '.$this->gblank.'</p></div>';
        echo '<div class="col-sm-3"><p><strong>hg pageview:</strong> '.$this->ghg.'</p></div>';
        echo '<div class="col-sm-3"><p><strong>pageview:</strong> '.$this->gpageview.'</p></div>';
        echo '<div class="col-sm-3"><p><strong>Video:</strong> '.$this->gvideo.'</p></div>';
        echo '<div class="col-sm-3"><p><strong>Semana 1:</strong> '.$this->gsemana1.'</p></div>';
        echo '<div class="col-sm-3"><p><strong>Semana 2:</strong> '.$this->gsemana2.'</p></div>';
        echo '<div class="col-sm-3"><p><strong>Semana 3:</strong> '.$this->gsemana3.'</p></div>';
        echo '<div class="col-sm-3"><p><strong>Semana 4:</strong> '.$this->gsemana4.'</p></div>';

        foreach($this->gresources as $k => $c) {
            echo '<div class="col-sm-3"><p><strong>'.$k.':</strong> '.$c.'</p></div>';
        }

        echo '<p><strong>Tiempo total de conexión:</strong> '.$this->secondsHours($this->gtime).'</p>';
        echo '<p><strong>Sesiones totales:</strong> '.$this->gsessions.'</p>';

        echo '<p><strong>Tiempo total de conexión después de septiembre:</strong> '.$this->secondsHours($this->gextratime).'</p>';
        echo '<p><strong>Sesiones totales después de septiembre:</strong> '.$this->gextrasessions.'</p>';

        $media = ($this->gtotal > 0) ? $this->secondsHours(round(abs($this->gtime/$this->gtotal))) : 0;
        echo '<p><strong>La media de tiempo por clic:</strong> '.$media.'</p>';
        echo '</div>';
    }

    private function totalSum($valor) {
        $this->gtotal += $valor->count;

        switch($valor->type) {
            case 'pageview':
                $this->gpageview += $valor->count;
                break;
            case 'hg.hg.pageview':
                $this->ghg += $valor->count;
                break;
            case 'user.video.lecture.action':
                $this->gvideo += $valor->count;
                break;
            default:
                $this->gblank += $valor->count;
                break;
        }
    }


    private function getPath($url) {
        $url = str_replace(COURSE_URL, '', $url);
        if(empty($url)) {
            return '/';
        }

        $temp = parse_url($url);
        if(preg_match('/^\/human_grading/', $temp['path'])) {
            $path = '/human_grading';
        } elseif(strcmp('/lecture/view', $temp['path']) === 0) {
            $num = substr($temp['query'], strrpos($temp['query'], '=') + 1);
            $path = '/lecture/'.$num;
        } elseif(strcmp('/quiz/start', $temp['path']) === 0 || strcmp('/quiz/attempt', $temp['path']) === 0) {
            $num = substr($temp['query'], strrpos($temp['query'], '=') + 1);
            $path = '/quiz/' . $num;
        } elseif(preg_match('/^\/forum/', $temp['path'])) {
            $path = '/forum';
        } elseif(preg_match('/^\/wiki/', $temp['path'])) {
            $path = '/wiki';
        } elseif(preg_match('/^\/auth|^\/account\/signin/', $temp['path'])) {
            $path = '/auth';
        } elseif(preg_match('/^\/class\/search/', $temp['path'])) {
            $path = '/search';
        } elseif(preg_match('/^\/lecture/', $temp['path'])) {
            $path = '/lecture';
        } elseif(preg_match('/^\/quiz/', $temp['path'])) {
            $path = '/quiz';
        } else {
            $path = '/other';
        }

        return $path;
    }

    private function getSimplePath($url) {
        $url = str_replace(COURSE_URL, '', $url);
        if(empty($url)) {
            return '/';
        }

        $temp = parse_url($url);
        if(preg_match('/^\/human_grading/', $temp['path'])) {
            $path = '/human_grading';
        } elseif(preg_match('/^\/lecture/', $temp['path'])) {
            $path = '/lecture';
        } elseif(preg_match('/^\/quiz/', $temp['path'])) {
            $path = '/quiz';
        } elseif(preg_match('/^\/forum/', $temp['path'])) {
            $path = '/forum';
        } elseif(preg_match('/^\/wiki/', $temp['path'])) {
            $path = '/wiki';
        } elseif(preg_match('/^\/auth|^\/account\/signin|^\/signin/', $temp['path'])) {
            $path = '/auth';
        } elseif(preg_match('/^\/class\/search/', $temp['path'])) {
            $path = '/search';
        } else {
            $path = '/other';
        }

        return $path;
    }

    private function getSimpleFromPath($url) {
        $url = str_replace(COURSE_URL, '', $url);
        if(empty($url)) {
            return '/';
        }

        $temp = parse_url($url);
        if(isset($temp['host']) && strpos($temp['host'], 'coursera') === false) {
            return '/external';
        }

        if(preg_match('/^\/human_grading/', $temp['path'])) {
            $path = '/human_grading';
        } elseif(preg_match('/^\/lecture/', $temp['path'])) {
            $path = '/lecture';
        } elseif(preg_match('/^\/quiz/', $temp['path'])) {
            $path = '/quiz';
        } elseif(preg_match('/^\/forum/', $temp['path'])) {
            $path = '/forum';
        } elseif(preg_match('/^\/wiki/', $temp['path'])) {
            $path = '/wiki';
        } elseif(preg_match('/^\/auth|^\/account\/signin|^\/signin/', $temp['path'])) {
            $path = '/auth';
        } elseif(preg_match('/^\/class\/search/', $temp['path'])) {
            $path = '/search';
        } else {
            $path = '/other';
        }

        return $path;
    }

    private function countResources($urls) {

        $paths = array();
        $lectures = array();
        $quizes = array();
        foreach($urls as $u) {
            $temp = $this->getPath($u);

            if(preg_match('/^\/lecture/', $temp)) {
                $num = str_replace('/lecture/', '', $temp);
                $lectures[] = $num;
                $temp = '/lecture';
            } elseif(preg_match('/^\/quiz/', $temp)) {
                $num = str_replace('/quiz/', '', $temp);
                $quizes[] = $num;
                $temp = '/quiz';
            }

            if(isset($paths[$temp])) {
                $paths[$temp]++;
            } else {
                $paths[$temp] = 1;
            }
        }

        $totallectures = count(array_unique($lectures));
        $totalquizes = count(array_unique($quizes));

        return array('totallectures' => $totallectures, 'totalquizes' => $totalquizes, 'count' => $paths);
    }

    private function secondsHours($seconds) {
        $hours = floor($seconds / 3600);
        $mins = floor(($seconds - ($hours * 3600)) / 60);
        $secs = floor($seconds % 60);

        return $hours.'h, '.$mins.'min, '.$secs.'seg';
    }

    function paintGraph($graph) {
        $i = 0;
        foreach ($graph as $kg => $g) {
            foreach ($g as $kn => $n) {
                $i++;
                echo '<p><strong>' . $i . ':</strong> ' . $kg . '--' . $n . '-->' . $kn . '</p>';
            }
        }
    }

    function buildCSV($graph) {
        $name = time().'.csv';
        $headers = array(
            'Source',
            'Target',
            'Weight'
        );

        $file = fopen(DATA_CSV.$name, 'w');
        fputcsv($file, $headers, CSV_DELIMITER);

        foreach ($graph as $kg => $g) {
            foreach ($g as $kn => $n) {
                $line = array($kg, $kn, $n);
                fputcsv($file, $line, CSV_DELIMITER);
            }
        }

        fclose($file);

        return $name;
    }
}