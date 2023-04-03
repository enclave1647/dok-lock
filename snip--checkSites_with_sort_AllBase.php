<?php

/*
* Проверка сайтов из migx-поля на вхождение в черный список Роскомнадзора с JS сортировкой. 
* Перед проверкой сайтов скачивается вся база РКН.
*/


echo '
<style>

.container {
    margin: 0 auto;
    max-width: 756px;
}

.table-title {
    text-align: center;
    font-size: 25px;
    font-weight: 500;
    font-family: monospace;
    margin-top: 76px;
    margin-left: auto;
    margin-right: auto;
    max-width: 416px;
    transition: all 0.5s;
}

h2#project-title:hover {
    cursor: pointer;
    font-size: 26px;
    transition: all 0.5s ease;
}

.rkn-table {
    font-size: 16px;
    border-spacing: 0;
    text-align: center;
    margin: 0 auto;
    font-family: monospace;
}

tr, td, th {
    border: 1px solid #000;
}

.rkn-table__row.rkn-table__row_head {
    height: 35px;
}

tr.rkn-table__row.rkn-table__row_head th {
    background-color: #ececec;
    transition: all .7s ease;
}

.rkn-table__row.rkn-table__row_head th:hover {
    background-color: #cacaca;
    transition: all .7s ease;
    cursor: pointer;
}

tr.rkn-table__row.rkn-table__row_head #project-number {
    width: 126px;
}

.rkn-table__row {
    height: 37px;
    position: relative;
    transition: all .5s ease;
}

tr.rkn-table__row.rkn-table__row_good {
    background-color: #d9f5d9;
}

tr.rkn-table__row.rkn-table__row_good_last {
    background-color: #a3d1a3;
}

tr.rkn-table__row.rkn-table__row_bad {
    background-color: #ecddd6;
}

tr.rkn-table__row.rkn-table__row_bad_last {
    background-color: #b99585;
}

tr.rkn-table__row.rkn-table__row_bad_last:hover, tr.rkn-table__row.rkn-table__row_good_last:hover {
    cursor: pointer;
    background-color: #b5b2b2;
    transition: all .5s ease;
}

.rkn-table__first-column {
    width: 180px;
}

.rkn-table__second-column {
    width: 140px;
}

.rkn-table__third-column {
    width: 72px;
}

img.rkn-table__img {
    width: 23px;
    margin-top: 4px;
}

tr.rkn-table__row.rkn-table__row_good_last.unique:hover {
    cursor: auto;
    background-color: #a3d1a3;
}

tr.rkn-table__row.rkn-table__row_bad_last.unique:hover {
    cursor: auto; 
    background-color: #b99585;
}


.rkn-table__row.rkn-table__row_bad_last.close  .rkn-table__zero-column.unique:before,
.rkn-table__row.rkn-table__row_good_last.close  .rkn-table__zero-column.unique:before
{
    content: '."''".';
}

.rkn-table__row.rkn-table__row_bad_last .rkn-table__zero-column:before,
.rkn-table__row.rkn-table__row_good_last .rkn-table__zero-column:before{
    position: absolute;
    top: 1px;
    left: 12px;
    font-size: 20px;  
}
.rkn-table__row.rkn-table__row_bad_last.close .rkn-table__zero-column:before,
.rkn-table__row.rkn-table__row_good_last.close .rkn-table__zero-column:before{
    content: '."'+'".';
}

.rkn-table__row.rkn-table__row_bad_last.open .rkn-table__zero-column:before,
.rkn-table__row.rkn-table__row_good_last.open .rkn-table__zero-column:before{
    content: '."'-'".';
}

</style>';

