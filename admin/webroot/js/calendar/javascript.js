// JS ���������
var calendar = null; 


// ������� ���������� ����� ���� ������� �� ����
function selected(cal, date) {
	cal.sel.value = date; // ��������� �������� ���� ������
}

// ���������� ����� ������������ ������� ������ ��� �������� ������ �������
function closeHandler(cal) {
	cal.hide();			// ������ ���������

	Calendar.removeEvent(document, "mousedown", checkCalendar);
}

// ���������� ����� ������������ �������� �� ������ ���� � ����� ����� ��������� ����� ���������. ��������� �����������
function checkCalendar(ev) {
	var el = Calendar.is_ie ? Calendar.getElement(ev) : Calendar.getTargetElement(ev);
	for (; el != null; el = el.parentNode)
	
	if (el == calendar.element || el.tagName == "A") break;
	if (el == null) {
		// �������� closeHandler ��� �������� ���������
		calendar.callCloseHandler(); Calendar.stopEvent(ev);
	}
}

// ������� ����������� ���������
function showCalendar(id) {
	var el = document.getElementById(id);
	if (calendar != null) {
		// ���� ��� ����� �� ���������.
		calendar.hide();		// �������� ������������ ���������
		calendar.parseDate(el.value); // �������� ����� ����
	} else {
		// ������ �����, �������� ������� ���������
		var cal = new Calendar(true, null, selected, closeHandler);
		calendar = cal;		
		cal.setRange(1970, 2070);	// ���/���� ��� �����������
		calendar.create();		// ������� ����������� ���� ���������
	}
	calendar.sel = el;		// ��������� �� ������������ ���� input
	calendar.showAtElement(el);	// ���������� ��������� ��� ����.input

	Calendar.addEvent(document, "mousedown", checkCalendar);
	return false;
}