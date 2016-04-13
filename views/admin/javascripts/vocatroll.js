
var dateID = 0;
var uniq = [];
var elemID = [];
var originalField = [];
var currentValues = [
{
    type: 'Exact',
    year: '',
    month: 0,
    day: 0,
    rangeYear: '',
    rangeMonth: 0,
    rangeDay: 0
}];

(function ($) {
    function isArray(o) {
        return Object.prototype.toString.call(o) === '[object Array]';
    }
    $( document ).ready(function() {
        
		//$('#item-type').change(function (){
		//	alert("My Change");
		//});

		Omeka.Items.changeItemType = function (changeItemTypeUrl, itemId) {
			$('#change_type').hide();
			$('#item-type').change(function () {
				var params = {
					type_id: $(this).val()
				};
				//alert(params.type_id);
				if (itemId) {
					params.item_id = itemId;
				}
				$.ajax({
					url: changeItemTypeUrl,
					type: 'POST',
					dataType: 'html',
					data: params,
					success: function (response) {
						var form = $('#type-metadata-form');
						//alert("MY Change");
						form.hide();
						form.find('textarea').each(function () {
							tinyMCE.execCommand('mceRemoveControl', true, this.id);
						});
						form.html(response);
						form.trigger('omeka:elementformload');
						form.slideDown(1000, function () {
							// Explicit show() call fixes IE7
							$(this).show();
						});
						if (item_type_metadate[params.type_id]) {
						  for (i=0;i<item_type_metadate[params.type_id].length;i++) {
							vocatrollChangeField(item_type_metadate[params.type_id][i]);
						  }
						}
					}
				});
			});
		};


       function vocatrollChangeField (vocaObject) {

                    // act only if the element exists in the page
                    switch (vocaObject.elemtype) {

                    case 'select':

                        var elemLenght = vocaObject.elemlist.length; // number of elements in the list
                        var name = $('#Elements-' + vocaObject.elem + '-0-text').attr('name');
                        // retrieve the value(s)
                        var e = 0;
                        var value = '';
                        var sel = '';
                        var html = '';
                        var selcheck = false; // is one of the elements checked/selected?

                        if ($('#Elements-' + vocaObject.elem + '-' + e + '-text').val().trim() != '') {
                            value = $('#Elements-' + vocaObject.elem + '-' + e + '-text').val().trim();
                        }
                        var parent = $('#Elements-' + vocaObject.elem + '-0-text').parent();

                        for (var a = 0; a < elemLenght; a++) {
                            html += '<option value="' + vocaObject.elemlist[a].trim() + '"';
                            if (vocaObject.elemlist[a].trim() == value) {
                                html += ' selected';
                                selcheck = true;
                            }
                            html += '>' + vocaObject.elemlist[a].trim() + '</option>';
                        }

                        if (selcheck != true) {
                            sel = ' selected';
                        }
                        html = '<option value=""' + sel + '>Select One</option>' + html;
                        $('#element-' + vocaObject.elem + ' .input').append('<select id="Elements-' + vocaObject.elem + '-0-select" name="' + name + '">' + html + '</select>');



                        $('#Elements-' + vocaObject.elem + '-0-text').remove();
                        $('#element-' + vocaObject.elem + ' .use-html').remove();
                        $('#add_element_' + vocaObject.elem).prop('disabled', true);
                        $('#element-' + vocaObject.elem + ' .inputs .input-block').first().addClass('keepit');
                        $('#element-' + vocaObject.elem + ' .inputs .input-block').each(function () {
                            if (!$(this).hasClass('keepit')) {
                                $(this).empty();
                            }
                        });





                        break;

                    case 'checkbox':

                        var elemLenght = vocaObject.elemlist.length; // number of elements in the list
                        var name = $('#Elements-' + vocaObject.elem + '-0-text').attr('name');
                        // retrieve the value(s)
                        var e = 0;
                        var exists = true;
                        var value = [];
                        while (exists) {
                            if ($('#Elements-' + vocaObject.elem + '-' + e + '-text').length != 0) {
                                if ($('#Elements-' + vocaObject.elem + '-' + e + '-text').val() != '') {
                                    value.push($('#Elements-' + vocaObject.elem + '-' + e + '-text').val());
                                }
                                e++;
                            } else {
                                exists = false;
                            }
                        }
                        var parent = $('#Elements-' + vocaObject.elem + '-0-text').parent();
                        var html = '';
                        var selcheck = false; // is one of the elements checked/selected?

                        for (var a = 0; a < elemLenght; a++) {
                            html += '<input name="Elements[' + vocaObject.elem + '][' + a + '][text]" type="checkbox" value="' + vocaObject.elemlist[a].trim() + '"';
                            if ($.inArray(vocaObject.elemlist[a].trim(), value) > - 1) {
                                html += ' checked';
                                selcheck = true;
                            }
                            html += '> ' + vocaObject.elemlist[a] + '<br>';
                        }

                        $('#element-' + vocaObject.elem + ' .input').append(html);


                        $('#Elements-' + vocaObject.elem + '-0-text').remove();
                        $('#element-' + vocaObject.elem + ' .use-html').remove();
                        $('#add_element_' + vocaObject.elem).prop('disabled', true);
                        $('#element-' + vocaObject.elem + ' .inputs .input-block').first().addClass('keepit');
                        $('#element-' + vocaObject.elem + ' .inputs .input-block').each(function () {
                            if (!$(this).hasClass('keepit')) {
                                $(this).empty();
                            }
                        });




                        break;

                    case 'date':


                        // the actual element ID
                        elemID[dateID] = 'Elements-' + vocaObject.elem + '-0-text';
                        uniq[dateID] = 'vocatroll-' + vocaObject.elem;
                        var parentElement, pElem, originalValues = [], ov, v;
                        // minYear is a pref, max year could  also be
                        if (typeof vocaObject.elemlist[0] != 'undefined' && vocaObject.elemlist[0].length) {
                            var minYear = vocaObject.elemlist[0];
                        } else {
                            var minYear = 1967;
                        }
                        if (typeof vocaObject.elemlist[1] != 'undefined') {
                            var maxYear = vocaObject.elemlist[1];
                        } else {
                            var maxYear = new Date().getFullYear();
                        }

                        var yearList = [], monthList = [], dayList = [], typeList = [];
                        
                        var monthNumber = {
                            January: 1,
                            February: 2,
                            March: 3,
                            April: 4,
                            May: 5,
                            June: 6,
                            July: 7,
                            August: 8,
                            September: 9,
                            October: 10,
                            November: 11,
                            December: 12
                        };

                        // a bunch of functions to calculate things

                        var correctDayMonth = function (y, m, d) {
                            // returns corrected day
                            var maxMonthDays = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]; // index 0 is 0, Jan is index 1 Feb 2 etc ...
                            if (d < 29) {
                                return d;
                            } else {
                                if (d <= maxMonthDays[m]) {
                                    return d;
                                } else if (m == 2) {
                                    if ((!(y % 4) && y % 100) || !(y % 400)) {
                                        return 29;
                                    } else {
                                        return 28;
                                    }
                                } else {
                                    return 30;
                                }
                            }
                        }
                        
                        var updateOriginal = function(dataDate) {
                            // get values
                            var id = parseInt(dataDate);
                            dateID = id;
                            //alert("updating date = " + id);
                            //alert(dateID);
                            currentValues[id] = {
                                type: (document.getElementById(uniq[id] + '-type').options[document.getElementById(uniq[id] + '-type').selectedIndex].value.length) ? document.getElementById(uniq[id] + '-type').options[document.getElementById(uniq[dateID] + '-type').selectedIndex].value : 'Exact',
                                year: (document.getElementById(uniq[id] + '-year').options[document.getElementById(uniq[id] + '-year').selectedIndex].value.length) ? document.getElementById(uniq[id] + '-year').options[document.getElementById(uniq[dateID] + '-year').selectedIndex].value : '',
                                month: (document.getElementById(uniq[id] + '-month').options[document.getElementById(uniq[id] + '-month').selectedIndex].value.length) ? document.getElementById(uniq[id] + '-month').options[document.getElementById(uniq[dateID] + '-month').selectedIndex].value : 0,
                                day: (document.getElementById(uniq[id] + '-day').options[document.getElementById(uniq[id] + '-day').selectedIndex].value.length) ? document.getElementById(uniq[id] + '-day').options[document.getElementById(uniq[dateID] + '-day').selectedIndex].value : 0,
                                rangeYear: (document.getElementById(uniq[id] + '-range-year').options[document.getElementById(uniq[id] + '-range-year').selectedIndex].value.length) ? document.getElementById(uniq[id] + '-range-year').options[document.getElementById(uniq[dateID] + '-range-year').selectedIndex].value : '',
                                rangeMonth: (document.getElementById(uniq[id] + '-range-month').options[document.getElementById(uniq[id] + '-range-month').selectedIndex].value.length) ? document.getElementById(uniq[id] + '-range-month').options[document.getElementById(uniq[dateID] + '-range-month').selectedIndex].value : 0,
                                rangeDay: (document.getElementById(uniq[id] + '-range-day').options[document.getElementById(uniq[id] + '-range-day').selectedIndex].value.length) ? document.getElementById(uniq[id] + '-range-day').options[document.getElementById(uniq[dateID] + '-range-day').selectedIndex].value : 0
                            };


                            originalField[dateID].val(generateString());
                            switch (currentValues[id].type) {
                            case 'Exact':
                            case 'Circa':
                                document.getElementById(uniq[id] + '-year').style.display = 'inline';
                                document.getElementById(uniq[id] + '-month').style.display = 'none';
                                document.getElementById(uniq[id] + '-day').style.display = 'none';
                                document.getElementById(uniq[id] + '-range-year').style.display = 'none';
                                document.getElementById(uniq[id] + '-range-month').style.display = 'none';
                                document.getElementById(uniq[id] + '-range-day').style.display = 'none';
                                if (currentValues[id].year.length) {
                                    document.getElementById(uniq[id] + '-month').style.display = 'inline';
                                }
                                if (currentValues[id].month.length) {
                                    document.getElementById(uniq[id] + '-day').style.display = 'inline';
                                }
                                break;
                            case 'Range':
                                document.getElementById(uniq[id] + '-year').style.display = 'inline';
                                document.getElementById(uniq[id] + '-month').style.display = 'none';
                                document.getElementById(uniq[id] + '-day').style.display = 'none';
                                document.getElementById(uniq[id] + '-range-year').style.display = 'inline';
                                document.getElementById(uniq[id] + '-range-month').style.display = 'none';
                                document.getElementById(uniq[id] + '-range-day').style.display = 'none';
                                if (currentValues[id].year.length) {
                                    document.getElementById(uniq[id] + '-month').style.display = 'inline';
                                    document.getElementById(uniq[id] + '-range-month').style.display = 'inline';
                                }
                                if (currentValues[id].month.length) {
                                    document.getElementById(uniq[id] + '-day').style.display = 'inline';
                                    document.getElementById(uniq[id] + '-range-day').style.display = 'inline';
                                }
                                break;
                            }
                        };

                        var createSelect = function (fieldName, selID, list, currValue, parentElem, className) {
                            var selectOption, selectName, newField;

                            newField = document.createElement("select");
                            newField.name = newField.id = selID;
                            if (list.length) {
                                for (s = 0; s < list.length; s++) {
                                    selectOption = document.createElement("option");
                                    selectOption.value = '' + list[s][0];
                                    selectOption.textContent = '' + list[s][1];
                                    if (list[s][0] + '' == currValue + '') {
                                        selectOption.selected = 'selected';
                                    }
                                    newField.appendChild(selectOption);
                                }
                            }
                            selectName = document.createElement("name");
                            selectName.htmlFor = selID;
                            selectName.textContent = fieldName;
                            parentElem.appendChild(newField);
                            // originalField.parentNode.insertBefore(newField, originalField);
                            newField.setAttribute('data-dateid', dateID);
                            newField.addEventListener('change', function(){ 
                            	updateOriginal($(this).attr("data-dateid"));
                            }, false);
                            $('#' + selID).addClass(className);
                        }

                        var getCurrentValues = function (origVal) {
                            /* possible values examples:
                                          * Ranges:
                                          * 1980 - 1990 (year range)
                                          * 6/24/1976 - 5/15/1977 (full date range)
                                          * August 1943 - September 1945 (month and year range)
                                          *
                                          * Regular Dates:
                                          * 1775 (year only)
                                          * June 1648 (month and year)
                                          * 3/26/2001 (full date)
                                          * 
                                          * Circa:
                                          * to any regular date the word (Circa) is appended
                                          * 1645 (Circa)
                                          * June 1776 (Circa)
                                          * 3/24/1876 (Circa)
                                          * 
                                          * Logic:
                                          * ov.split return x fields:
                                          * 1: must be a year
                                          * 2: can be a year range, a year circa, or a month and year exact
                                          * 3: full date or month and year circa
                                          * 4: month and year range or full date circa
                                          * 5: impossible?
                                          * 6: full date range
                                          */
                            var currVal = {
                                type: '',
                                year: '',
                                month: '',
                                day: '',
                                rangeYear: '',
                                rangeMonth: '',
                                rangeDay: ''
                            };

                            if (origVal.length) {
                                switch (originalValues.length ) {
                                case 1:
                                    // must be a year
                                    var currVal = {
                                        type: 'Exact',
                                        year: originalValues[0],
                                        month: '',
                                        day: '',
                                        rangeYear: '',
                                        rangeMonth: '',
                                        rangeDay: ''
                                    };
                                    break;
                                case 2:
                                    if (originalValues[1] == '(Circa)') {
                                        // year circa
                                        var currVal = {
                                            type: 'Circa',
                                            year: originalValues[0],
                                            month: '',
                                            day: '',
                                            rangeYear: '',
                                            rangeMonth: '',
                                            rangeDay: ''
                                        };
                                    } else if (originalValues[0].length > 2) {
                                        // month and year exact
                                        var currVal = {
                                            type: 'Exact',
                                            year: originalValues[1],
                                            month: originalValues[0],
                                            day: '',
                                            rangeYear: '',
                                            rangeMonth: '',
                                            rangeDay: ''
                                        };
                                    } else {
                                        // year range
                                        var currVal = {
                                            type: 'Range',
                                            year: originalValues[0],
                                            month: '',
                                            day: '',
                                            rangeYear: originalValues[1],
                                            rangeMonth: '',
                                            rangeDay: ''
                                        };
                                    }
                                    break;
                                case 3:
                                    if (originalValues[2] == '(Circa)') {
                                        // month and year circa
                                        var currVal = {
                                            type: 'Circa',
                                            year: originalValues[1],
                                            month: originalValues[0],
                                            day: '',
                                            rangeYear: '',
                                            rangeMonth: '',
                                            rangeDay: ''
                                        };
                                    } else {
                                        // full date
                                        var currVal = {
                                            type: 'Exact',
                                            year: originalValues[2],
                                            month: originalValues[0],
                                            day: originalValues[1],
                                            rangeYear: '',
                                            rangeMonth: '',
                                            rangeDay: ''
                                        };
                                    }
                                    break;
                                case 4:
                                    if (originalValues[3] == '(Circa)') {
                                        // full date circa
                                        var currVal = {
                                            type: 'Circa',
                                            year: originalValues[2],
                                            month: originalValues[0],
                                            day: originalValues[1],
                                            rangeYear: '',
                                            rangeMonth: '',
                                            rangeDay: ''
                                        };
                                    } else {
                                        // month and year range
                                        var currVal = {
                                            type: 'Range',
                                            year: originalValues[1],
                                            month: originalValues[0],
                                            day: '',
                                            rangeYear: originalValues[3],
                                            rangeMonth: originalValues[2],
                                            rangeDay: ''
                                        };
                                    }
                                    break;
                                case 6:
                                    // full date range
                                    var currVal = {
                                        type: 'Range',
                                        year: originalValues[2],
                                        month: originalValues[0],
                                        day: originalValues[1],
                                        rangeYear: originalValues[5],
                                        rangeMonth: originalValues[3],
                                        rangeDay: originalValues[4]
                                    };
                                    break;
                                default:
                                    var currVal = {
                                        type: '',
                                        year: '',
                                        month: '',
                                        day: '',
                                        rangeYear: '',
                                        rangeMonth: '',
                                        rangeDay: ''
                                    };
                                }
                            }

                            return currVal;
                        }

                        var generateString = function () {
                            switch (currentValues[dateID].type) {
                            case 'Exact':
                                if (currentValues[dateID].day.length && currentValues[dateID].month.length && currentValues[dateID].year.length) {
                                    currentValues[dateID].day = correctDayMonth(currentValues[dateID].year, monthNumber[currentValues[dateID].month], currentValues[dateID].day);
                                    return currentValues[dateID].month + ' ' + currentValues[dateID].day + ', ' + currentValues[dateID].year;
                                } else if (currentValues[dateID].month.length && currentValues[dateID].year.length) {
                                    return currentValues[dateID].month + ' ' + currentValues[dateID].year;
                                } else {
                                    return currentValues[dateID].year;
                                }
                                break;
                            case 'Circa':
                                if (currentValues[dateID].day.length && currentValues[dateID].month.length && currentValues[dateID].year.length) {
                                    currentValues[dateID].day = correctDayMonth(currentValues[dateID].year, monthNumber[currentValues[dateID].month], currentValues[dateID].day);
                                    return currentValues[dateID].month + ' ' + currentValues[dateID].day + ', ' + currentValues[dateID].year + ' (Circa)';
                                } else if (currentValues[dateID].month.length && currentValues[dateID].year.length) {
                                    return currentValues[dateID].month + ' ' + currentValues[dateID].year + ' (Circa)';
                                } else {
                                    return currentValues[dateID].year + ' (Circa)';
                                }
                                break;
                            case 'Range':
                                var fromRange = '';
                                var toRange = '';
                                if (currentValues[dateID].day.length && currentValues[dateID].month.length && currentValues[dateID].year.length) {
                                    currentValues[dateID].day = correctDayMonth(currentValues[dateID].year, monthNumber[currentValues[dateID].month], currentValues[dateID].day);
                                    fromRange = currentValues[dateID].month + ' ' + currentValues[dateID].day + ', ' + currentValues[dateID].year;
                                } else if (currentValues[dateID].month.length && currentValues[dateID].year.length) {
                                    fromRange = currentValues[dateID].month + ' ' + currentValues[dateID].year;
                                } else {
                                    fromRange = currentValues[dateID].year;
                                }

                                if (currentValues[dateID].rangeDay.length && currentValues[dateID].rangeMonth.length && currentValues[dateID].rangeYear.length) {
                                    currentValues[dateID].rangeDay = correctDayMonth(currentValues[dateID].rangeYear, monthNumber[currentValues[dateID].rangeMonth], currentValues[dateID].rangeDay);
                                    toRange = currentValues[dateID].rangeMonth + ' ' + currentValues[dateID].rangeDay + ', ' + currentValues[dateID].rangeYear;
                                } else if (currentValues[dateID].rangeMonth.length && currentValues[dateID].rangeYear.length) {
                                    toRange = currentValues[dateID].rangeMonth + ' ' + currentValues[dateID].rangeYear;
                                } else {
                                    toRange = currentValues[dateID].rangeYear;
                                }
                                // range is from earlier date to more recent date
                                if (currentValues[dateID].year > currentValues[dateID].rangeYear) {
                                    return toRange + ' - ' + fromRange;
                                } else if (currentValues[dateID].year < currentValues[dateID].rangeYear) {
                                    return fromRange + ' - ' + toRange;
                                } else {
                                    if (monthNumber[currentValues[dateID].month] > monthNumber[currentValues[dateID].rangeMonth]) {
                                        return toRange + ' - ' + fromRange;
                                    } else if (monthNumber[currentValues[dateID].month] < monthNumber[currentValues[dateID].rangeMonth]) {
                                        return fromRange + ' - ' + toRange;
                                    } else {
                                        if (currentValues[dateID].day > currentValues[dateID].rangeDay) {
                                            return toRange + ' - ' + fromRange;
                                        } else {
                                            return fromRange + ' - ' + toRange;
                                        }
                                    }
                                }
                                break;
                            default:
                                return '';
                                break;
                            }
                        }


                        // generate lists
                        yearList.push(['', 'Year']);
                        for (z = maxYear; z >= minYear; z--) {
                            yearList.push([z, z]);
                        }
                        monthList.push(['', 'Month'], ['January', 'January'], ['February', 'February'], ['March', 'March'], ['April', 'April'], ['May', 'May'], ['June', 'June'], ['July', 'July'], ['August', 'August'], ['September', 'September'], ['October', 'October'], ['November', 'November'], ['December', 'December']);
                        dayList.push(['', 'Day']);
                        for (x = 1; x <= 31; x++) {
                            dayList.push([x, x]);
                        }
                        typeList.push(['Circa', 'Circa']);
                        typeList.push(['Exact', 'Exact']);
                        typeList.push(['Range', 'Range']);


                        // get the initial value, remove textarea and add hidden field
                        originalField[dateID] = $('#' + elemID[dateID]);
                        originalField[dateID].attr('rows', '1');
                        originalField[dateID].attr('readonly', 'readonly');
                        pElem = originalField[dateID].parent();
                        pElem.id = uniq[dateID] + '-parent';
                        pElem.addClass('vocatroll-date-parent');
                        // parentElement = document.getElementById(uniq[dateID] + '-parent');
                        ov = originalField[dateID].val();

                        originalValues = ov.split(/[- \/,]+/);
						
                        currentValues[dateID] = getCurrentValues(originalValues);

                        var typeArea = document.createElement('div');
                        var exactArea = document.createElement('div');
                        var rangeArea = document.createElement('div');
                        typeArea.id = uniq[dateID] + '-type-parent';
                        exactArea.id = uniq[dateID] + '-exact-parent';
                        rangeArea.id = uniq[dateID] + '-range-parent';
                        pElem.append(typeArea);
                        pElem.append(exactArea);
                        pElem.append(rangeArea);

                        createSelect('Type', uniq[dateID] + '-type', typeList, currentValues[dateID].type, typeArea, 'vocatroll-date-type');
                        createSelect('Year', uniq[dateID] + '-year', yearList, currentValues[dateID].year, exactArea, 'vocatroll-date-year');
                        createSelect('Month', uniq[dateID] + '-month', monthList, currentValues[dateID].month, exactArea, 'vocatroll-date-month');
                        createSelect('Day', uniq[dateID] + '-day', dayList, currentValues[dateID].day, exactArea, 'vocatroll-date-day');
                        createSelect('Year', uniq[dateID] + '-range-year', yearList, currentValues[dateID].rangeYear, rangeArea, 'vocatroll-date-range-year');
                        createSelect('Month', uniq[dateID] + '-range-month', monthList, currentValues[dateID].rangeMonth, rangeArea, 'vocatroll-date-range-month');
                        createSelect('Day', uniq[dateID] + '-range-day', dayList, currentValues[dateID].rangeDay, rangeArea, 'vocatroll-date-range-day');


                        $('#element-' + vocaObject.elem + ' .use-html').remove();
                        $('#add_element_' + vocaObject.elem).prop('disabled', true);
						
                        updateOriginal(dateID); // ADDED BY GRETCHEN
                        dateID++;
				

                        break;

                    case 'hide':
                        $('div#element-' + vocaObject.elem + '.field').hide();
                        break;




                    case 'text':



                        break;


                    }



                

       
       }





        if (isArray( customInput )) {
            var ciLenght = customInput.length; // number of elements that we are we going to modify
            for (var i = 0; i < ciLenght; i++) {
                if ($('#Elements-' + customInput[i].elem + '-0-text').length != 0) {
                  vocatrollChangeField(customInput[i]);            
                }
            }
        }
    })
})(jQuery);

