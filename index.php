<?php
error_reporting(0);

//Генерируем картинку с версией базы nod32
Include('ess/gif/update3.php');

/**
 * Навигация по папкам
 * Переменная
 * $dir_size=0 - не считаем размер папок(ускоряет работу скрипта)
 * $dir_size=1 - считаем размер папок(может медленно работать при большом кол-ве фалов или на медленных хостах)
 * Скрытие папок неполное =)) кто догадается в чем дело тот молодец =)
 * @author Щербаков Иван <a class="astyle"ntilamer87@mail.ru>
 * @link http://www.alamer.ru
 * @version
 * @package
 */
/////////////////////////////////////////////////////////////////////////////////
/////////////////////Секция настроек!!!!!!!!!!!!!!///////////////////////////////
/////////////////////////////////////////////////////////////////////////////////
/**
 * Отвечает за подсчет размера папок
 * @global
 * @name
 */
$dir_size = 1;

/**
 * Отвечает за фильтр (какие элементы не будут отображаться)
 *  Пример ".php" уберет все php файлы
 *  "/dirname" скроет все папки с именем "dirname"
 *  "filename.php" скроет все файлы с именем "filename.php"
 * @global
 * @name
 */
$mask = array(".php", ".html", "/css", "/js", "/img", "/Joomla", "/phpMyAdmin", "/crontab", "/ess", "/CI", "/eset_upd", "/rtgui", "/wTorrent");

function public_base_directory() {
    //get public directory structure eg "/top/second/third" 
    $public_directory = dirname($_SERVER['PHP_SELF']);
    //place each directory into array 
    $directory_array = explode('/', $public_directory);
    //get highest or top level in array of directory strings 
    $public_base = max($directory_array);
    if ($public_base === "\\" || $public_base === "/") {
        //echo $public_base;
        return "";
    }

    return "/" . $public_base;
}

/////////////////////////////////////////////////////////////////////////////////
/////////////////////Секция настроек!!!!!!!!!!!!!!///////////////////////////////
/////////////////////////////////////////////////////////////////////////////////

/**
 * This is a shorter (but not faster) code for the getsize function, will work for files and folders.
 *
 * @global
 * @staticvar
 * @param
 * @return
 */
function getsize($path) {
    if (!is_dir($path))
        return filesize($path);
    $size = 0;
    foreach (scandir($path) as $file) {
        if ($file == '.' or $file == '..')
            continue;
        $size+=getsize($path . '/' . $file);
    }
    return $size;
}

/**
 * КОнвертация байтов в удобоваримую форму. Честно спизжено с Php.net
 *
 * @global
 * @staticvar
 * @param
 * @return
 */
function byteConvert($bytes) {
    if ($bytes > 0) {
        $s = array('Б', 'Кб', 'Мб', 'Гб', 'Тб', 'Пб');
        $e = floor(log($bytes) / log(1024));

        return sprintf('%.2f ' . $s[$e], ($bytes / pow(1024, floor($e))));
    } else
        return '0';
}

//Считываем текущее время
$mtime = microtime();
//Разделяем секунды и миллисекунды
$mtime = explode(" ", $mtime);
//Составляем одно число из секунд и миллисекунд
$mtime = $mtime[1] + $mtime[0];
//Записываем стартовое время в переменную
$tstart = $mtime;
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>WEB File Explorer by alamer</title>
        <link rel="stylesheet" href="css/dir.css">
            <link rel="stylesheet" href="css/jquery-ui-1.10.4.custom.min.css">
                <link rel="stylesheet" href="css/jquery-ui.structure.css">
                    <script src="js/jquery.min.js"></script>
                    <script src="js/jquery-ui.min.js"></script>
                    <script src="js/jquery.cookie.js"></script>
                    <script src="js/SimpleAjaxUploader.min.js"></script>
                    <script src="js/dir.js"></script>
                    </head>

                    <body class="bodystyle">
                        <span id="loginShow">Войти</span>
                        <span id="loginHide">Выйти</span>
                        <div id="login">
                            <input type="text" id="username" />
                            <input type="password" id="password" />
                            <div id="authAction">Войти</div>
                        </div>
                        <div id="forauth">
                            <div id="createdir">Создать папку</div>
                            <input type="button" id="upload-btn"  value="Choose file">
                        </div>

                        <div id="dialog" title="Переименование">
                            <input type="text" id="olditem" disabled="true" />
                            <input type="text" id="newitem" />
                        </div>
                        <div id="dialogdir" title="Создать папку">
                            <input type="text" id="newdir" />
                        </div>         
                        <div id="dialogload" title="Basic dialog">
                            <p>Идет загрузка файла. Ждите</p>

                        </div>                        

                        <?php
// шапка таблицы
                        echo '<table class="tablestyle" cellpadding="2">';
                        $base_dir = getcwd(); // ФАйл лежит в корне поэтому это будет корневой каталог
