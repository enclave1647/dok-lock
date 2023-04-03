document.addEventListener("DOMContentLoaded", () =>{
    
    let clmNumber = document.getElementById('project-number');
    let clmUrl = document.getElementById('project-url');
    let clmDate = document.getElementById('project-date');
    let clmStatus = document.getElementById('project-status');
    let titleH2 = document.getElementById('project-title');

    // При клике на заголовки столбцов, переходим по ссылкам
    
    clmNumber.onclick = () => window.location.replace('https://dok-lock.top?sort=project_name');

    clmUrl.onclick = () => window.location.replace('https://dok-lock.top?sort=project_url');
    
    clmDate.onclick = () => window.location.replace('https://dok-lock.top?sort=project_date');
    
    clmStatus.onclick = () => window.location.replace('https://dok-lock.top?sort=project_blocked');
    
    titleH2.onclick = () => window.location.replace('https://dok-lock.top');
    
    // Для главной страницы
    if (window.location.href == 'https://dok-lock.top/') {
    
        /**
         * Скрытие строк таблицы по проектам
        */
        
        // Процедура
        // Показать скрытые строки
        let showRows = (e, currentRow, countToShow) => {
            //console.log(`Текущая строка - ${currentRow}`);
            //console.log(`Строк для показа - ${countToShow}`);
            e.currentTarget.classList.remove('close');
            e.currentTarget.classList.add('open');
            for (let i = currentRow - countToShow; i < currentRow; i++) {
                allRows[i].hidden = false;
                allRows[i].classList.remove('hide');
                console.log(`Отобразится строка - ${i}`);
            }
            
        }
        
        // Процедура
        // Скрыть показанные строки
        let hiddenRows = (e, currentRow, countToShow) => {
            //console.log(`Текущая строка - ${currentRow}`);
            //console.log(`Строк для показа - ${countToShow}`);
            e.currentTarget.classList.remove('open');
            e.currentTarget.classList.add('close');
            for (let i = currentRow - countToShow; i < currentRow; i++) {
                allRows[i].hidden = true;
                allRows[i].classList.add('hide');
                console.log(`Скроется строка - ${i}`);
            }
            
        }
        
        // Процедура
        // Переключить режим видимости
        let switchVisibleRow = (e) => {
            console.log('Click');
            let idxOfClickedRow = arrRows.indexOf(e.currentTarget) + 1;
            let countHiddenRow = arrOfHiddens.get(idxOfClickedRow);
            // Если у данной строки могут быть скрытые строки И класс 'close' - показать
            if (countHiddenRow && e.currentTarget.classList.contains('close')) showRows(e, idxOfClickedRow-1, countHiddenRow);
            else /*(countHiddenRow && e.currentTarget.classList.contains('open'))*/ hiddenRows(e, idxOfClickedRow-1, countHiddenRow);
        };
        
        /* START */
        
        let table = document.querySelector('.rkn-table');
        let allRows = document.querySelectorAll('.rkn-table__row:not(.rkn-table__row_head)');
        
        // Из NodeList в Array (для определения индекса нажатой строки)
        let arrRows = Array.prototype.slice.call(allRows);
        
        let countHiddens = 0;
        let iterator = 1;
        
        const arrOfHiddens = new Map();
        
        /* Скрытие */
        for(let row of allRows) {
            
            if (row.classList.contains('rkn-table__row_good') || row.classList.contains('rkn-table__row_bad')) {
                
                row.classList.add('hide');
                row.hidden = true;
                countHiddens++;
            }
            else {
                
                if (countHiddens !== 0) { 
                    // Обработчик только для строк с _last (не для тех, которые в IF)
                    // и у которых есть скрытые элементы
                    row.onclick = switchVisibleRow;
                    // Добавляем в ассоц. массив
                    arrOfHiddens.set(iterator, countHiddens);
                } else {
                    // Если скрытых элементов нет, то присваиваем класс (убираем выделение +/- и hover по этому классу)
                    row.querySelector('.rkn-table__zero-column').classList.add('unique');
                    row.classList.add('unique');
                }
                // Класс свёрнутого дерева
                row.classList.add('close');
                console.log(row);
                console.log(`Строка № - ${iterator}, скрыто до неё - ${countHiddens} строк(и)`);
                countHiddens = 0;
            }
            iterator++;
        }
        
        console.log(`Массив с парой НомерСтроки/СкрытоДо:`);
        for(let pair of arrOfHiddens) {
            console.log(`Элемент № - ${pair[0]}, скрыто до этой строки - ${pair[1]}`);
        }
        
    } /* END of (window.location.href == 'https://dok-lock.top/')*/


}); 