// Отрисовка таблицы из массива данных (после ответа от API РосКомНадзора или сотритовки) 
// Выполняет:
// 1. Отрисовку таблицы
function renderTable(array $dataArray, array $arrLastElements = []) {
   
    echo '<div class="container">';
    echo '<h2 id="project-title" class="table-title">Реестр сайтов в РосКомНадзоре</h2>';
    echo '<table class="rkn-table">';
    echo '<tr class="rkn-table__row rkn-table__row_head"><th id="project-number">Номер проекта</th><th id="project-url">URL (Сайт)</th><th id="project-date">Дата попадания</th><th id="project-status">Статус</th></tr>';

    // Итератор цикла
    $i = 0;
    
    // Перебор входящего массива данных
    foreach ($dataArray as $dataItem) {
    
    $modifierLastElement = '';
    
    // Если в ответе от РосКом существует дата занесения в реестр, то сайт блокирован
    if ($dataItem['date']) {
        
        // Получаем признак последнего элемента в проекте
        $modifierLastElement = getModifierLastElement($dataItem['url'], $arrLastElements);

        echo '<tr class="rkn-table__row rkn-table__row_bad'.$modifierLastElement.'" data-filter="rnk">
                <td class="rkn-table__zero-column">'.$dataItem['name'].'</td>
                <td class="rkn-table__first-column">'.$dataItem['url'].'</td>'
                .'<td class="rkn-table__second-column">'.$dataItem['date'].'</td>'
                .'<td class="rkn-table__third-column"><img class="rkn-table__img" src="/assets/img/bad.png"></td>'
            .'</tr>';

    } else {
        
        // Получаем признак последнего элемента в проекте
        $modifierLastElement = getModifierLastElement($dataItem['url'], $arrLastElements);
        
        echo '<tr class="rkn-table__row rkn-table__row_good'.$modifierLastElement.'" data-filter="notRkn">
                <td class="rkn-table__zero-column">'.$dataItem['name'].'</td>
                <td class="rkn-table__first-column">'.$dataItem['url'].'</td>'
                .'<td class="rkn-table__second-column"></td>'
                .'<td class="rkn-table__third-column"><img class="rkn-table__img" src="/assets/img/good.png"></td">'
            .'</tr>';
        }
        
        $i++;
            
    }
    
    echo '</table>';
    echo '</div>';
}

// Заполнение массива для отправки телеграмм боту
// Выполняет:
// 1. Заполение массива адресами сайтов и датами добавления в реестр РосКомНадзора для отправки телеграмм боту
// Возвращает:
// (array) - заполенный адресами и датами занесения в реестр РосКомНадзора массив
function fillArraySendToBot(array $dataArray) {
     
    // Возвращаемый массив для отправки боту
    $arrToSend = [];
     
    // Итератор цикла
    $i = 0;
    
    // Перебор входящего массива данных
    foreach ($dataArray as $dataItem) {
        
        // Если в ответе от РосКомНадзора существует дата занесения в реестр, то сайт блокирован
        if ($dataItem['date']) {
            
            // Если о блокировке текущего сайта не уведомлено 
            if ($dataItem['sended'] == 0) {
                
                // Добавляем сайт в массив для отправки боту
                $arrToSend[$dataItem['url']] = $dataItem['date'];
                
            }
        }
        
        $i++;
    }
    
    // Возвращаем (пустой или) заполенный адресами и датами занесения в реестр РосКомНадзора массив (для отправки боту)
    return $arrToSend;
}

