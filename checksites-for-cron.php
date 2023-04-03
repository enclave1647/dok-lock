<?php

/* Для Cron*/
/* Для работы в отдельном файле .php */
// Подключаем API MODx
define('MODX_API_MODE', true);
// Подключаем файл index.php
require $_SERVER['DOCUMENT_ROOT'].'/index.php';
// Включаем обработку ошибок
$modx->getService('error','error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget(XPDO_CLI_MODE ? 'ECHO' : 'HTML');
/* END */

// Заполнение массива для отправки телеграмм боту
// Выполняет:
// 1. Заполение массива адресами сайтов и датами добавления в реестр РосКомНадзора для отправки телеграмм боту
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
    
    // Возвращаем заполенный адресами и датами занесения в реестр РосКомНадзора массив (для отправки боту)
    return $arrToSend;
}

// Отправка сообщений о заблокированных сайтах телеграмм боту
// Выполняет:
// 1. Отправку сообщений телеграмм боту из массива для отправки
// 2. Запись в исходный migx-массив признаков 'site_sended' и даты блокировки (site_sended - признак того, что сайт был отправлен телеграмм боту) (при успешной отправке сообщения боту)
function sendToBot(array $urls, array &$originalMIGxArray) {
    
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
    
    /* ЕСЛИ НЕТ ЭЛЕМЕНТОВ ДЛЯ ОТПРАВКИ БОТУ*/
    
    // Если входящий массив с url для отправки - пустой
    // Отправляется сообщение об успешной проверке сайтов
    if (empty($urls)) {
        
        // Устанавливаем сообщение о успешной проверке в массив параметров
        $params['text'] = 'Проверка выполнена успешно.';
        
        // Устанавливаем массив параметров в cURL
        curl_setopt($ch_bot, CURLOPT_POSTFIELDS, ($params));
        
        // Выполняем отправку боту
        $result = curl_exec($ch_bot);
        
        // Декодируем ответ для получения результата отправки
        $result = json_decode($result, true);
        
        // Закрываем соединение cURL
        curl_close($ch_bot);
        
        // Если отправка сообщения боту завершилась успешно - ВЫХОД ИЗ ФУНКЦИИ
        if ($result['ok'] == true) return;
        // Если отправка завершилась ошибкой - выводим сообщение
        else  echo '<br>(!) Ошибка отправки сообщения боту <br>';
        
    }
    
    /* ЕСЛИ ЕСТЬ ЭЛЕМЕНТЫ ДЛЯ ОТПРАВКИ БОТУ*/
    
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
    
    // Разбираем входящий массив на [сайт] => [date]
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

// Установка URL
curl_setopt($ch, CURLOPT_URL, "https://reestr.rublacklist.net/api/v3/domains/");
//curl_setopt($ch, CURLOPT_URL, "https://reestr.rublacklist.net/api/v3/snapshot/");

// Выполнение запроса cURL и получение результата
$baseRKN = curl_exec($ch);

// Закрываем сеанс cURL (все строки из migx обработаны)
curl_close($ch);

// Убираем '[', ']', ' ' и ""
$baseRKN = str_replace(array('[',']','"',' '), '', $baseRKN);

// Пробразум строку в массив по разделителю - ','
$ArrBaseRkn = explode(',', $baseRKN);


// Проходим по списку url из migx-поля (для поиска URL в базе РКН)
foreach($migxArr as $migxItem) {

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

    