// Если не передаем параметр(выдаем содержимое корня)
                        if (!isset($_GET['fold'])) {
                            $strok = 0;
                            // Заголовок
                            echo '<tr>';
                            echo '<th class="w1"><img alt="" src="img/logo.gif" /></th>';
                            echo '<th class="w2">Файл / Папка</th>';
                            echo '<th class="w3">Размер</th>';
                            echo '<th class="w4">Дата изменения</th>';
                            echo '<th class="w5">Удалить</th>';
                            echo '<th class="w6">Переименовать</th>';
                            echo '</tr>';
                            // Получаем список фалйов и папок в корне
                            $files = scandir($base_dir);
                            usort($files, create_function('$a,$b', '
	return	is_dir ($a)
		? (is_dir ($b) ? strnatcasecmp ($a, $b) : -1)
		: (is_dir ($b) ? 1 : (
			strcasecmp (pathinfo ($a, PATHINFO_EXTENSION), pathinfo ($b, PATHINFO_EXTENSION)) == 0
			? strnatcasecmp ($a, $b)
			: strcasecmp (pathinfo ($a, PATHINFO_EXTENSION), pathinfo ($b, PATHINFO_EXTENSION))
		))	;'));
                            //echo $base_dir;
                            // Выдаем список
                            for ($i = 2; $i < count($files); $i++) {
                                // Каждый файл или папка новая строка в таблице
                                // Применяем фильтр по расширениям файлов
                                $path_parts = pathinfo($base_dir . '/' . $files[$i]);
                                //echo $base_dir.'/'.$files[$i].'<br>';
                                // если папка то выводим так
                                if (is_dir($base_dir . '/' . $files[$i])) {
                                    // Фильтр по именам папок
                                    if (array_search('/' . $files[$i], $mask) === FALSE) {
                                        $strok++;
                                        // красим строки
                                        if ($strok % 2 == 0) {
                                            echo '<tr class="s1">';
                                        } else {
                                            echo '<tr class="s2">';
                                        }
                                        echo '<td><img alt="" src="img/dir.gif" /></td>';
                                        echo '<td class="w2"><a class="astyle" href="' . '?fold=' . str_replace(' ', '%20', '/' . $files[$i]) . '">' . $files[$i] . '/</a></td>';
                                        switch ($dir_size) {
                                            case 0:
                                                echo '<td class="w3">Папка</td>';
                                                break;
                                            case 1:
                                                $size = getsize($base_dir . '/' . $files[$i]);
                                                echo '<td class="w3">' . byteconvert($size) . '</td>';
                                                break;
                                        }
                                        echo '<td>' . date("d.m.Y H:i:s", filemtime($base_dir . '/' . $files[$i])) . '</td>';
                                        echo '<td class="remove"><div id="remove">Удалить</div></td>';
                                        echo '<td class="rename"><div id="rename">Переименовать</div></td>';

                                        // закрываем строку
                                        echo '</tr>';
                                    }
                                } else {
                                    // Фильтр по расширениям
                                    if (array_search('.' . $path_parts['extension'], $mask) === FALSE) {
                                        // Фильтр по именам
                                        if (array_search($path_parts['basename'], $mask) === FALSE) {
                                            $strok++;
                                            // красим строки
                                            if ($strok % 2 == 0) {
                                                echo '<tr class="s1">';
                                            } else {
                                                echo '<tr class="s2">';
                                            }
                                            echo '<td><img alt="" src="img/file.gif" /></td>';
                                            echo '<td class="w2"><a class="astyle" href="' . public_base_directory() . str_replace(' ', '%20', $_GET['fold'] . '/' . $files[$i]) . '">' . $files[$i] . '</a></td>';
                                            $size = getsize($base_dir . '/' . $files[$i]);
                                            echo '<td class="w3">' . byteconvert($size) . '</td>';
                                            echo '<td>' . date("d.m.Y H:i:s", filemtime($base_dir . '/' . $files[$i])) . '</td>';
                                            echo '<td class="remove"><div id="remove">Удалить</div></td>';
                                            echo '<td class="rename"><div id="rename">Переименовать</div></td>';
                                            // закрываем строку
                                            echo '</tr>';
                                        }
                                    }
                                }
                            }
                        }
// если передавали параметр то
                        else {
                            $strok = 0;
                            $fold = $_GET['fold'];
                            // делаем ссылку на родительский каталог
                            $n = strlen($fold);
                            $pos = 0;
                            for ($i = $n - 1; $i >= 0; $i--) {
                                if ($fold[$i] == '/') {
                                    $pos = $i;
                                    break;
                                }
                            }
                            $foldup = substr($fold, 0, $pos);
                            $strok++;
                            if ($fold !== '') {
                                echo '<tr>';
                                echo '<th class="w1"><img alt="" src="img/up.gif" /></th>';
                                echo '<th class="w2"><a class="astyle" href="' . '?fold=' . str_replace(' ', '%20', $foldup) . '">' . 'Родительский каталог' . '/' . '</a></th>';
                                echo '<th class="w3">Размер</th>';
                                echo '<th class="w4">Дата изменения</th>';
                                echo '<th class="w5">Удалить</th>';
                                echo '<th class="w6">Переименовать</th>';
                                echo '</tr>';
                            } else {
                                // Заголовок
                                echo '<tr>';
                                echo '<th class="w1"><img alt="" src="img/logo.gif" /></th>';
                                echo '<th class="w2">Файл / Папка</th>';
                                echo '<th class="w3">Размер</th>';
                                echo '<th class="w4">Дата изменения</th>';
                                echo '<th class="w5">Удалить</th>';
                                echo '<th class="w6">Переименовать</th>';
                                echo '</tr>';
                            }
                            // Получаем список фалйов и папок в корне
                            $files = scandir($base_dir . $fold);
                            usort($files, create_function('$a,$b', '
	return	is_dir ($a)
		? (is_dir ($b) ? strnatcasecmp ($a, $b) : -1)
		: (is_dir ($b) ? 1 : (
			strcasecmp (pathinfo ($a, PATHINFO_EXTENSION), pathinfo ($b, PATHINFO_EXTENSION)) == 0
			? strnatcasecmp ($a, $b)
			: strcasecmp (pathinfo ($a, PATHINFO_EXTENSION), pathinfo ($b, PATHINFO_EXTENSION))
		))	;'));
                            // Выдаем список
                            for ($i = 2; $i < count($files); $i++) {

                                // Применяем фильтр по расширениям файлов
                                $path_parts = pathinfo($base_dir . $fold . '/' . $files[$i]);
                                // если папка то выводим так
                                if (is_dir($base_dir . $fold . '/' . $files[$i])) {
                                    // Фильтр по именам папок
                                    if (array_search('/' . $files[$i], $mask) === FALSE) {
                                        $strok++;
                                        // красим строки
                                        if ($strok % 2 == 0) {
                                            echo '<tr class="s1">';
                                        } else {
                                            echo '<tr class="s2">';
                                        }
                                        echo '<td><img alt="" src="img/dir.gif" /></td>';
                                        echo '<td class="w2"><a class="astyle" href="' . '?fold=' . str_replace(' ', '%20', $fold . '/' . $files[$i]) . '">' . $files[$i] . '/' . '</a></td>';
                                        switch ($dir_size) {
                                            case 0:
                                                echo '<td class="w3">Папка</td>';
                                                break;
                                            case 1:
                                                $size = getsize($base_dir . $fold . '/' . $files[$i]);
                                                echo '<td class="w3">' . byteconvert($size) . '</td>';
                                                break;
                                        }
                                        echo '<td>' . date("d.m.Y H:i:s", filemtime($base_dir . $fold . '/' . $files[$i])) . '</td>';
                                        echo '<td class="remove"><div id="remove">Удалить</div></td>';
                                        echo '<td class="rename"><div id="rename">Переименовать</div></td>';
                                        echo '</tr>';
                                    }
                                } else {
                                    // Фильтр по расширениям
                                    if (array_search('.' . $path_parts['extension'], $mask) === FALSE) {
                                        // Фильтр по именам
                                        if (array_search($path_parts['basename'], $mask) === FALSE) {
                                            $strok++;
                                            // красим строки
                                            if ($strok % 2 == 0) {
                                                echo '<tr class="s1">';
                                            } else {
                                                echo '<tr class="s2">';
                                            }
                                            echo '<td><img alt="" src="img/file.gif" /></td>';
                                            echo '<td class="w2"><a class="astyle" href="' . public_base_directory() . str_replace(' ', '%20', $fold . '/' . $files[$i]) . '">' . $files[$i] . '</a></td>';
                                            $size = getsize($base_dir . $fold . '/' . $files[$i]);
                                            echo '<td class="w3">' . byteconvert($size) . '</td>';
                                            echo '<td>' . date("d.m.Y H:i:s", filemtime($base_dir . $fold . '/' . $files[$i])) . '</td>';
                                            echo '<td class="remove"><div id="remove">Удалить</div></td>';
                                            echo '<td class="rename"><div id="rename">Переименовать</div></td>';
                                            echo '</tr>';
                                        }
                                    }
                                }
                            }
                        }
// закрываем таблицу
                        echo '</table>';


//Делаем все то же самое, чтобы получить текущее время
                        $mtime = microtime();
                        $mtime = explode(" ", $mtime);
                        $mtime = $mtime[1] + $mtime[0];
//Записываем время окончания в другую переменную
                        $tend = $mtime;
//Вычисляем разницу
                        $totaltime = ($tend - $tstart);
//Выводим не экран
                        echo '<br /><div class="ess"><img style="vertical-align: middle; margin-right: 15px" alt="" src="ess/ess.gif" />';
                        printf("Страница сгенерирована за %f секунд ", $totaltime);
                        echo '</div>';
                        ?>
                        <div id="pic-progress-wrap" class="progress-wrap" style="margin-top:10px;margin-bottom:10px;"></div>
                    </body>
                    </html>