// Отправка сообщений о заблокированных сайтах телеграмм боту
// Выполняет:
// 1. Отправку сообщений телеграмм боту из массива для отправки
// 2. Запись в исходный migx-массив признаков 'site_sended' и даты блокировки (site_sended - признак того, что сайт был отправлен телеграмм боту) (при успешной отправке сообщения боту)
// 3. При ошибке отправки сообщения боту - выводит список неотправленных URL на экран
function sendToBot(array $urls, array &$originalMIGxArray) {
    
    /* ЕСЛИ НЕТ ЭЛЕМЕНТОВ ДЛЯ ОТПРАВКИ БОТУ*/
    
    // Если входящий массив с url для отправки пустой - ВЫХОД ИЗ ФУНКЦИИ ОТПРАВКИ БОТУ
    if (empty($urls)) return;
    
    /* ЕСЛИ ЕСТЬ ЭЛЕМЕНТЫ ДЛЯ ОТПРАВКИ БОТУ*/
    
    $token = "6186163698:AAHnrj43GDtZJozTXA7zdO8NjrW0YRuclG0"; // Токен бота
    $chat_id = "-1001627447977"; // ID чата, куда будут приходить сообщения
    
    // Отправляем сообщение посредством cURL
    $website = "https://api.telegram.org/bot".$token;
    $params = [
        'chat_id' => $chat_id,
        'parse_mode' => 'html',
        'text' => '',
    ];
    
    // Инициализация
    $ch_bot = curl_init($website . '/sendMessage');
    
    // Устанавливаем параметры cURL
    curl_setopt($ch_bot, CURLOPT_HEADER, false);
    curl_setopt($ch_bot, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch_bot, CURLOPT_POST, 1);
    curl_setopt($ch_bot, CURLOPT_SSL_VERIFYPEER, false);
    
    
    // Разбираем входящий массив на [сайт] => [date] для заполнения сообщения боту
    foreach($urls as $url => $date) {
        // Заполняем сообщение
        $messageToBot .= "Блокировка сайта: ".$url." - ".$date."\r\n";
    }
    
    // Устанавливаем заполненное сообщение в массив параметров
    $params['text'] = $messageToBot;
    
    // Устанавливаем массив параметров в cURL
    curl_setopt($ch_bot, CURLOPT_POSTFIELDS, ($params));
    
    // Выполняем отправку боту
    $result = curl_exec($ch_bot);
    
    // Декодируем ответ для получения результата отправки
    $result = json_decode($result, true);
    
    // Разбираем входящий массив на [сайт] => [date] для проставления признака 'site_sended' и даты занесения в реестр (берем текущую дату)
    foreach($urls as $url => $date) {
        
        // Итератор внутреннего цикла
        $i = 0;
        
        // Поиск отправленных адресов в общем migx-массиве для установки признака отправки
        foreach ($originalMIGxArray as $key => $migxItem) {
            
            // Если для сайта из массива отправки не установлен признак 'site_sended' и отправка боту выполнена успешна
            if ($url == $originalMIGxArray[$key]['site_url'] && $originalMIGxArray[$key]['site_sended'] == 0 && $result['ok'] == true)
            {
                // Устанавливаем признак уведомления в исходный массив migx значений
                $originalMIGxArray[$i]['site_sended'] = 1;
                // Устанавливаем дату блокировки в исходный массив migx значений
                $originalMIGxArray[$i]['site_date'] = $date;
            }
            
            $i++;
        }
        
        // Если отправки завершилась ошибкой - выводим сообщение
        if ($result['ok'] == false) echo '<br>(!) Ошибка отправки данных о сайте -> '.$url.' боту<br>';
    }
    
    // Закрываем соединение cURL
    curl_close($ch_bot);
}

// Сортировка массива по GET параметру
// Выполняет:
// 1. Сортировку переданного массива и возвращает отсортированный массив
// Возвращает:
// (array) - сортированый массив
function sortArray(array $inputArray, string $sortView) {
    
    // Массив данных после сортировки
    $outputArray = array(array());
    
    // Получаем массивы столбцов из входящего массива (для сортировки)
    $arrSiteName  = array_column($inputArray, 'name');
    $arrSiteUrl = array_column($inputArray, 'url');
    $arrSiteDate = array_column($inputArray, 'date');
    $arrSiteBlocked = array_column($inputArray, 'isBlocked');
    
    // Переводим дату из строки в число секунд (от 1970г)
    foreach($arrSiteDate as $key => $item) {
        $arrSiteDate[$key] = strtotime($item);
    }
    
    // Сортируем массивы столбцов между собой в зависимости от параметра (изменяется каждый переданный массив)
    // array_multisort() - сортирует по столбцам (передаются все массивы столбцов для привязки значений в пределах одной строки вывода)
    switch ($sortView) {
        case 'project_name':
            array_multisort($arrSiteName, SORT_ASC, $arrSiteDate, SORT_ASC, $arrSiteUrl, SORT_ASC, $arrSiteBlocked, SORT_ASC);
            break;
        case 'project_url':
            array_multisort($arrSiteUrl, SORT_ASC, $arrSiteDate, SORT_ASC, $arrSiteName, SORT_ASC, $arrSiteBlocked, SORT_ASC);
            break;
        case 'project_date':
            array_multisort($arrSiteDate, SORT_DESC, $arrSiteName, SORT_ASC, $arrSiteUrl, SORT_ASC, $arrSiteBlocked, SORT_ASC);
            break;
        case 'project_blocked':
            array_multisort($arrSiteBlocked, SORT_ASC, $arrSiteDate, SORT_ASC, $arrSiteName, SORT_ASC, $arrSiteUrl, SORT_ASC);
            break;
        case 'origin':
            // Без сортировки (чтобы быстро (без API) вернуть вид как в migx-поле)
            break;
    }

    // Переводим дату обратно из числа секунд (от 1970г) в строку
    foreach($arrSiteDate as $key => $date) {
        // Если есть дата
        if ($date) $arrSiteDate[$key] = date('d.m.y', $date);
    }
    
    // Собираем общий массив строк из столбцов для отрисовки (в renderTable)
    for ($i = 0; $i < count($inputArray); $i++) {
        $outputArray[$i]['name'] = $arrSiteName[$i];
        $outputArray[$i]['url'] = $arrSiteUrl[$i];
        $outputArray[$i]['date'] = $arrSiteDate[$i];
        $outputArray[$i]['isBlocked'] = $arrSiteBlocked[$i];
    }
    
    // Передаем сортированый массив обратно (для отображения в renderTable())
    return $outputArray;
}

