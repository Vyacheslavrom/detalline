// JS календарь
var calendar = null; 


// Функция вызывается когда юзер кликает на дате
function selected(cal, date) {
	cal.sel.value = date; // обновляет значение поля инпута
}

// вызывается когда пользователь кликает подате или нажимает кнопку закрыть
function closeHandler(cal) {
	cal.hide();			// скрыть календарь

	Calendar.removeEvent(document, "mousedown", checkCalendar);
}

// вызывается когда пользователь нажимает на кнопку мыши в любом месте документа кроме календаря. календарь закрывается
function checkCalendar(ev) {
	var el = Calendar.is_ie ? Calendar.getElement(ev) : Calendar.getTargetElement(ev);
	for (; el != null; el = el.parentNode)
	
	if (el == calendar.element || el.tagName == "A") break;
	if (el == null) {
		// вызывает closeHandler для закрытия календаря
		calendar.callCloseHandler(); Calendar.stopEvent(ev);
	}
}

// Функция отображения календаря
function showCalendar(id) {
	var el = document.getElementById(id);
	if (calendar != null) {
		// если уже окрыт то обновляем.
		calendar.hide();		// скрывает существующий календарь
		calendar.parseDate(el.value); // посылает новую дату
	} else {
		// первый вызов, создание объекта календаря
		var cal = new Calendar(true, null, selected, closeHandler);
		calendar = cal;		
		cal.setRange(1970, 2070);	// мин/макс год отображения
		calendar.create();		// создает всплывающее окно календаря
	}
	calendar.sel = el;		// указывает на используемое поле input
	calendar.showAtElement(el);	// показывает календарь для след.input

	Calendar.addEvent(document, "mousedown", checkCalendar);
	return false;
}