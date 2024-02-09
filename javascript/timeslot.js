var AJAX_URL = "/main/public/plugins/deploy/ajax/timeslot.php";

var timeslot = {};
var timeslotsData = JSON.parse(document.getElementById('timeslotsData').dataset.value);

var everydayButton = document.getElementById('everyday');
var daysLength = parseInt(document.getElementById('daysLength').dataset.value);
var timeslot_id = parseInt(document.getElementById('timeslotId').dataset.value);

function setSliderValues(slider, values) {
    slider.noUiSlider.set(values);
}

function toggleSlider(slider, checkbox, addRangeButton, delRangeButton, i) {
    if (checkbox.checked) {
        addRangeButton.removeAttribute('disabled');
        delRangeButton.removeAttribute('disabled');
        setSliderValues(slider, timeslotsData[i] ? timeslotsData[i].map(slot => [slot.starttime, slot.endtime]).flat() : [8, 12, 14, 18]);
        slider.style.display = 'block';
    } else {
        addRangeButton.setAttribute('disabled', true);
        delRangeButton.setAttribute('disabled', true);
        slider.style.display = 'none';
    }
    timeslot[i]['is_enable'] = +checkbox.checked;
    document.getElementById('timeslot').value = JSON.stringify(timeslot);
}

function sendAjaxRequest(action, timeslot, timeslot_id) {
    $.ajax({
        method: 'POST',
        url: AJAX_URL,
        data: {
            action: action,
            timeslot: timeslot,
            plugin_deploy_timeslots_id: timeslot_id
        }
    }).done(function(response) {
        $('#tr_countainer').html(response);
    });
}

for (let i = 1; i <= daysLength; i++) {
    let slider = document.getElementById('slider' + i);
    let checkbox = document.getElementById('notimeslot' + i);
    let alldayButton = document.getElementById('allday' + i);
    let addRangeButton = document.getElementById('addrange' + i);
    let delRangeButton = document.getElementById('delrange' + i);

    timeslot[i] = {
        is_enable: +checkbox.checked
    };

    noUiSlider.create(slider, {
        start: timeslotsData[i] ? timeslotsData[i].map(slot => [slot.starttime, slot.endtime]).flat() : [0, 0, 0, 0],
        connect: [false, ...Array(timeslotsData[i].length * 2).fill().flatMap((_, idx) => idx % 2 === 0 ? [true, false] : [])],
        behaviour: 'drag',
        step: 1,
        range: {
            'min': 0,
            'max': 24
        },
        margin: 1,
    });

    slider.noUiSlider.on('update', function(values, handle) {
        timeslot[i]['is_enable'] = +checkbox.checked;
        let order = Math.floor(handle / 2);
        let start = document.getElementById('value' + order + '_start' + i);
        let end = document.getElementById('value' + order + '_end' + i);
        if (handle % 2 === 1) {
            end.value = values[handle];
        } else {
            start.value = values[handle];
        }
        timeslot[i][order] = {
            starttime: start.value,
            endtime: end.value
        };
        document.getElementById('timeslot').value = JSON.stringify(timeslot);
    });

    // Disable slider if checkbox is not checked
    toggleSlider(slider, checkbox, addRangeButton, delRangeButton, i);

    // Enable or disable slider on checkbox change
    checkbox.addEventListener('change', function() {
        toggleSlider(slider, this, addRangeButton, delRangeButton, i);
    });

    alldayButton.addEventListener('click', function(event) {
        timeslot[i] = {
            0: {
                starttime: '0.00',
                endtime: '24.00'
            },
            is_enable: 1
        };
        document.getElementById('timeslot').value = JSON.stringify(timeslot);
        sendAjaxRequest('add', timeslot, timeslot_id);
    });

    // Disable add button if there are already 12 ranges
    if (Object.keys(timeslot[i]).length - 1 >= 12) {
        addRangeButton.setAttribute('disabled', true);
    }

    addRangeButton.addEventListener('click', function(event) {
        let lastKey = Object.keys(timeslot[i]).length - 1;
        timeslot[i][lastKey + 1] = {
            starttime: '23.00',
            endtime: '24.00'
        };
        document.getElementById('timeslot').value = JSON.stringify(timeslot);
        sendAjaxRequest('add', timeslot, timeslot_id);
    });

    // Disable delete button if there is only 1 range
    if (Object.keys(timeslot[i]).length - 1 === 1) {
        delRangeButton.setAttribute('disabled', true);
    }

    delRangeButton.addEventListener('click', function(event) {
        let lastKey = Object.keys(timeslot[i]).length - 1;
        delete timeslot[i][lastKey - 1];
        document.getElementById('timeslot').value = JSON.stringify(timeslot);
        sendAjaxRequest('add', timeslot, timeslot_id);
    });

}

everydayButton.addEventListener('click', function(event) {
    for (let i = 1; i <= daysLength; i++) {
        timeslot[i] = {
            0: {
                starttime: '0.00',
                endtime: '24.00'
            },
            is_enable: 1
        };
        document.getElementById('timeslot').value = JSON.stringify(timeslot);
        document.getElementById("rangeform").submit();
    }
});