// Присвоение признаков последних элементов проекта (для вывода на экран)
// Выполняет:
// 1. Поиск URL текущего элемента в массиве последних элементов проекта
// Возвращает:
// (string) - признак последнего элемента
function getModifierLastElement($currentUrl, array $arrLastElements) {
    // Если массив последних элементов пуст
    // Возвращаем пустою строку для модификатора
    if (empty($arrLastElements))
        return '';
    else {
        // Ищем текущий элемент вывода в массиве последних элементов проекта
        $keyLastElements = array_search($currentUrl, $arrLastElements);
        
        // Если для текущего элемента найден ключ в массиве последних элементов проекта -
        // возвращаем признак поседнего элемента (для rkn-table__row_bad_last или rkn-table__row_good_last)
        if ($keyLastElements) return '_last';
    }
}


// Получаем ресурс с migx-полем сайтов
$page = $modx->getObject('modResource', 1);
// Берем migx-поле
$sites = $page->getTVValue('migx_sites_url');
// Декодируем migx
$migxArr = json_decode($sites, true);
// Инициализация cURL 
// Метод GET по-умолчанию
$ch = curl_init();
// Возврат результата в качестве строки
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Отключение заголовков в выводе
curl_setopt($ch, CURLOPT_HEADER, 0); 
// Ответ API РосКомНадзора
$result = '';
// Массив данных после ответа от API РосКомНадзора
$dataArrayAfterAPI = [];

/* ДЛЯ СОРТИРОВКИ */
// Массив данных из migx-поля без обращения к API РосКомНадзора (для вывода сортировки) (для скорости загрузки страницы)
$dataArrayWithoutAPI = [];
// Принимаем вид сортировки
$sortView = trim($_GET['sort']);
// Массив данных после сортировки
$sortedArray = array(array());

/* ДЛЯ ПОДСТВЕТКИ ПОСЛЕДНЕГО ЭЛЕМЕНТА В ПРОЕКТЕ */
// Массив последник элементов в проекте
$arrLastElem = [];
// Переменные для сохранения предыдущих элементов migx-поля
$tempNumberProj = '';
$tempUrl = '';
// Итератор
$iteratorForLastElem = 0;
/* END */


// Установка URL
curl_setopt($ch, CURLOPT_URL, "https://reestr.rublacklist.net/api/v3/domains/");
//curl_setopt($ch, CURLOPT_URL, "https://reestr.rublacklist.net/api/v3/snapshot/");
    
// Выполнение запроса cURL и получение результата
$baseRKN = curl_exec($ch);

// Закрываем сеанс cURL (все строки из migx обработаны)
curl_close();

// Убираем '[', ']', ' ' и ""
$baseRKN = str_replace(array('[',']','"',' '), '', $baseRKN);

// Пробразум строку в массив по разделителю - ','
$ArrBaseRkn = explode(',', $baseRKN);


