function standardize(node) {
    if (!node.addEventListener)
        node.addEventListener = function(t, l, c) { this["on"+t] = l; };
    if (!node.dispatchEvent)
        node.dispatchEvent = function(e) { this["on"+e.type](e); };
}
window.onload = function () { init(); };
function init()
{
	var start_year = document.getElementById("start_year");
	standardize(start_year);
	start_year.addEventListener("change", selectChangeEvent, true);

	var start_month = document.getElementById("start_month");
	standardize(start_month);
	start_month.addEventListener("change", selectChangeEvent, true);

	var start_day = document.getElementById("start_day");
	standardize(start_day);
	start_day.addEventListener("change", selectChangeEvent, true);

	var end_year = document.getElementById("end_year");
	standardize(end_year);
	end_year.addEventListener("change", selectChangeEvent, true);

	var end_month = document.getElementById("end_month");
	standardize(end_month);
	end_month.addEventListener("change", selectChangeEvent, true);

	var end_day = document.getElementById("end_day");
	standardize(end_day);
	end_day.addEventListener("change", selectChangeEvent, true);
}
function selectChangeEvent(event)
{
	nonExistDayIsNonDisplayed("start_year", "start_month", "start_day");
	nonExistDayIsNonDisplayed("end_year", "end_month", "end_day");
}
/**
 * ���݂��Ȃ����i2��30���Ȃǁj�̑I�������\���ɂ���
 *
 * @param yearId �u�N�v��select�v�f��ID
 * @param monthId �u���v��select�v�f��ID
 * @param dayId �u���v��select�v�f��ID
 */
function nonExistDayIsNonDisplayed(yearId, monthId, dayId) {
	var yearSelect = document.getElementById(yearId);
	var monthSelect = document.getElementById(monthId);
	var daySelect = document.getElementById(dayId);
	var dayOptions = daySelect.options;

	if((yearSelect.options[yearSelect.selectedIndex].value == "----") ||
	   (monthSelect.options[monthSelect.selectedIndex].value == "--"))
	{
		return;
	}
	
	var selectedMonth = parseInt(monthSelect.options[monthSelect.selectedIndex].value, 10);
	
	if (selectedMonth === 2) {
	// 2���̏ꍇ
		var selectedYear = parseInt(yearSelect.options[yearSelect.selectedIndex].value, 10);

		for (var i = dayOptions.length - 1; i >= 1; i--) {
			var dayOption = dayOptions[i];
			var dayValue = parseInt(dayOption.value, 10);
			var leapYear = isLeapYear(selectedYear); // �[�N��
			if (dayValue >= 30 || (dayValue === 29 && !leapYear)) {
				dayOption.setAttribute("disabled", "disabled"); // �I��s�\�w��
				if (dayOption.selected) {
				// 29��(�[�N�łȂ��ꍇ�̂�)�A30���A31���̂����ꂩ���I������Ă����ꍇ�́A2���̍ŏI���ɕύX
					if (leapYear) {
						daySelect.value = "29";
					} else {
						daySelect.value = "28";
					}
				}
			} else if ("disabled" === dayOption.getAttribute("disabled")) {
			// �I��s�\�w�肪������Ă��������
				dayOption.removeAttribute("disabled");
			} else {
				break;
			}
		}
	} else if (selectedMonth === 4 || selectedMonth === 6 || selectedMonth === 9 || selectedMonth === 11) {
	// ���̓�����30���̏ꍇ
		for (var i = dayOptions.length - 1; i >= 1; i--) {
			var dayOption = dayOptions[i];
			var dayValue = parseInt(dayOption.value, 10);
			if (dayValue >= 31) {
				dayOption.setAttribute("disabled", "disabled"); // �I��s�\�w��
				if (dayOption.selected) {
				// 31�����I������Ă����ꍇ�́A�e���̍ŏI���ɕύX
					daySelect.value = "30";
				}
			} else if ("disabled" === dayOption.getAttribute("disabled")) {
			// �I��s�\�w�肪������Ă��������
				dayOption.removeAttribute("disabled");
			} else {
				break;
			}
		}
	} else {
	// ���̓�����31���̏ꍇ
		for (var i = dayOptions.length - 1; i >= 1; i--) {
			var dayOption = dayOptions[i];
			if ("disabled" === dayOption.getAttribute("disabled")) {
			// �I��s�\�w�肪������Ă��������
				dayOption.removeAttribute("disabled");
			} else {
				break;
			}
		}
	}
}
/**
 * �[�N��
 *
 * @param year �N
 *
 * @return �[�N�Ȃ�true�A����ȊO�̏ꍇ��false
 */
function isLeapYear(year) {
	if (((year % 4 == 0) && (year % 100 != 0)) || (year % 400 == 0))
	{
		return true;
	}
	else
	{
		return false;
	}
}