// Проходим по списку url из migx-поля (для поиска URL в базе РКН)
foreach($migxArr as $migxItem) {

    // Если передан GET-параметр для сортировки
    // тогда (для оптимизации скорости загрузки страницы) выводим данные из migx-поля
    // без обращения к API
    if($sortView) {
        
        // Если есть дата блокировки (из migx-поля), то проставляем статус - блокирован
        if($migxItem['site_date']) $isBlocked = 1;
        else $isBlocked = 0;
        
        // Собираем все данные из migx-поля без обращения к API РосКомНадзора
        $dataArrayWithoutAPI[] = array(  
                                    'name' =>    $migxItem['site_name'],
                                    'url' =>    $migxItem['site_url'],
                                    'sended' => $migxItem['site_sended'],
                                    'date' =>    $migxItem['site_date'],
                                    'isBlocked' => $isBlocked,
                        );
    }

    // Если нет GET-параметра для сортировки - обращаемся к базе РКН
    else {
        
        // Для следующего элемента сбрасываем дату новой блокировки
        $dateBlocked = 0;
        
        // Сверяем только сайты, которые не были отправлены боту (не блокированные)
        if (!$migxItem['site_sended']) {
            
            // Ищем текущий URL в массиве адрессов РКН (получаем ключ)
            $keyInArrRKN = array_search($migxItem['site_url'], $ArrBaseRkn);    
            
            // ЕСЛИ КЛЮЧ НАЙДЕН - САЙТ БЛОКИРОВАН
            if($keyInArrRKN != false) {
                // Ставим признак блокировки
                $isBlocked = 1;
                // Дата блокировки - текущая дата
                $dateBlocked = date('d.m.Y');
            }
        }
        
        // Если появилась дата новой блокировки
        if($dateBlocked) $dateToArr = $dateBlocked;
        // Иначе берем дату из migx-поля
        else $dateToArr = $migxItem['site_date'];
        
        // Собираем все данные в массив после ответа от API РосКомНадзора
        $dataArrayAfterAPI[] = array(  
                                        'name' =>    $migxItem['site_name'],
                                        'url' =>    $migxItem['site_url'],
                                        'sended' => $migxItem['site_sended'],
                                        'date' =>    $dateToArr,
                                        'isBlocked' => $isBlocked,
                            );
    }
    
    /* ВЫБОРКА ПОСЛЕДНИХ ДОБАВЛЕННЫХ ЭЛЕМЕНТОВ В ПРОЕКТЕ */
    
    // Если текущий элемент не первый в migx и № Проекта не совпадает с последующим  
    if ($tempNumberProj != '' && $migxItem['site_name'] != $tempNumberProj) {
        // Значит, это последний элемент в проекте - добавляем в массив
        $arrLastElem[$tempNumberProj] = $tempUrl;
    }
    
    // Сохраняем данные предыдущего элемента
    $tempNumberProj = $migxItem['site_name'];
    $tempUrl = $migxItem['site_url'];
    
    // Счетчик элементов migx-поля
    $iteratorForLastElem++;
    
    // Если последний элемент в migx - добавляем в массив
    if (count($migxArr) == $iteratorForLastElem) $arrLastElem[$tempNumberProj] = $tempUrl;
    
    /* END */
    
}

    
// Если передан GET-параметр для сортировки
if ($sortView) {
    // Выполняем сортировку  массива (только по значениям из migx, без API)
    $sortedArray = sortArray($dataArrayWithoutAPI, $sortView);
    // Отрисовываем таблицу из массива после сортировки
    renderTable($sortedArray);
} else {
    // Иначе
    // Отрисовываем таблицу из массива
    renderTable($dataArrayAfterAPI, $arrLastElem);
    
    // Заполняем массив для отправки боту
    // Возвращает массив заполненый адрессами сайтов и датами добавления в реест РосКомНадзора
    $sitesToSend = fillArraySendToBot($dataArrayAfterAPI);
    
    // Чистим кэш
    $modx->cacheManager->clearCache();
    
    // Отправляем сообщения телеграмм боту и заполняем исходный migx-массив признаками 'site_sended' и датами блокировки
    // Параметр 1: массив адресов сайтов и дат блокировок РосКомНадзором 
    // Параметр 2: исходный массив migx значений (передаётся по ссылке, т.е. функция меняет входящий массив, а не его копию)
    sendToBot($sitesToSend, $migxArr);
    
    // Сохраняем заполненый параметрами 'site_sended' массив обратно в migx-поле
    $page->setTVValue('migx_sites_url', json_encode($migxArr));
    $page->save();
    $modx->cacheManager->clearCache();
}

// TEST
/*foreach($arrLastElem as $key => $value) {
    echo $key.' --> '.$value.'<br>';
}*/